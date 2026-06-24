<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\ServerModel;
use App\Models\BillingTransactionModel;
use App\Models\ActivityLogModel;

/**
 * Admin Dashboard Controller
 */
class AdminDashboard extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        $serverModel = new ServerModel();
        $billingModel = new BillingTransactionModel();

        // Get statistics
        $stats = [
            'total_users' => $userModel->countAllResults(false),
            'active_users' => $userModel->where('status', 'active')->countAllResults(),
            'total_servers' => $serverModel->countAllResults(false),
            'running_servers' => $serverModel->where('status', 'running')->countAllResults(),
            'total_revenue' => $this->getTotalRevenue($billingModel),
            'today_revenue' => $this->getTodayRevenue($billingModel)
        ];

        // Get recent activities
        $activityLog = new ActivityLogModel();
        $recentActivities = $activityLog->getAllActivities(10);

        return view('admin/dashboard', [
            'stats' => $stats,
            'activities' => $recentActivities
        ]);
    }

    private function getTotalRevenue($billingModel)
    {
        $db = \Config\Database::connect();
        $result = $db->table('billing_transactions')
                    ->select('SUM(amount) as total')
                    ->where('transaction_type', 'charge')
                    ->where('status', 'completed')
                    ->get()
                    ->getRow();
        
        return $result ? $result->total : 0;
    }
    private function getRevenue($todayOnly = false)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('billing_transactions')
                      ->selectSum('amount')
                      ->where('transaction_type', 'charge')
                      ->where('status', 'completed');
        
        if ($todayOnly) $builder->where('DATE(created_at)', date('Y-m-d'));
        
        $result = $builder->get()->getRow();
        return $result->amount ?? 0;
    }


    public function billing()
    {
        $db = \Config\Database::connect();
        $requests = $db->table('billing_transactions')
                       ->select('billing_transactions.*, users.username, users.email')
                       ->join('users', 'billing_transactions.user_id = users.id')
                       ->where('transaction_type', 'topup')
                       ->orderBy('created_at', 'DESC')
                       ->get()
                       ->getResultArray();

        return view('admin/billing/index', ['requests' => $requests]);
    }

    private function getTodayRevenue($billingModel)
    {
        $db = \Config\Database::connect();
        $result = $db->table('billing_transactions')
                    ->select('SUM(amount) as total')
                    ->where('transaction_type', 'charge')
                    ->where('status', 'completed')
                    ->where('DATE(created_at)', date('Y-m-d'))
                    ->get()
                    ->getRow();
        
        return $result ? $result->total : 0;
    }
}

/**
 * User Management Controller
 */
class UserManagement extends BaseController
{
    protected $userModel;
    protected $activityLog;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->activityLog = new ActivityLogModel();
    }

    /**
     * List all users
     */
    public function index()
    {
        $users = $this->userModel->orderBy('created_at', 'DESC')->findAll();
        
        return view('admin/users/index', ['users' => $users]);
    }

    /**
     * Create user form
     */
    public function create()
    {
        return view('admin/users/create');
    }

    /**
     * Store new user
     */
    public function store()
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'username' => 'required|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'full_name' => 'required',
            'role' => 'required|in_list[user,admin]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $validation->getErrors());
        }

        $data = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'full_name' => $this->request->getPost('full_name'),
            'phone' => $this->request->getPost('phone'),
            'role' => $this->request->getPost('role'),
            'balance' => $this->request->getPost('balance') ?? 0,
            'status' => 'active'
        ];

        if ($this->userModel->insert($data)) {
            return redirect()->to('/admin/users')
                ->with('success', 'User created successfully');
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'Failed to create user');
    }

    /**
     * Edit user form
     */
    public function edit($id)
    {
        $user = $this->userModel->find($id);
        
        if (!$user) {
            return redirect()->to('/admin/users')
                ->with('error', 'User not found');
        }

        return view('admin/users/edit', ['user' => $user]);
    }

    /**
     * Update user
     */
    public function update($id)
    {
        $user = $this->userModel->find($id);
        
        if (!$user) {
            return redirect()->to('/admin/users')
                ->with('error', 'User not found');
        }

        $data = [
            'full_name' => $this->request->getPost('full_name'),
            'phone' => $this->request->getPost('phone'),
            'role' => $this->request->getPost('role'),
            'status' => $this->request->getPost('status')
        ];

        // Update password if provided
        $newPassword = $this->request->getPost('password');
        if (!empty($newPassword)) {
            $data['password'] = $newPassword;
        }

        if ($this->userModel->update($id, $data)) {
            return redirect()->to('/admin/users')
                ->with('success', 'User updated successfully');
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'Failed to update user');
    }

    /**
     * Delete user
     */
    public function delete($id)
    {
        if ($id == session()->get('user_id')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cannot delete your own account'
            ]);
        }

        if ($this->userModel->delete($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to delete user'
        ]);
    }

    /**
     * Suspend user
     */
    public function suspend($id)
    {
        if ($this->userModel->update($id, ['status' => 'suspended'])) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'User suspended successfully'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to suspend user'
        ]);
    }

    /**
     * Activate user
     */
    public function activate($id)
    {
        if ($this->userModel->update($id, ['status' => 'active'])) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'User activated successfully'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to activate user'
            ]);
    }

    /**
     * Adjust user balance
     */
    public function adjustBalance($id)
    {
        $amount = $this->request->getPost('amount');
        $type = $this->request->getPost('type'); // add or subtract
        $reason = $this->request->getPost('reason');

        if ($this->userModel->adjustBalance($id, abs($amount), $type)) {
            // Log transaction
            $billingModel = new BillingTransactionModel();
            $user = $this->userModel->find($id);
            
            $billingModel->insert([
                'user_id' => $id,
                'transaction_type' => $type === 'add' ? 'topup' : 'penalty',
                'amount' => abs($amount),
                'description' => 'Admin adjustment: ' . $reason,
                'balance_before' => $type === 'add' ? $user['balance'] - $amount : $user['balance'] + $amount,
                'balance_after' => $user['balance'],
                'status' => 'completed',
                'payment_method' => 'admin'
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Balance adjusted successfully'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to adjust balance'
        ]);
    }
}

