<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\BillingSystem;

/**
 * Billing Charge Command
 * 
 * Usage: php spark billing:charge
 * 
 * Add to crontab to run every hour:
 * 0 * * * * cd /path/to/project && php spark billing:charge >> /path/to/logs/billing.log 2>&1
 */
class BillingCharge extends BaseCommand
{
    protected $group       = 'Billing';
    protected $name        = 'billing:charge';
    protected $description = 'Charge all running servers for hourly usage';

    public function run(array $params)
    {
        CLI::write('Starting automated billing process...', 'yellow');
        CLI::newLine();

        $billing = new BillingSystem();
        
        try {
            $result = $billing->chargeRunningServers();

            CLI::write('Billing Process Completed!', 'green');
            CLI::write('Servers Charged: ' . $result['charged_count'], 'cyan');
            CLI::write('Failed Charges: ' . $result['failed_count'], 'red');
            CLI::write('Total Amount: $' . number_format($result['total_amount'], 2), 'cyan');
            CLI::newLine();

            return EXIT_SUCCESS;

        } catch (\Exception $e) {
            CLI::error('Billing process failed: ' . $e->getMessage());
            return EXIT_ERROR;
        }
    }
}

/**
 * Monitoring Collector Command
 * Collects server monitoring metrics
 * 
 * Usage: php spark monitoring:collect
 * 
 * Add to crontab to run every 5 minutes:
 * *5 * * * * cd /path/to/project && php spark monitoring:collect
 */
class MonitoringCollect extends BaseCommand
{
    protected $group       = 'Monitoring';
    protected $name        = 'monitoring:collect';
    protected $description = 'Collect monitoring metrics for all running servers';

    public function run(array $params)
    {
        CLI::write('Collecting server monitoring data...', 'yellow');

        $serverModel = new \App\Models\ServerModel();
        $monitoringModel = new \App\Models\ServerMonitoringModel();

        $runningServers = $serverModel->getRunningServers();
        $collected = 0;

        foreach ($runningServers as $server) {
            $monitoringModel->recordMetrics($server['id']);
            $collected++;
        }

        CLI::write("Collected metrics for {$collected} servers", 'green');
        CLI::newLine();

        return EXIT_SUCCESS;
    }
}

/**
 * Database Cleanup Command
 * Removes old logs and monitoring data
 * 
 * Usage: php spark db:cleanup
 * 
 * Run daily at midnight:
 * 0 0 * * * cd /path/to/project && php spark db:cleanup
 */
class DatabaseCleanup extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'db:cleanup';
    protected $description = 'Clean up old logs and monitoring data';

    public function run(array $params)
    {
        CLI::write('Starting database cleanup...', 'yellow');

        $monitoringModel = new \App\Models\ServerMonitoringModel();
        $activityModel = new \App\Models\ActivityLogModel();

        // Clean old monitoring data (>30 days)
        $monitoringModel->cleanOldData();
        CLI::write('Cleaned old monitoring data', 'green');

        // Clean old activity logs (>90 days)
        $activityModel->cleanOldLogs();
        CLI::write('Cleaned old activity logs', 'green');

        CLI::write('Database cleanup completed!', 'green');
        CLI::newLine();

        return EXIT_SUCCESS;
    }
}