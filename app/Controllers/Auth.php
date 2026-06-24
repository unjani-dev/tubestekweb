<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ActivityLogModel;

/**
 * Authentication Controller
 * Handles user login, registration, password reset
 */
class Auth extends BaseController
{
    protected $userModel;
    protected $activityLog;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->activityLog = new ActivityLogModel();
    }

    /**
     * Show login form
     */
    public function login()
    {
        // Redirect if already logged in
        if (session()->has('user_id')) {
            return redirect()->to($this->getRedirectUrl());
        }

        return view('auth/login');
    }

    /**
     * Process login
     */
    public function doLogin()
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $validation->getErrors());
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = $this->userModel->verifyCredentials($username, $password);

        if (!$user) {
            // Log failed attempt
            $this->activityLog->logActivity([
                'user_id' => null,
                'action' => 'login_failed',
                'description' => 'Failed login attempt for: ' . $username,
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Invalid username or password');
        }

        // Check if account is active
        if ($user['status'] !== 'active') {
            return redirect()->back()
                ->with('error', 'Your account has been suspended. Please contact support.');
        }

        // Set session
        session()->set([
            'user_id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'full_name' => $user['full_name'],
            'logged_in' => true,
            'last_activity' => time()
        ]);

        // Log successful login
        $this->activityLog->logActivity([
            'user_id' => $user['id'],
            'action' => 'login',
            'description' => 'User logged in successfully',
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString()
        ]);

        return redirect()->to($this->getRedirectUrl())
            ->with('success', 'Welcome back, ' . $user['full_name'] . '!');
    }

    /**
     * Show registration form
     */
    public function register()
    {
        // Redirect if already logged in
        if (session()->has('user_id')) {
            return redirect()->to($this->getRedirectUrl());
        }

        return view('auth/register');
    }

    /**
     * Process registration
     */
    public function doRegister()
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'username' => 'required|min_length[3]|max_length[100]|is_unique[users.username]|alpha_numeric',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
            'full_name' => 'required|min_length[3]'
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
            'role' => 'user',
            'balance' => 0.00,
            'status' => 'active',
            'email_verified' => 0
        ];

        $userId = $this->userModel->insert($data);

        if ($userId) {
            // Log registration
            $this->activityLog->logActivity([
                'user_id' => $userId,
                'action' => 'registration',
                'description' => 'New user registered',
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString()
            ]);

            // Auto login after registration
            $user = $this->userModel->find($userId);
            session()->set([
                'user_id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
                'full_name' => $user['full_name'],
                'logged_in' => true,
                'last_activity' => time()
            ]);

            return redirect()->to('/dashboard')
                ->with('success', 'Registration successful! Welcome to Cloud Platform Mini.');
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'Registration failed. Please try again.');
    }

    /**
     * Logout
     */
    public function logout()
    {
        $userId = session()->get('user_id');

        if ($userId) {
            // Log logout
            $this->activityLog->logActivity([
                'user_id' => $userId,
                'action' => 'logout',
                'description' => 'User logged out',
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString()
            ]);
        }

        session()->destroy();
        
        return redirect()->to('/auth/login')
            ->with('success', 'You have been logged out successfully');
    }

    /**
     * Show forgot password form
     */
    public function forgotPassword()
    {
        return view('auth/forgot_password');
    }

    /**
     * Process forgot password
     */
    public function doForgotPassword()
    {
        $email = $this->request->getPost('email');
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()
                ->with('error', 'Please provide a valid email address');
        }

        $token = $this->userModel->createResetToken($email);

        if ($token) {
            // In production, send email with reset link
            // For now, just show the token
            return redirect()->back()
                ->with('success', 'Password reset link has been sent to your email');
        }

        return redirect()->back()
            ->with('error', 'Email address not found');
    }

    /**
     * Show reset password form
     */
    public function resetPassword($token)
    {
        $user = $this->userModel->verifyResetToken($token);

        if (!$user) {
            return redirect()->to('/auth/forgot-password')
                ->with('error', 'Invalid or expired reset token');
        }

        return view('auth/reset_password', ['token' => $token]);
    }

    /**
     * Process password reset
     */
    public function doResetPassword()
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'token' => 'required',
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->with('errors', $validation->getErrors());
        }

        $token = $this->request->getPost('token');
        $password = $this->request->getPost('password');

        if ($this->userModel->resetPassword($token, $password)) {
            return redirect()->to('/auth/login')
                ->with('success', 'Password reset successfully. Please login with your new password.');
        }

        return redirect()->back()
            ->with('error', 'Failed to reset password. Please try again.');
    }

    /**
     * Get redirect URL based on user role
     */
    private function getRedirectUrl()
    {
        $redirectUrl = session()->get('redirect_url');
        
        if ($redirectUrl) {
            session()->remove('redirect_url');
            return $redirectUrl;
        }

        $role = session()->get('role');
        return $role === 'admin' ? '/admin' : '/dashboard';
    }
}