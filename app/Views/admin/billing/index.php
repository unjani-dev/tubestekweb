<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Transaction History - Admin Core</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .bento-card { background: white; border-radius: 24px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
        .table thead th { background: #f1f5f9; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 1.5px; font-weight: 800; color: #64748b; padding: 15px 20px; border: none; }
        .table tbody td { padding: 20px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .status-badge { padding: 6px 14px; border-radius: 50px; font-weight: 800; font-size: 0.65rem; text-transform: uppercase; border: 1px solid transparent; }
        .st-pending { background: #fffbe6; color: #faad14; border-color: rgba(250, 173, 20, 0.2); }
        .st-completed { background: #e6f9f4; color: #05cd99; border-color: rgba(5, 205, 153, 0.2); }
        .st-failed, .st-cancelled { background: #fff1f0; color: #ff4d4f; border-color: rgba(255, 77, 79, 0.2); }
        .trx-type { padding: 6px 12px; border-radius: 12px; font-weight: 800; font-size: 0.65rem; text-transform: uppercase; }
        .type-topup { background: #e6f9f4; color: #05cd99; }
        .type-charge { background: #fff1f0; color: #ff4d4f; }
        .type-refund { background: #e0f2fe; color: #0ea5e9; }
        .type-penalty { background: #fff1f0; color: #ff4d4f; }
        .user-mini-avatar { width: 36px; height: 36px; border-radius: 10px; background: #6366f1; color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.8rem; }
        @media (max-width: 768px) { .content-area { padding: 20px !important; } }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0">
        <?= view('admin/sidebar') ?>

        <div class="col-md-10 p-4 p-md-5 content-area">
            <div class="mb-5 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h2 class="fw-800 text-dark mb-1">User Transaction History</h2>
                    <p class="text-muted small fw-medium mb-0">Histori semua top-up, pembelian domain, server charge, refund, dan penalty user.</p>
                </div>
                <div class="bg-white px-3 py-2 rounded-4 border shadow-sm">
                    <span class="text-muted small fw-bold">SYSTEM TIME:</span>
                    <span class="fw-bold ms-1 text-primary"><?= date('H:i') ?> UTC</span>
                </div>
            </div>

            <div class="card bento-card overflow-hidden">
                <div class="card-header bg-white border-0 py-4 px-4">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="fw-800 mb-0">User Financial Activity</h5>
                        </div>
                        <div class="col-auto">
                            <div class="input-group input-group-sm" style="width: 260px;">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                                <input type="text" id="trxSearch" class="form-control bg-light border-0" placeholder="Search user, ref, type...">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="trxTable">
                            <thead>
                                <tr>
                                    <th class="ps-4">User / Date</th>
                                    <th>Reference</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th class="text-end pe-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($transactions)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <i class="bi bi-inbox text-muted display-3 opacity-25"></i>
                                            <p class="text-muted fw-bold mt-2">No transactions found.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($transactions as $trx): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="user-mini-avatar me-2"><?= strtoupper(substr($trx['username'] ?? 'U', 0, 1)) ?></div>
                                                    <div>
                                                        <div class="fw-bolder text-dark mb-0"><?= esc($trx['username'] ?? 'Unknown') ?></div>
                                                        <small class="text-muted"><?= date('d M Y, H:i', strtotime($trx['created_at'])) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><code class="text-dark small fw-bold">#<?= esc($trx['reference_id'] ?? 'N/A') ?></code></td>
                                            <td>
                                                <div class="small fw-medium text-dark text-truncate" style="max-width: 260px;">
                                                    <?= esc($trx['description'] ?? '-') ?>
                                                </div>
                                                <small class="text-muted"><?= strtoupper(str_replace('_', ' ', $trx['payment_method'] ?? 'system')) ?></small>
                                            </td>
                                            <td>
                                                <span class="trx-type type-<?= esc($trx['transaction_type']) ?>">
                                                    <?= esc($trx['transaction_type']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                    $isPositive = in_array($trx['transaction_type'], ['topup', 'refund'], true);
                                                    $operator = $isPositive ? '+' : '-';
                                                    $textColor = $isPositive ? 'text-success' : 'text-dark';
                                                ?>
                                                <span class="fw-800 fs-6 <?= $textColor ?>"><?= $operator ?>$<?= number_format($trx['amount'], 2) ?></span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <span class="status-badge st-<?= esc($trx['status']) ?>"><?= strtoupper(esc($trx['status'])) ?></span>
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

<script>
    document.getElementById('trxSearch').addEventListener('keyup', function() {
        const value = this.value.toLowerCase();
        document.querySelectorAll('#trxTable tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
        });
    });
</script>

</body>
</html>
