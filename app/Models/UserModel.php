<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\ServerModel; // Wajib ditambahkan agar fungsi getUserStats tidak error

/**
 * User Model - Enterprise Level (Fixed & Optimized)
 * Handles all user-related database operations with security
 */
class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    // Field yang diizinkan untuk diisi/diubah
    protected $allowedFields    = [
        'username', 'email', 'password', 'full_name', 'phone',
        'role', 'balance', 'status', 'email_verified',
        'verification_token', 'reset_token', 'reset_token_expire', 'last_login'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Matikan validasi Model agar tidak bentrok dengan validasi di Controller Auth.php
    protected $skipValidation = true; 

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['hashPassword'];
    protected $beforeUpdate   = ['hashPassword'];

    // ==========================================
    // FUNGSI AUTENTIKASI & KEAMANAN
    // ==========================================

    /**
     * Hash password before saving
     */
    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_BCRYPT);
        }
        return $data;
    }

    /**
     * Verify user credentials
     */
    public function verifyCredentials(string $username, string $password)
    {
        $user = $this->where('username', $username)
                     ->orWhere('email', $username)
                     ->first();

        if (!$user) return false;
        
        if (!password_verify($password, $user['password'])) return false;

        // Update last login
        $this->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);

        return $user;
    }

    public function createResetToken(string $email)
    {
        $user = $this->where('email', $email)->first();
        if (!$user) return false;

        $token = bin2hex(random_bytes(32));
        $expire = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->update($user['id'], [
            'reset_token' => $token,
            'reset_token_expire' => $expire
        ]);

        return $token;
    }

    public function verifyResetToken(string $token)
    {
        $user = $this->where('reset_token', $token)
                     ->where('reset_token_expire >', date('Y-m-d H:i:s'))
                     ->first();

        return $user ?: false;
    }

    public function resetPassword(string $token, string $newPassword)
    {
        $user = $this->verifyResetToken($token);
        if (!$user) return false;

        return $this->update($user['id'], [
            'password' => $newPassword, // Akan otomatis di-hash oleh callback
            'reset_token' => null,
            'reset_token_expire' => null
        ]);
    }

    // ==========================================
    // FUNGSI TRANSAKSI & CLOUD BILLING
    // ==========================================

    /**
     * Adjust user balance (for topup/charges)
     */
    public function adjustBalance(int $userId, float $amount, string $type = 'add')
    {
        $user = $this->find($userId);
        if (!$user) return false;

        $newBalance = $type === 'add' ? $user['balance'] + $amount : $user['balance'] - $amount;
        if ($newBalance < 0) $newBalance = 0;

        return $this->update($userId, ['balance' => $newBalance]);
    }

    /**
     * Get user statistics for Dashboard
     */
    public function getUserStats(int $userId)
    {
        $serverModel = new ServerModel(); // Sekarang tidak akan error
        
        return [
            'total_servers' => $serverModel->where('user_id', $userId)->countAllResults(),
            'running_servers' => $serverModel->where(['user_id' => $userId, 'status' => 'running'])->countAllResults(),
            'stopped_servers' => $serverModel->where(['user_id' => $userId, 'status' => 'stopped'])->countAllResults(),
            'total_spent' => $this->getTotalSpent($userId),
            'balance' => $this->find($userId)['balance']
        ];
    }

    /**
     * Get total amount spent by user
     */
    private function getTotalSpent(int $userId)
    {
        $db = \Config\Database::connect();
        $result = $db->table('billing_transactions')
                     ->select('SUM(amount) as total')
                     ->where('user_id', $userId)
                     ->where('transaction_type', 'charge')
                     ->where('status', 'completed')
                     ->get()
                     ->getRow();

        return $result ? $result->total : 0;
    }

    /**
     * Check if user has sufficient balance
     */
    public function hasSufficientBalance(int $userId, float $amount)
    {
        $user = $this->find($userId);
        return $user && $user['balance'] >= $amount;
    }
}