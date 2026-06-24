<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\ServerModel;
use App\Models\BillingTransactionModel;
use App\Models\ActivityLogModel;

/**
 * User Management Controller
 * Complete user administration (CRUD, suspend, balance adjustment)
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
            'username' => 'required|min_length[3]|max_length[100]|is_unique[users.username]|alpha_numeric',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'full_name' => 'required|min_length[3]',
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
            'status' => 'active',
            'email_verified' => 1
        ];

        $userId = $this->userModel->insert($data);

        if ($userId) {
            // Log activity
            $this->activityLog->logActivity([
                'user_id' => session()->get('user_id'),
                'action' => 'user_created',
                'description' => 'Admin created user: ' . $data['username'],
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString()
            ]);

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
 /**
     * Proses Update User (FIXED)
     */
    public function update($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) return redirect()->to('/admin/users')->with('error', 'User not found.');

        // Validasi Email (Kecualikan ID ini agar tidak error is_unique)
        $rules = [
            'full_name' => 'required|min_length[3]',
            'role'      => 'required|in_list[user,admin]',
            'status'    => 'required|in_list[active,suspended]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'full_name' => $this->request->getPost('full_name'),
            'phone'     => $this->request->getPost('phone'),
            'role'      => $this->request->getPost('role'),
            'status'    => $this->request->getPost('status')
        ];

        // Update Password hanya jika diisi
        $newPass = $this->request->getPost('password');
        if (!empty($newPass)) {
            if (strlen($newPass) < 8) {
                return redirect()->back()->with('error', 'Password must be at least 8 characters.');
            }
            $updateData['password'] = $newPass; // Akan di-hash otomatis oleh callback di UserModel
        }

        if ($this->userModel->update($id, $updateData)) {
            $this->activityLog->logActivity([
                'user_id'     => session()->get('user_id'),
                'action'      => 'user_updated',
                'description' => 'Admin updated profile for: ' . $user['username'],
                'ip_address'  => $this->request->getIPAddress()
            ]);

            return redirect()->to('/admin/users')->with('success', 'Changes for ' . $user['username'] . ' saved successfully.');
        }

        return redirect()->back()->with('error', 'Failed to update record.');
    }
    /**
     * Delete user
     */
    public function delete($id)
    {
        // Prevent deleting own account
        if ($id == session()->get('user_id')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cannot delete your own account'
            ]);
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found'
            ]);
        }

        if ($this->userModel->delete($id)) {
            // Log activity
            $this->activityLog->logActivity([
                'user_id' => session()->get('user_id'),
                'action' => 'user_deleted',
                'description' => 'Admin deleted user: ' . $user['username'],
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString()
            ]);

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
        $user = $this->userModel->find($id);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found'
            ]);
        }

        if ($this->userModel->update($id, ['status' => 'suspended'])) {
            // Stop all user's running servers
            $serverModel = new ServerModel();
            $servers = $serverModel->where('user_id', $id)
                                   ->where('status', 'running')
                                   ->findAll();
            
            foreach ($servers as $server) {
                $serverModel->stopServer($server['id']);
            }

            // Log activity
            $this->activityLog->logActivity([
                'user_id' => session()->get('user_id'),
                'action' => 'user_suspended',
                'description' => 'Admin suspended user: ' . $user['username'],
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString()
            ]);

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
        $user = $this->userModel->find($id);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found'
            ]);
        }

        if ($this->userModel->update($id, ['status' => 'active'])) {
            // Log activity
            $this->activityLog->logActivity([
                'user_id' => session()->get('user_id'),
                'action' => 'user_activated',
                'description' => 'Admin activated user: ' . $user['username'],
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString()
            ]);

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
        $user = $this->userModel->find($id);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found'
            ]);
        }

        $amount = (float) $this->request->getPost('amount');
        $type = $this->request->getPost('type'); // add or subtract
        $reason = $this->request->getPost('reason');

        if ($amount <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Amount must be greater than 0'
            ]);
        }

        if (!in_array($type, ['add', 'subtract'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid adjustment type'
            ]);
        }

        $balanceBefore = $user['balance'];

        if ($this->userModel->adjustBalance($id, $amount, $type)) {
            // Get updated user
            $updatedUser = $this->userModel->find($id);
            
            // Log transaction
            $billingModel = new BillingTransactionModel();
            $billingModel->insert([
                'user_id' => $id,
                'transaction_type' => $type === 'add' ? 'topup' : 'penalty',
                'amount' => $amount,
                'description' => 'Admin adjustment: ' . $reason,
                'balance_before' => $balanceBefore,
                'balance_after' => $updatedUser['balance'],
                'status' => 'completed',
                'payment_method' => 'admin',
                'reference_id' => 'ADJ-' . date('YmdHis')
            ]);

            // Log activity
            $this->activityLog->logActivity([
                'user_id' => session()->get('user_id'),
                'action' => 'balance_adjusted',
                'description' => "Admin adjusted balance for {$user['username']}: {$type} \${$amount}",
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString()
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Balance adjusted successfully',
                'new_balance' => $updatedUser['balance']
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to adjust balance'
        ]);
    }

    /**
     * View user details
     */
    public function show($id)
    {
        $user = $this->userModel->find($id);
        
        if (!$user) {
            return redirect()->to('/admin/users')
                ->with('error', 'User not found');
        }

        // Get user statistics
        $serverModel = new ServerModel();
        $billingModel = new BillingTransactionModel();

        $stats = [
            'total_servers' => $serverModel->where('user_id', $id)->countAllResults(),
            'running_servers' => $serverModel->where(['user_id' => $id, 'status' => 'running'])->countAllResults(),
            'total_spent' => $billingModel->getUserTransactionStats($id)['total_spent'],
            'transactions' => $billingModel->getUserTransactions($id, 10)
        ];

        $servers = $serverModel->getServersWithPlan($id);

        return view('admin/users/show', [
            'user' => $user,
            'stats' => $stats,
            'servers' => $servers
        ]);
    }
}