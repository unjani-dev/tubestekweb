<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\ServerModel;
use App\Models\BillingTransactionModel;

class Reports extends BaseController
{
    /**
     * Main Reports Dashboard
     */
    public function index()
    {
        $db = \Config\Database::connect();
        $billingModel = new BillingTransactionModel();

        // 1. STATS: Total Revenue (Charges) & Total Deposits (Topups)
        $data['total_revenue'] = $db->table('billing_transactions')
            ->where('transaction_type', 'charge')
            ->where('status', 'completed')
            ->selectSum('amount')
            ->get()->getRow()->amount ?? 0;

        $data['total_deposits'] = $db->table('billing_transactions')
            ->where('transaction_type', 'topup')
            ->where('status', 'completed')
            ->selectSum('amount')
            ->get()->getRow()->amount ?? 0;

        // 2. STATS: User & Server Growth
        $data['user_count'] = (new UserModel())->countAllResults();
        $data['server_count'] = (new ServerModel())->countAllResults();

        // 3. CHART DATA: Revenue 7 Hari Terakhir
        $chartQuery = $db->query("
            SELECT DATE(created_at) as date, SUM(amount) as total 
            FROM billing_transactions 
            WHERE transaction_type = 'charge' 
            AND status = 'completed'
            AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at)
            ORDER BY DATE(created_at) ASC
        ");
        
        $data['chart_labels'] = [];
        $data['chart_data'] = [];
        foreach ($chartQuery->getResult() as $row) {
            $data['chart_labels'][] = date('d M', strtotime($row->date));
            $data['chart_data'][] = $row->total;
        }

        return view('admin/reports/index', $data);
    }
}