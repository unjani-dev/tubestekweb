<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\BillingTransactionModel;
use App\Models\ActivityLogModel;

/**
 * Billing Controller
 * Handles user billing, topup, and transaction history
 */
class BillingController extends BaseController
{
    protected $userModel;
    protected $billingModel;
    protected $activityLog;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->billingModel = new BillingTransactionModel();
        $this->activityLog = new ActivityLogModel();
    }

    /**
     * Billing overview
     */
    public function index()
    {
        $userId = session()->get('user_id');
        $userModel = new UserModel();
        $trxModel = new \App\Models\ServerModel(); // Atau model transaksi kamu

        // AMBIL DATA TERBARU DARI DATABASE (Bukan dari Session)
        $userData = $userModel->find($userId);
        
        // Ambil riwayat transaksi (Gunakan tabel billing_transactions)
        $db = \Config\Database::connect();
        $transactions = $db->table('billing_transactions')
                          ->where('user_id', $userId)
                          ->orderBy('created_at', 'DESC')
                          ->get()
                          ->getResultArray();

        $data = [
            'user'         => $userData, // Ini berisi balance asli dari DB
            'transactions' => $transactions
        ];

        return view('dashboard/billing/index', $data);
    }
    /**
     * Show topup form
     */
    public function topup()
    {
        $userId = session()->get('user_id');
        $user = $this->userModel->find($userId);

        return view('dashboard/billing/topup', ['user' => $user]);
    }

    /**
     * Process topup
     */
    public function doTopup()
    {
        $userId = session()->get('user_id');
        
        $validation = \Config\Services::validation();
        $rules = [
            'payment_method' => 'required|in_list[bank_transfer,credit_card,paypal,crypto,manual]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $validation->getErrors());
        }

        $presetAmount = (float) $this->request->getPost('amount');
        $customAmount = (float) $this->request->getPost('custom_amount');
        $amount = $customAmount > 0 ? $customAmount : $presetAmount;
        $paymentMethod = $this->request->getPost('payment_method');

        if ($amount <= 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Please select or enter a top-up amount.');
        }

        // Minimum topup $5
        if ($amount < 5.00) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Minimum topup amount is $5.00');
        }

        // Maximum topup $10,000
        if ($amount > 10000.00) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Maximum topup amount is $10,000.00');
        }

        try {
            // Create topup transaction
            $transactionId = $this->billingModel->createTopup($userId, $amount, $paymentMethod);

            if ($transactionId) {
                // Log activity
                $this->activityLog->logActivity([
                    'user_id' => $userId,
                    'action' => 'balance_topup',
                    'description' => 'Topped up balance: $' . number_format($amount, 2),
                    'ip_address' => $this->request->getIPAddress(),
                    'user_agent' => $this->request->getUserAgent()->getAgentString()
                ]);

                return redirect()->to('/dashboard/billing')
                    ->with('success', 'Balance topped up successfully! Added $' . number_format($amount, 2));
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to process topup');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Transaction history
     */
    public function history()
    {
        $userId = session()->get('user_id');
        
        // Pagination
        $perPage = 20;
        $page = $this->request->getGet('page') ?? 1;
        $offset = ($page - 1) * $perPage;

        $transactions = $this->billingModel->getUserTransactions($userId, $perPage, $offset);
        $total = $this->billingModel->where('user_id', $userId)->countAllResults();

        return view('dashboard/billing/history', [
            'transactions' => $transactions,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page
        ]);
    }

    /**
     * View invoice
     */
    public function invoice($id)
    {
        $userId = session()->get('user_id');
        
        $transaction = $this->billingModel->where('id', $id)
                                          ->where('user_id', $userId)
                                          ->first();

        if (!$transaction) {
            return redirect()->to('/dashboard/billing/history')
                ->with('error', 'Transaction not found');
        }

        return view('dashboard/billing/details', ['transaction' => $transaction]);
    }


    

}
