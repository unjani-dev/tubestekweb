<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Server Model - Enterprise Level
 * Handles server provisioning and management
 */
class ServerModel extends Model
{
    protected $table            = 'servers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'plan_id',
        'server_name',
        'server_type',
        'ip_address',
        'hostname',
        'os',
        'status',
        'cpu_cores',
        'ram_gb',
        'storage_gb',
        'bandwidth_used_gb',
        'uptime_hours',
        'last_start_time',
        'last_stop_time'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'user_id' => 'required|is_natural_no_zero',
        'plan_id' => 'required|is_natural_no_zero',
        'server_name' => 'required|min_length[3]|max_length[255]',
        'os' => 'required',
        'cpu_cores' => 'required|is_natural_no_zero',
        'ram_gb' => 'required|is_natural_no_zero',
        'storage_gb' => 'required|is_natural_no_zero'
    ];

    protected $validationMessages = [
        'server_name' => [
            'required' => 'Server name is required',
            'min_length' => 'Server name must be at least 3 characters'
        ]
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get servers with plan details
     */
    public function getServersWithPlan($userId = null)
    {
        $builder = $this->select('servers.*, server_plans.plan_name, server_plans.price_per_hour')
                        ->join('server_plans', 'servers.plan_id = server_plans.id');
        
        if ($userId) {
            $builder->where('servers.user_id', $userId);
        }
        
        return $builder->orderBy('servers.created_at', 'DESC')->findAll();
    }

    /**
     * Provision new server (dummy implementation)
     */
/**
     * PROVISION SERVER (Logika Inti Potong Saldo & Deployment)
     */
    public function provisionServer(array $data)
    {
        $db = \Config\Database::connect();
        $userModel = new UserModel();
        $planModel = new ServerPlanModel();
        
        // 1. Ambil data plan untuk tahu harganya
        $plan = $planModel->find($data['plan_id']);
        if (!$plan) throw new \Exception("Server plan not found.");

        // Simulasi biaya awal (misal potong biaya 1 jam pertama sebagai jaminan)
        $initialCharge = $plan['price_per_hour'] ?? 0.01;

        // 2. Cek Saldo User
        $user = $userModel->find($data['user_id']);
        if ($user['balance'] < $initialCharge) {
            throw new \Exception("Insufficient balance. You need at least $" . $initialCharge . " to deploy this instance.");
        }

        // --- MULAI TRANSAKSI DATABASE ---
        $db->transBegin();

        try {
            // 3. Potong Saldo User
            $newBalance = $user['balance'] - $initialCharge;
            $userModel->update($user['id'], ['balance' => $newBalance]);

            // 4. Catat ke Billing Transactions
            $db->table('billing_transactions')->insert([
                'user_id'          => $user['id'],
                'amount'           => $initialCharge,
                'transaction_type' => 'charge',
                'payment_method'   => 'wallet',
                'status'           => 'completed',
                'description'      => 'Initial setup & 1st hour charge for server: ' . $data['server_name'],
                'reference_id'     => 'DEP-' . strtoupper(bin2hex(random_bytes(4))),
                'created_at'       => date('Y-m-d H:i:s')
            ]);

            // 5. Insert Data Server
            $serverId = $this->insert([
                'user_id'     => $data['user_id'],
                'plan_id'     => $data['plan_id'],
                'server_name' => $data['server_name'],
                'server_type' => $data['server_type'] ?? 'VPS',
                'os'          => $data['os'],
                'cpu_cores'   => $data['cpu_cores'],
                'ram_gb'      => $data['ram_gb'],
                'storage_gb'  => $data['storage_gb'],
                'status'      => 'provisioning', // Status awal
                'ip_address'  => $this->generateIpAddress(),
                'hostname'    => $this->generateHostname($data['server_name'])
            ]);

            // Jika semua ok, simpan permanen
            $db->transCommit();

            // Simulasi proses "installing OS" sebentar
            // Di production ini biasanya asinkronus, tapi untuk tugas besar kita update saja statusnya
            $this->update($serverId, [
                'status' => 'running',
                'last_start_time' => date('Y-m-d H:i:s')
            ]);

            return $serverId;

        } catch (\Exception $e) {
            // Jika ada yang gagal di tengah jalan, batalkan potong saldo!
            $db->transRollback();
            throw new \Exception("Deployment failed: " . $e->getMessage());
        }
    }

    /**
     * Start Server (Cek saldo lagi sebelum nyala)
     */
    public function startServer(int $serverId)
    {
        $server = $this->find($serverId);
        $userModel = new UserModel();
        $user = $userModel->find($server['user_id']);

        if ($user['balance'] <= 0) {
            return false; // Gagal start karena saldo nol atau minus
        }

        return $this->update($serverId, [
            'status' => 'running',
            'last_start_time' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Start server
     */


     public function stopServer(int $serverId)
     {
         $server = $this->find($serverId);
         if (!$server || $server['status'] === 'stopped') return false;
 
         $uptime = 0;
         if ($server['last_start_time']) {
             $start = strtotime($server['last_start_time']);
             $now = time();
             $uptime = round(($now - $start) / 3600, 2);
         }
 
         return $this->update($serverId, [
             'status' => 'stopped',
             'uptime_hours' => $server['uptime_hours'] + $uptime,
             'last_stop_time' => date('Y-m-d H:i:s')
         ]);
     }
 
     public function restartServer(int $serverId)
     {
         $this->stopServer($serverId);
         return $this->startServer($serverId);
     }
 
     public function terminateServer(int $serverId)
     {
         return $this->update($serverId, [
             'status' => 'terminated',
             'last_stop_time' => date('Y-m-d H:i:s')
         ]);
        }

    /**
     * Terminate server
     */
   

    /**
     * Get server monitoring data
     */
    public function getServerMonitoring(int $serverId, int $limit = 24)
    {
        $monitoringModel = new ServerMonitoringModel();
        return $monitoringModel->where('server_id', $serverId)
                               ->orderBy('recorded_at', 'DESC')
                               ->limit($limit)
                               ->findAll();
    }

    /**
     * Calculate current uptime in hours
     */
    private function calculateUptime($startTime)
    {
        $start = strtotime($startTime);
        $now = time();
        return round(($now - $start) / 3600, 2);
    }

    /**
     * Generate random IP address (for demo)
     */
    private function generateIpAddress()
    {
        return '192.168.' . rand(1, 254) . '.' . rand(1, 254);
    }

    /**
     * Generate hostname
     */
    private function generateHostname($serverName)
    {
        $slug = strtolower(str_replace(' ', '-', $serverName));
        return $slug . '-' . rand(1000, 9999) . '.cloudplatform.local';
    }

    /**
     * Get server statistics
     */
    public function getServerStats($userId = null)
    {
        $builder = $this->builder();
        
        if ($userId) {
            $builder->where('user_id', $userId);
        }

        $total = $builder->countAllResults(false);
        $running = $builder->where('status', 'running')->countAllResults(false);
        $stopped = $builder->where('status', 'stopped')->countAllResults(false);
        $provisioning = $builder->where('status', 'provisioning')->countAllResults();

        return [
            'total' => $total,
            'running' => $running,
            'stopped' => $stopped,
            'provisioning' => $provisioning
        ];
    }

    /**
     * Get running servers for billing
     */
    public function getRunningServers()
    {
        return $this->where('status', 'running')
                    ->findAll();
    }
}