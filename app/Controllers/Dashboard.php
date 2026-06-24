<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ServerModel;
use App\Models\ServerPlanModel;
use App\Models\BillingTransactionModel;

/**
 * User Dashboard Controller
 */
class Dashboard extends BaseController
{
    protected $userModel;
    protected $serverModel;
    protected $billingModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->serverModel = new ServerModel();
        $this->billingModel = new BillingTransactionModel();
    }

    public function index()
    {
        $userId = session()->get('user_id');
        
        // Get user stats
        $user = $this->userModel->find($userId);
        $serverStats = $this->serverModel->getServerStats($userId);
        $billingStats = $this->billingModel->getUserTransactionStats($userId);
        
        // Get recent servers
        $recentServers = $this->serverModel->getServersWithPlan($userId);
        
        // Get recent transactions
        $recentTransactions = $this->billingModel->getUserTransactions($userId, 5);

        $data = [
            'user' => $user,
            'stats' => [
                'total_servers' => $serverStats['total'],
                'running_servers' => $serverStats['running'],
                'stopped_servers' => $serverStats['stopped'],
                'balance' => $user['balance'],
                'total_spent' => $billingStats['total_spent']
            ],
            'servers' => $recentServers,
            'transactions' => $recentTransactions
        ];

        return view('dashboard/index', $data);
    }
}

/**
 * Server Management Controller
 */
class ServerController extends BaseController
{
    protected $serverModel;
    protected $planModel;
    protected $userModel;
    protected $activityLog;

    public function __construct()
    {
        $this->serverModel = new ServerModel();
        $this->planModel = new ServerPlanModel();
        $this->userModel = new UserModel();
        $this->activityLog = new \App\Models\ActivityLogModel();
    }

    /**
     * List all servers
     */
    public function index()
    {
        $userId = session()->get('user_id');
        $servers = $this->serverModel->getServersWithPlan($userId);

        return view('dashboard/servers/index', ['servers' => $servers]);
    }

    /**
     * Show create server form
     */
    public function create()
    {
        $plans = $this->planModel->getActivePlans();
        
        $osOptions = [
            'Ubuntu 22.04 LTS',
            'Ubuntu 20.04 LTS',
            'Debian 11',
            'CentOS 8',
            'AlmaLinux 8',
            'Rocky Linux 8',
            'Windows Server 2022',
            'Windows Server 2019'
        ];

        return view('dashboard/servers/create', [
            'plans' => $plans,
            'os_options' => $osOptions
        ]);
    }

    /**
     * Store new server
     */
    public function store()
    {
        $userId = session()->get('user_id');
        
        $validation = \Config\Services::validation();
        $rules = [
            'plan_id' => 'required|is_natural_no_zero',
            'server_name' => 'required|min_length[3]|max_length[255]',
            'os' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $validation->getErrors());
        }

        // Get plan details
        $planId = $this->request->getPost('plan_id');
        $plan = $this->planModel->find($planId);

        if (!$plan || $plan['status'] !== 'active') {
            return redirect()->back()
                ->with('error', 'Invalid or inactive plan selected');
        }

        // Check if user has sufficient balance (minimum $5)
        $user = $this->userModel->find($userId);
        if ($user['balance'] < 5.00) {
            return redirect()->to('/dashboard/billing/topup')
                ->with('error', 'Insufficient balance. Please topup your account to create a server.');
        }

