<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\BillingTransactionModel;
use CodeIgniter\API\ResponseTrait;

class BillingManagement extends BaseController
{
    use ResponseTrait;

    protected $billingModel;
    protected $userModel;

    public function __construct()
    {
        $this->billingModel = new BillingTransactionModel();
        $this->userModel = new UserModel();
    }

    /**
     * Tampilkan histori transaksi seluruh user.
     */
    public function index()
    {
        $data['transactions'] = $this->billingModel->getAllTransactions(100);

        return view('admin/billing/index', $data);
    }

    /**
     * Tampilkan semua log transaksi (Topup + Charge)
     */
    public function transactions()
    {
        $data['transactions'] = $this->billingModel->getAllTransactions(100);
        return view('admin/billing/all_transactions', $data);
    }

    /**
     * Proses APPROVE Top-up (Pending -> Completed)
     */
    public function approve($id)
    {
        $trx = $this->billingModel->find($id);

        if (!$trx || $trx['status'] !== 'pending') {
            return $this->response->setJSON(['success' => false, 'message' => 'Transaction not found or already processed.']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Update status transaksi
        $this->billingModel->update($id, [
            'status' => 'completed',
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // 2. Tambah saldo user (Gunakan nominal dari transaksi)
        $this->userModel->adjustBalance($trx['user_id'], $trx['amount'], 'add');

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Database error during approval.']);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Payment approved! User balance updated.']);
    }

    /**
     * Proses REJECT Top-up (Pending -> Failed)
     */
    public function reject($id)
    {
        $trx = $this->billingModel->find($id);

        if (!$trx || $trx['status'] !== 'pending') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid transaction.']);
        }

        if ($this->billingModel->update($id, ['status' => 'failed'])) {
            return $this->response->setJSON(['success' => true, 'message' => 'Transaction rejected.']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to reject.']);
    }
}
