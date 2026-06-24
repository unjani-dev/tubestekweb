<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Ledger - Flizzy Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f2f5; color: #1e293b; }
        .bento-card { background: white; border-radius: 28px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.02); transition: 0.3s; }
        .bento-card:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(99, 102, 241, 0.05); }
        
        /* Table Styling */
        .table thead th { background: #f8fafc; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 1.5px; font-weight: 800; color: #94a3b8; padding: 20px; border: none; }
        .table tbody td { padding: 20px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        
        /* Transaction Specifics */
        .trx-type { padding: 6px 12px; border-radius: 12px; font-weight: 800; font-size: 0.65rem; text-transform: uppercase; }
        .type-topup { background: #e6f9f4; color: #05cd99; }
        .type-charge { background: #fff1f0; color: #ff4d4f; }
        .type-refund { background: #e0f2fe; color: #0ea5e9; }
        .type-penalty { background: #fff1f0; color: #ff4d4f; }
        
        .user-mini-avatar { width: 35px; height: 35px; border-radius: 10px; background: #6366f1; color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.8rem; }
        
        .status-pill { padding: 5px 12px; border-radius: 50px; font-weight: 700; font-size: 0.65rem; }
        .st-completed { background: #05cd99; color: white; }
        .st-pending { background: #faad14; color: white; }
        .st-failed, .st-cancelled { background: #ff4d4f; color: white; }

        @media (max-width: 768px) { .content-area { padding: 20px !important; } }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0">
        <?= view('admin/sidebar') ?>

        <div class="col-md-10 p-4 p-md-5 content-area">
            
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
                <div>
                    <h2 class="fw-800 text-dark mb-1">Financial Ledger 📜</h2>
                    <p class="text-muted small fw-medium mb-0">Complete historical overview of all credits and debits</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-white border rounded-pill px-4 fw-bold shadow-sm" onclick="window.print()">
                        <i class="bi bi-printer me-2"></i> Print Report
                    </button>
                    <button class="btn btn-dark rounded-pill px-4 fw-bold shadow-sm border-0" style="background: #2b3674;">
                        <i class="bi bi-download me-2"></i> Export CSV
                    </button>
                </div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card bento-card p-4 border-start border-4 border-success">
                        <small class="text-muted fw-bold text-uppercase" style="letter-spacing: 1px;">Total Inflow (Top-ups)</small>
                        <h3 class="fw-800 mb-0 text-dark">
                            <?php 
                                $totalIn = 0;
                                if (!empty($transactions)) {
                                    foreach ($transactions as $item) {
                                        if ($item['transaction_type'] == 'topup' && $item['status'] == 'completed') {
                                            $totalIn += $item['amount'];
                                        }
                                    }
                                }
                                echo '$' . number_format($totalIn, 2);
                            ?>
                        </h3>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card bento-card p-4 border-start border-4 border-danger">
                        <small class="text-muted fw-bold text-uppercase" style="letter-spacing: 1px;">Total Outflow (Service Charges)</small>
                        <h3 class="fw-800 mb-0 text-dark">
                            <?php 
                                $totalOut = 0;
                                if (!empty($transactions)) {
                                    foreach ($transactions as $item) {
                                        if ($item['transaction_type'] == 'charge' && $item['status'] == 'completed') {
                                            $totalOut += $item['amount'];
                                        }
                                    }
                                }
                                echo '$' . number_format($totalOut, 2);
                            ?>
                        </h3>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card bento-card p-4 border-start border-4 border-primary">
                        <small class="text-muted fw-bold text-uppercase" style="letter-spacing: 1px;">Net Platform Revenue</small>
                        <h3 class="fw-800 mb-0 text-primary">$<?= number_format($totalOut, 2) ?></h3>
                    </div>
                </div>
            </div>

            <div class="card bento-card overflow-hidden">
                <div class="card-header bg-white border-0 py-4 px-4">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="fw-800 mb-0">Global Transactions</h5>
                        </div>
                        <div class="col-auto">
                            <div class="input-group input-group-sm" style="width: 250px;">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                                <input type="text" id="trxSearch" class="form-control bg-light border-0" placeholder="Search user or ref...">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="trxTable">
                            <thead>
                                <tr>
                                    <th class="ps-4">User</th>
                                    <th>Reference & Date</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th class="text-end pe-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($transactions)): ?>
                                    <tr><td colspan="6" class="text-center py-5 text-muted fw-bold">No financial records found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($transactions as $trx): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="user-mini-avatar me-2"><?= strtoupper(substr($trx['username'], 0, 1)) ?></div>
                                                <div class="fw-bold text-dark"><?= esc($trx['username']) ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <code class="text-primary fw-bold" style="font-size: 0.75rem;">#<?= esc($trx['reference_id']) ?></code>
                                            <div class="text-muted small mt-1"><?= date('d M Y • H:i', strtotime($trx['created_at'])) ?></div>
                                        </td>
                                        <td>
                                            <div class="small fw-medium text-dark text-truncate" style="max-width: 200px;">
                                                <?= esc($trx['description']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="trx-type type-<?= esc($trx['transaction_type']) ?>">
                                                <?= esc($trx['transaction_type']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                                $isPositive = in_array($trx['transaction_type'], ['topup', 'refund']); 
                                                $operator = $isPositive ? '+' : '-';
                                                $textColor = $isPositive ? 'text-success' : 'text-dark';
                                            ?>
                                            <span class="fw-800 fs-6 <?= $textColor ?>">
                                                <?= $operator ?>$<?= number_format($trx['amount'], 2) ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <span class="status-pill st-<?= esc($trx['status']) ?>">
                                                <?= strtoupper(esc($trx['status'])) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-4 p-4 bg-primary bg-opacity-10 rounded-4 border border-primary border-opacity-10">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-shield-shaded text-primary fs-3"></i>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Financial Integrity Module</h6>
                        <p class="text-muted small mb-0">All transactions are immutable once marked as <strong>Completed</strong>. Platform revenue is calculated based on service charges only.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // Simple Search Filter
    document.getElementById('trxSearch').addEventListener('keyup', function() {
        let value = this.value.toLowerCase();
        let rows = document.querySelectorAll('#trxTable tbody tr');
        rows.forEach(row => {
            row.style.display = (row.innerText.toLowerCase().includes(value)) ? '' : 'none';
        });
    });
</script>

</body>
</html>