        try {
            // Provision server
            $serverId = $this->serverModel->provisionServer([
                'user_id' => $userId,
                'plan_id' => $plan['id'],
                'server_name' => $this->request->getPost('server_name'),
                'os' => $this->request->getPost('os'),
                'cpu_cores' => $plan['cpu_cores'],
                'ram_gb' => $plan['ram_gb'],
                'storage_gb' => $plan['storage_gb']
            ]);

            // Log activity
            $this->activityLog->logActivity([
                'user_id' => $userId,
                'action' => 'server_created',
                'description' => 'Created server: ' . $this->request->getPost('server_name'),
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString()
            ]);

            return redirect()->to('/dashboard/servers')
                ->with('success', 'Server created successfully! It will be ready in a few moments.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create server: ' . $e->getMessage());
        }
    }

    /**
     * Show server details
     */
    public function show($id)
    {
        $userId = session()->get('user_id');
        
        $server = $this->serverModel->select('servers.*, server_plans.plan_name, server_plans.price_per_hour')
                                    ->join('server_plans', 'servers.plan_id = server_plans.id')
                                    ->where('servers.id', $id)
                                    ->where('servers.user_id', $userId)
                                    ->first();

        if (!$server) {
            return redirect()->to('/dashboard/servers')
                ->with('error', 'Server not found');
        }

        // Get monitoring data
        $monitoring = $this->serverModel->getServerMonitoring($id, 24);

        return view('dashboard/servers/show', [
            'server' => $server,
            'monitoring' => $monitoring
        ]);
    }

    /**
     * Start server
     */
    public function start($id)
    {
        $userId = session()->get('user_id');
        
        $server = $this->serverModel->where('id', $id)
                                    ->where('user_id', $userId)
                                    ->first();

        if (!$server) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Server not found'
            ]);
        }

        // Check balance
        $user = $this->userModel->find($userId);
        if ($user['balance'] < 1.00) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Insufficient balance to start server'
            ]);
        }

        if ($this->serverModel->startServer($id)) {
            $this->activityLog->logActivity([
                'user_id' => $userId,
                'action' => 'server_started',
                'description' => 'Started server: ' . $server['server_name'],
                'ip_address' => $this->request->getIPAddress()
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Server started successfully'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to start server'
        ]);
    }

    /**
     * Stop server
     */
    public function stop($id)
    {
        $userId = session()->get('user_id');
        
        $server = $this->serverModel->where('id', $id)
                                    ->where('user_id', $userId)
                                    ->first();

        if (!$server) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Server not found'
            ]);
        }

        if ($this->serverModel->stopServer($id)) {
            $this->activityLog->logActivity([
                'user_id' => $userId,
                'action' => 'server_stopped',
                'description' => 'Stopped server: ' . $server['server_name'],
                'ip_address' => $this->request->getIPAddress()
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Server stopped successfully'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to stop server'
        ]);
    }

    /**
     * Restart server
     */
    public function restart($id)
    {
        $userId = session()->get('user_id');
        
        $server = $this->serverModel->where('id', $id)
                                    ->where('user_id', $userId)
                                    ->first();

        if (!$server) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Server not found'
            ]);
        }

        if ($this->serverModel->restartServer($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Server restarted successfully'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to restart server'
        ]);
    }

    /**
     * Delete server
     */
    public function delete($id)
    {
        $userId = session()->get('user_id');
        
        $server = $this->serverModel->where('id', $id)
                                    ->where('user_id', $userId)
                                    ->first();

        if (!$server) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Server not found'
            ]);
        }

        if ($this->serverModel->terminateServer($id)) {
            $this->activityLog->logActivity([
                'user_id' => $userId,
                'action' => 'server_deleted',
                'description' => 'Deleted server: ' . $server['server_name'],
                'ip_address' => $this->request->getIPAddress()
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Server terminated successfully'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to terminate server'
        ]);
    }

    /**
     * Server monitoring page
     */
    public function monitoring($id)
    {
        $userId = session()->get('user_id');
        
        $server = $this->serverModel->where('id', $id)
                                    ->where('user_id', $userId)
                                    ->first();

        if (!$server) {
            return redirect()->to('/dashboard/servers')
                ->with('error', 'Server not found');
        }

        $monitoring = $this->serverModel->getServerMonitoring($id, 48);

        return view('dashboard/servers/monitoring', [
            'server' => $server,
            'monitoring' => $monitoring
        ]);
    }
}