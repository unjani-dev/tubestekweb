<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing & Payments - Flizzy Cloud Mini</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f4f7fe;
            color: #2b3674;
        }
        .bento-card {
            background: white;
            border-radius: 24px;
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.02);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .bento-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.05);
        }
        .stat-icon-wrapper {
            width: 55px;
            height: 55px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
        }
        .balance-card {
            background: linear-gradient(135deg, #05cd99 0%, #11998e 100%);
            color: white;
            overflow: hidden;
            position: relative;
        }
        .balance-card::after {
            content: '';
            position: absolute;
            top: -20%;
            right: -10%;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        
        /* Custom Table Styling */
        .table-responsive { border-radius: 20px; }
        .table thead th {
            background-color: #f8f9fc;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            font-weight: 700;
            color: #a3b1cc;
            padding: 18px;
            border: none;
        }
        .table tbody td { padding: 18px; border-bottom: 1px solid #f1f4f9; vertical-align: middle; }
        
        .badge-pill { padding: 8px 16px; border-radius: 50px; font-weight: 700; font-size: 0.7rem; }

        @media (max-width: 767.98px) {
            .content-area { margin-top: 70px; }
            .mobile-sidebar { width: 280px; }
        }
    </style>
</head>
<body>

    <nav class="navbar bg-white shadow-sm fixed-top d-md-none border-bottom px-3 z-3">
        <a class="navbar-brand fw-bold text-dark d-flex align-items-center" href="#">
            <i class="bi bi-cloud-fill text-primary me-2"></i> Flizzy Cloud
        </a>
        <button class="btn btn-light border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
            <i class="bi bi-list fs-3"></i>
        </button>
    </nav>

    <div class="offcanvas offcanvas-start mobile-sidebar" tabindex="-1" id="mobileSidebar">
        <div class="offcanvas-body p-0" style="background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);">
             <?= view('dashboard/sidebar') ?> 
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            
            <?= view('dashboard/sidebar') ?>

            <div class="col-md-10 p-4 p-md-5 content-area">
                
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
                    <div>
                        <p class="text-muted mb-1 fw-bold small text-uppercase" style="letter-spacing: 1.5px;">Financial Center</p>
                        <h2 class="fw-bolder text-dark mb-0">Billing & Payments 💸</h2>
                    </div>
                    <div>
                        <a href="<?= base_url('dashboard/billing/topup') ?>" class="btn btn-dark rounded-pill px-4 py-2 shadow-lg fw-bold border-0" style="background: #2b3674;">
                            <i class="bi bi-plus-circle-fill text-warning me-2"></i> Add Funds
                        </a>
                    </div>
                </div>

                <?php if(session()->getFlashdata('success')): ?>
                    <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success fw-bold rounded-4 mb-4">
                        <i class="bi bi-check-circle-fill me-2"></i><?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>
                <?php if(session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger fw-bold rounded-4 mb-4">
                        <i class="bi bi-exclamation-circle-fill me-2"></i><?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <div class="row g-4 mb-5">
                    
                    <div class="col-md-4">
                        <div class="card bento-card balance-card h-100">
                            <div class="card-body p-4 p-md-5">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="stat-icon-wrapper bg-white bg-opacity-20">
                                        <i class="bi bi-wallet2 fs-3"></i>
                                    </div>
                                    <span class="badge bg-white text-success fw-bolder px-3 py-2 rounded-pill">REAL-TIME DB</span>
                                </div>
                                <p class="mb-1 opacity-75 fw-bold text-uppercase small" style="letter-spacing: 1px;">Fresh Balance</p>
                                <h1 class="mb-0 fw-bolder display-4">$<?= number_format($user['balance'] ?? 0, 2) ?></h1>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card bento-card h-100">
                            <div class="card-body p-4 p-md-5">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="stat-icon-wrapper" style="background: #fff1f0; color: #ff4d4f;">
                                        <i class="bi bi-graph-down-arrow"></i>
                                    </div>
                                    <span class="text-muted fw-bold small">This Month</span>
                                </div>
                                <p class="mb-1 text-muted fw-bold text-uppercase small" style="letter-spacing: 1px;">Burn Rate (Spent)</p>
                                <h1 class="mb-0 fw-bolder display-5 text-danger">
                                    <?php 
                                        $totalSpent = 0;
                                        if(!empty($transactions)){
                                            foreach($transactions as $t){
                                                if($t['transaction_type'] === 'charge' && $t['status'] === 'completed') {
                                                    $totalSpent += $t['amount'];
                                                }
                                            }
                                        }
                                        echo '$' . number_format($totalSpent, 2);
                                    ?>
                                </h1>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card bento-card h-100">
                            <div class="card-body p-4 p-md-5 d-flex flex-column justify-content-center">
                                <p class="text-muted fw-bold text-uppercase small mb-3" style="letter-spacing: 1px;">Account Status</p>
                                <?php if($user['balance'] > 5): ?>
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon-wrapper me-3" style="background: #e6f9f4; color: #05cd99;">
                                            <i class="bi bi-shield-check"></i>
                                        </div>
                                        <div>
                                            <h4 class="fw-bolder mb-0 text-success">Healthy</h4>
                                            <small class="text-muted fw-medium">Service auto-renews</small>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon-wrapper me-3" style="background: #fffbe6; color: #faad14;">
                                            <i class="bi bi-exclamation-circle"></i>
                                        </div>
                                        <div>
                                            <h4 class="fw-bolder mb-0 text-warning">Critical</h4>
                                            <small class="text-muted fw-medium">Top up to avoid suspension</small>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card bento-card p-2 overflow-hidden">
                    <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bolder text-dark">Activity Log 🪵</h5>
                        <button class="btn btn-light rounded-pill border-0 fw-bold px-3 py-2 small shadow-sm">
                            <i class="bi bi-download me-2"></i> PDF Report
                        </button>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Transaction Details</th>
                                        <th>Method</th>
                                        <th>Reference</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end pe-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($transactions)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <div class="opacity-50">
                                                    <i class="bi bi-receipt-cutoff display-3"></i>
                                                    <p class="mt-3 fw-bold">No data to display yet.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($transactions as $trx): ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-bolder text-dark mb-0"><?= esc($trx['description']) ?></div>
                                                    <small class="text-muted fw-medium"><?= date('M d, Y • h:i A', strtotime($trx['created_at'])) ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark border-0 px-3 py-2 rounded-pill fw-bold">
                                                        <?= strtoupper(str_replace('_', ' ', $trx['payment_method'] ?? 'System')) ?>
                                                    </span>
                                                </td>
                                                <td><code class="text-primary small fw-bold">#<?= esc($trx['reference_id'] ?? 'N/A') ?></code></td>
                                                <td class="text-end">
                                                    <?php 
                                                        $isIncome = ($trx['transaction_type'] === 'topup');
                                                        $color = $isIncome ? 'text-success' : 'text-dark';
                                                        $sign = $isIncome ? '+' : '-';
                                                    ?>
                                                    <span class="fw-bolder fs-5 <?= $color ?>"><?= $sign ?>$<?= number_format($trx['amount'], 2) ?></span>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <?php 
                                                        $status = $trx['status'];
                                                        $class = 'bg-secondary';
                                                        if($status === 'completed') $class = 'bg-success';
                                                        if($status === 'pending') $class = 'bg-warning text-dark';
                                                        if($status === 'failed') $class = 'bg-danger';
                                                    ?>
                                                    <span class="badge badge-pill <?= $class ?>"><?= strtoupper($status) ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
