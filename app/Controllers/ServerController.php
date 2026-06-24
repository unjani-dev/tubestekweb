<?php

namespace App\Controllers;

use App\Models\ServerModel;
use App\Models\ServerPlanModel;
use App\Models\UserModel;
use App\Models\ActivityLogModel;

/**
 * Server Controller
 * Handles all server operations for users
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
        $this->activityLog = new ActivityLogModel();
    }

    /**
     * List all user's servers
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
            'Debian 12',
            'CentOS 8',
            'AlmaLinux 8',
            'Rocky Linux 8',
            'Fedora 38',
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
            'server_name' => 'required|min_length[3]|max_length[255]|alpha_dash',
            'os' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $validation->getErrors());
        }

        $planId = $this->request->getPost('plan_id');
        $plan = $this->planModel->find($planId);

        if (!$plan || $plan['status'] !== 'active') {
            return redirect()->back()
                ->with('error', 'Invalid or inactive plan selected');
        }

        // Check if user has sufficient balance
        $user = $this->userModel->find($userId);
        if ($user['balance'] < 5.00) {
            return redirect()->to('/dashboard/billing/topup')
                ->with('error', 'Insufficient balance. Minimum $5.00 required to create a server.');
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
            log_message('error', 'Server creation failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create server. Please try again.');
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

        // Check if already running
        if ($server['status'] === 'running') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Server is already running'
            ]);
        }

        // Check if terminated
        if ($server['status'] === 'terminated') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cannot start terminated server'
            ]);
        }

        // Check balance
        $user = $this->userModel->find($userId);
        if ($user['balance'] < 1.00) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Insufficient balance to start server. Please topup your account.'
            ]);
        }

        if ($this->serverModel->startServer($id)) {
            // Log activity
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

        // Check if already stopped
        if ($server['status'] === 'stopped') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Server is already stopped'
            ]);
        }

        if ($this->serverModel->stopServer($id)) {
            // Log activity
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

        // Check if terminated
        if ($server['status'] === 'terminated') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cannot restart terminated server'
            ]);
        }

        if ($this->serverModel->restartServer($id)) {
            // Log activity
            $this->activityLog->logActivity([
                'user_id' => $userId,
                'action' => 'server_restarted',
                'description' => 'Restarted server: ' . $server['server_name'],
                'ip_address' => $this->request->getIPAddress()
            ]);

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
     * Delete/Terminate server
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

        // Check if already terminated
        if ($server['status'] === 'terminated') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Server is already terminated'
            ]);
        }

        if ($this->serverModel->terminateServer($id)) {
            // Log activity
            $this->activityLog->logActivity([
                'user_id' => $userId,
                'action' => 'server_deleted',
                'description' => 'Terminated server: ' . $server['server_name'],
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

        // Get monitoring data for last 48 hours
        $monitoring = $this->serverModel->getServerMonitoring($id, 48);

        return view('dashboard/servers/monitoring', [
            'server' => $server,
            'monitoring' => $monitoring
        ]);
    }

    /**
     * Get server logs
     */
    public function logs($id)
    {
        $userId = session()->get('user_id');
        
        $server = $this->serverModel->where('id', $id)
                                    ->where('user_id', $userId)
                                    ->first();

        if (!$server) {
            return redirect()->to('/dashboard/servers')
                ->with('error', 'Server not found');
        }

        // Get activity logs for this server
        $logs = $this->activityLog->where('description LIKE', '%' . $server['server_name'] . '%')
                                  ->where('user_id', $userId)
                                  ->orderBy('created_at', 'DESC')
                                  ->limit(50)
                                  ->findAll();

        return view('dashboard/servers/logs', [
            'server' => $server,
            'logs' => $logs
        ]);
    }
}