<?php

namespace App\Libraries;

use App\Models\ServerModel;
use App\Models\UserModel;
use App\Models\BillingTransactionModel;
use App\Models\ServerPlanModel;
use App\Models\ServerUsageLogModel;

/**
 * Billing System Library
 * Handles automated billing and charging for running servers
 * 
 * This should be run as a cron job every hour:
 * 0 * * * * php /path/to/project/spark billing:charge
 */
class BillingSystem
{
    protected $serverModel;
    protected $userModel;
    protected $billingModel;
    protected $planModel;

    public function __construct()
    {
        $this->serverModel = new ServerModel();
        $this->userModel = new UserModel();
        $this->billingModel = new BillingTransactionModel();
        $this->planModel = new ServerPlanModel();
    }

    /**
     * Charge all running servers
     * This method should be called by cron every hour
     */
    public function chargeRunningServers()
    {
        log_message('info', 'Starting automated billing process...');

        $runningServers = $this->serverModel->getRunningServers();
        $chargedCount = 0;
        $failedCount = 0;
        $totalCharged = 0;

        foreach ($runningServers as $server) {
            $result = $this->chargeServer($server);
            
            if ($result['success']) {
                $chargedCount++;
                $totalCharged += $result['amount'];
                log_message('info', "Charged server #{$server['id']}: ${$result['amount']}");
            } else {
                $failedCount++;
                log_message('error', "Failed to charge server #{$server['id']}: {$result['reason']}");
                
                // Stop server if insufficient balance
                if ($result['reason'] === 'insufficient_balance') {
                    $this->serverModel->stopServer($server['id']);
                    log_message('warning', "Server #{$server['id']} stopped due to insufficient balance");
                }
            }
        }

        log_message('info', "Billing complete: {$chargedCount} charged, {$failedCount} failed, Total: \${$totalCharged}");

        return [
            'success' => true,
            'charged_count' => $chargedCount,
            'failed_count' => $failedCount,
            'total_amount' => $totalCharged
        ];
    }

    /**
     * Charge a single server
     */
    private function chargeServer($server)
    {
        // Get plan details
        $plan = $this->planModel->find($server['plan_id']);
        
        if (!$plan) {
            return ['success' => false, 'reason' => 'plan_not_found'];
        }

        $hourlyRate = $plan['price_per_hour'];
        
        // Calculate hours since last charge (default 1 hour)
        $hoursToCharge = 1;

        // Get user
        $user = $this->userModel->find($server['user_id']);
        
        if (!$user) {
            return ['success' => false, 'reason' => 'user_not_found'];
        }

        // Check if user has sufficient balance
        if ($user['balance'] < $hourlyRate) {
            // Send low balance notification (implement email/notification)
            $this->notifyLowBalance($user, $server);
            
            return ['success' => false, 'reason' => 'insufficient_balance'];
        }

        // Create charge transaction
        $description = "Server usage: {$server['server_name']} ({$hoursToCharge} hour(s))";
        
        $transactionId = $this->billingModel->createCharge(
            $user['id'],
            $hourlyRate * $hoursToCharge,
            $server['id'],
            $description
        );

        if ($transactionId) {
            // Log usage
            $this->logServerUsage($server['id'], $hoursToCharge, $hourlyRate * $hoursToCharge);
            
            return [
                'success' => true,
                'amount' => $hourlyRate * $hoursToCharge,
                'transaction_id' => $transactionId
            ];
        }

        return ['success' => false, 'reason' => 'transaction_failed'];
    }

    /**
     * Log server usage for reporting
     */
    private function logServerUsage($serverId, $hours, $cost)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('server_usage_logs');

        $server = $this->serverModel->find($serverId);

        $builder->insert([
            'server_id' => $serverId,
            'user_id' => $server['user_id'],
            'plan_id' => $server['plan_id'],
            'hours_used' => $hours,
            'cost' => $cost,
            'billing_period_start' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'billing_period_end' => date('Y-m-d H:i:s'),
            'charged' => 1,
            'charged_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Notify user about low balance
     */
    private function notifyLowBalance($user, $server)
    {
        // TODO: Implement email notification
        log_message('warning', "Low balance alert for user #{$user['id']}: {$user['email']}");
        
        // In production, send email:
        // $email = \Config\Services::email();
        // $email->setTo($user['email']);
        // $email->setSubject('Low Balance Alert - Cloud Platform Mini');
        // $email->setMessage("Your server '{$server['server_name']}' will be stopped due to low balance...");
        // $email->send();
    }

    /**
     * Generate usage report for a user
     */
    public function generateUsageReport($userId, $startDate, $endDate)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('server_usage_logs');

        $usage = $builder->select('server_usage_logs.*, servers.server_name, server_plans.plan_name')
                        ->join('servers', 'server_usage_logs.server_id = servers.id')
                        ->join('server_plans', 'server_usage_logs.plan_id = server_plans.id')
                        ->where('server_usage_logs.user_id', $userId)
                        ->where('server_usage_logs.billing_period_start >=', $startDate)
                        ->where('server_usage_logs.billing_period_end <=', $endDate)
                        ->get()
                        ->getResultArray();

        $totalHours = array_sum(array_column($usage, 'hours_used'));
        $totalCost = array_sum(array_column($usage, 'cost'));

        return [
            'usage' => $usage,
            'summary' => [
                'total_hours' => $totalHours,
                'total_cost' => $totalCost,
                'period_start' => $startDate,
                'period_end' => $endDate
            ]
        ];
    }

    /**
     * Check and warn users with low balance
     */
    public function checkLowBalanceUsers($threshold = 5.00)
    {
        $users = $this->userModel->where('balance <', $threshold)
                                 ->where('status', 'active')
                                 ->findAll();

        foreach ($users as $user) {
            // Check if user has running servers
            $runningServers = $this->serverModel->where('user_id', $user['id'])
                                                ->where('status', 'running')
                                                ->findAll();

            if (count($runningServers) > 0) {
                log_message('warning', "User #{$user['id']} has low balance with {$count} running servers");
                // Send notification
            }
        }

        return $users;
    }
}