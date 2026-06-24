<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History - Flizzy Cloud</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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
        }
        
        .table thead th {
            background-color: #f8f9fc;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 1.5px;
            font-weight: 800;
            color: #a3b1cc;
            padding: 20px;
            border: none;
        }
        .table tbody td {
            padding: 20px;
            border-bottom: 1px solid #f1f4f9;
            vertical-align: middle;
            font-size: 0.9rem;
        }
        
        .badge-status {
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
        }
        .bg-success-soft { background-color: #e6f9f4; color: #05cd99; }
        .bg-warning-soft { background-color: #fffbe6; color: #faad14; }
        .bg-danger-soft { background-color: #fff1f0; color: #ff4d4f; }
        
        .type-icon {
            width: 35px;
            height: 35px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

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
        <div class="offcanvas-header bg-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;">
            <h5 class="offcanvas-title fw-bold">Navigation</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
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
                        <a href="<?= base_url('dashboard/billing') ?>" class="text-decoration-none small fw-bold text-primary mb-2 d-block">
                            <i class="bi bi-arrow-left me-1"></i> Back to Billing
                        </a>
                        <h2 class="fw-bolder text-dark mb-0">Transaction History 📜</h2>
                        <p class="text-muted mb-0 small fw-medium">View and download all your past cloud expenditures</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-white border shadow-sm rounded-pill px-3 fw-bold small">
                            <i class="bi bi-filter me-1"></i> Filter
                        </button>
                        <a href="<?= base_url('dashboard/billing/export-csv') ?>" class="btn btn-dark rounded-pill px-4 shadow-sm fw-bold small border-0" style="background: #2b3674;">
                            <i class="bi bi-download me-2"></i> Export CSV
                        </a>
                    </div>
                </div>

                <div class="card bento-card overflow-hidden">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Date & Time</th>
                                        <th>Description</th>
                                        <th>Reference ID</th>
                                        <th>Amount</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-end pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($transactions)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="opacity-25 mb-3">
                                                    <i class="bi bi-folder2-open display-1"></i>
                                                </div>
                                                <h5 class="fw-bold">No transactions found</h5>
                                                <p class="text-muted small">You haven't made any transactions yet.</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($transactions as $trx): ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-bold text-dark"><?= date('M d, Y', strtotime($trx['created_at'])) ?></div>
                                                    <small class="text-muted fw-medium"><?= date('h:i A', strtotime($trx['created_at'])) ?></small>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($trx['transaction_type'] === 'topup'): ?>
                                                            <div class="type-icon bg-success-soft me-3"><i class="bi bi-plus-circle-fill"></i></div>
                                                        <?php else: ?>
                                                            <div class="type-icon bg-danger-soft me-3"><i class="bi bi-dash-circle-fill"></i></div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <div class="fw-bold text-dark"><?= esc($trx['description']) ?></div>
                                                            <small class="text-muted"><?= ucfirst($trx['payment_method'] ?? 'System') ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <code class="text-primary fw-bold bg-light px-2 py-1 rounded">#<?= esc($trx['reference_id'] ?? 'N/A') ?></code>
                                                </td>
                                                <td>
                                                    <?php 
                                                        $isPlus = ($trx['transaction_type'] === 'topup');
                                                        $color = $isPlus ? 'text-success' : 'text-dark';
                                                        $sign = $isPlus ? '+' : '-';
                                                    ?>
                                                    <span class="fw-bolder fs-6 <?= $color ?>"><?= $sign ?>$<?= number_format($trx['amount'], 2) ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <?php 
                                                        $status = $trx['status'];
                                                        $badgeClass = 'bg-secondary-soft';
                                                        if($status === 'completed') $badgeClass = 'bg-success-soft';
                                                        if($status === 'pending') $badgeClass = 'bg-warning-soft';
                                                        if($status === 'failed') $badgeClass = 'bg-danger-soft';
                                                    ?>
                                                    <span class="badge-status <?= $badgeClass ?>"><?= strtoupper($status) ?></span>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <a href="<?= base_url('dashboard/billing/invoice/' . $trx['id']) ?>" class="btn btn-light btn-sm rounded-pill fw-bold border-0 px-3">
                                                        Details
                                                    </a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>