/**
 * Plan Management Controller
 */
class PlanManagement extends BaseController
{
    protected $planModel;

    public function __construct()
    {
        $this->planModel = new \App\Models\ServerPlanModel();
    }

    /**
     * List all plans
     */
    public function index()
    {
        $plans = $this->planModel->orderBy('price_per_month', 'ASC')->findAll();
        
        return view('admin/plans/index', ['plans' => $plans]);
    }

    /**
     * Create plan form
     */
    public function create()
    {
        return view('admin/plans/create');
    }

    /**
     * Store new plan
     */
    public function store()
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'plan_name' => 'required',
            'cpu_cores' => 'required|is_natural_no_zero',
            'ram_gb' => 'required|is_natural_no_zero',
            'storage_gb' => 'required|is_natural_no_zero',
            'price_per_hour' => 'required|decimal'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $validation->getErrors());
        }

        $pricePerHour = $this->request->getPost('price_per_hour');
        
        $data = [
            'plan_name' => $this->request->getPost('plan_name'),
            'cpu_cores' => $this->request->getPost('cpu_cores'),
            'ram_gb' => $this->request->getPost('ram_gb'),
            'storage_gb' => $this->request->getPost('storage_gb'),
            'bandwidth_gb' => $this->request->getPost('bandwidth_gb') ?? 1000,
            'price_per_hour' => $pricePerHour,
            'price_per_month' => $this->planModel->calculateMonthlyPrice($pricePerHour),
            'description' => $this->request->getPost('description'),
            'status' => 'active'
        ];

        if ($this->planModel->insert($data)) {
            return redirect()->to('/admin/plans')
                ->with('success', 'Plan created successfully');
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'Failed to create plan');
    }

    /**
     * Edit plan
     */
    public function edit($id)
    {
        $plan = $this->planModel->find($id);
        
        if (!$plan) {
            return redirect()->to('/admin/plans')
                ->with('error', 'Plan not found');
        }

        return view('admin/plans/edit', ['plan' => $plan]);
    }

    /**
     * Update plan
     */
    public function update($id)
    {
        $pricePerHour = $this->request->getPost('price_per_hour');
        
        $data = [
            'plan_name' => $this->request->getPost('plan_name'),
            'cpu_cores' => $this->request->getPost('cpu_cores'),
            'ram_gb' => $this->request->getPost('ram_gb'),
            'storage_gb' => $this->request->getPost('storage_gb'),
            'bandwidth_gb' => $this->request->getPost('bandwidth_gb'),
            'price_per_hour' => $pricePerHour,
            'price_per_month' => $this->planModel->calculateMonthlyPrice($pricePerHour),
            'description' => $this->request->getPost('description'),
            'status' => $this->request->getPost('status')
        ];

        if ($this->planModel->update($id, $data)) {
            return redirect()->to('/admin/plans')
                ->with('success', 'Plan updated successfully');
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'Failed to update plan');
    }

    /**
     * Delete plan
     */
    public function delete($id)
    {
        // Check if plan is in use
        $serverModel = new ServerModel();
        $inUse = $serverModel->where('plan_id', $id)->first();

        if ($inUse) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cannot delete plan that is currently in use'
            ]);
        }

        if ($this->planModel->delete($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Plan deleted successfully'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to delete plan'
        ]);
    }

    public function approveTopup($id)
    {
        $db = \Config\Database::connect();
        $userModel = new UserModel();
        
        $trx = $db->table('billing_transactions')->where('id', $id)->get()->getRow();

        // Validasi: Harus pending & tipe topup
        if (!$trx || $trx->status !== 'pending' || $trx->transaction_type !== 'topup') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid transaction state.']);
        }

        $db->transStart();

        // 1. Update status transaksi jadi Completed
        $db->table('billing_transactions')->where('id', $id)->update([
            'status' => 'completed',
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // 2. Tambahkan saldo ke User
        $userModel->adjustBalance($trx->user_id, $trx->amount, 'add');

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Database error.']);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Top-up approved! Balance credited.']);
    }

    public function rejectTopup($id)
    {
        $db = \Config\Database::connect();
        $trx = $db->table('billing_transactions')->where('id', $id)->get()->getRow();

        if (!$trx || $trx->status !== 'pending') {
            return $this->response->setJSON(['success' => false, 'message' => 'Only pending requests can be rejected.']);
        }

        $db->table('billing_transactions')->where('id', $id)->update([
            'status' => 'failed',
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON(['success' => true, 'message' => 'Transaction has been unapproved/rejected.']);
    }

    public function allTransactions()
{
    $db = \Config\Database::connect();
    $data['transactions'] = $db->table('billing_transactions')
                                ->select('billing_transactions.*, users.username')
                                ->join('users', 'billing_transactions.user_id = users.id')
                                ->orderBy('created_at', 'DESC')
                                ->get()
                                ->getResultArray();
                                
    return view('admin/billing/all_transactions', $data);
}
}