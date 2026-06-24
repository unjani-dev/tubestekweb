<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Billing Transaction Model
 * Handles all financial transactions
 */
class BillingTransactionModel extends Model
{
    protected $table            = 'billing_transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'server_id',
        'transaction_type',
        'amount',
        'description',
        'balance_before',
        'balance_after',
        'status',
        'payment_method',
        'reference_id'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'user_id' => 'required|is_natural_no_zero',
        'transaction_type' => 'required|in_list[topup,charge,refund,penalty]',
        'amount' => 'required|decimal',
        'status' => 'in_list[pending,completed,failed,cancelled]'
    ];

    protected $validationMessages = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Create topup transaction
     */
    public function createTopup(int $userId, float $amount, string $paymentMethod = 'manual')
    {
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return false;
        }

        $transactionId = $this->insert([
            'user_id' => $userId,
            'transaction_type' => 'topup',
            'amount' => $amount,
            'description' => 'Balance topup via ' . $paymentMethod,
            'balance_before' => $user['balance'],
            'balance_after' => $user['balance'] + $amount,
            'status' => 'completed',
            'payment_method' => $paymentMethod,
            'reference_id' => $this->generateReferenceId()
        ]);

        $userModel->adjustBalance($userId, $amount, 'add');

        return $transactionId;
    }

    /**
     * Create charge transaction
     */
    public function createCharge(int $userId, float $amount, int $serverId, string $description)
    {
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user || $user['balance'] < $amount) {
            return false;
        }

        $transactionId = $this->insert([
            'user_id' => $userId,
            'server_id' => $serverId,
            'transaction_type' => 'charge',
            'amount' => $amount,
            'description' => $description,
            'balance_before' => $user['balance'],
            'balance_after' => $user['balance'] - $amount,
            'status' => 'completed',
            'reference_id' => $this->generateReferenceId()
        ]);

        // Deduct from user balance
        $userModel->adjustBalance($userId, $amount, 'subtract');

        return $transactionId;
    }

    /**
     * Get user transactions with pagination
     */
    public function getUserTransactions(int $userId, int $limit = 50, int $offset = 0)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit, $offset)
                    ->findAll();
    }

    /**
     * Get transaction statistics for user
     */
    public function getUserTransactionStats(int $userId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);

        $totalTopup = $builder->select('SUM(amount) as total')
                              ->where('user_id', $userId)
                              ->where('transaction_type', 'topup')
                              ->where('status', 'completed')
                              ->get()
                              ->getRow()->total ?? 0;

        $totalCharge = $builder->select('SUM(amount) as total')
                               ->where('user_id', $userId)
                               ->where('transaction_type', 'charge')
                               ->where('status', 'completed')
                               ->get()
                               ->getRow()->total ?? 0;

        return [
            'total_topup' => $totalTopup,
            'total_spent' => $totalCharge,
            'net_balance' => $totalTopup - $totalCharge
        ];
    }

    /**
     * Generate unique reference ID
     */
    private function generateReferenceId()
    {
        return 'TRX-' . date('YmdHis') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
    }

    /**
     * Get all transactions (admin)
     */
    public function getAllTransactions($limit = 100, $offset = 0)
    {
        return $this->select('billing_transactions.*, users.username, users.email')
                    ->join('users', 'billing_transactions.user_id = users.id')
                    ->orderBy('billing_transactions.created_at', 'DESC')
                    ->limit($limit, $offset)
                    ->findAll();
    }
}
