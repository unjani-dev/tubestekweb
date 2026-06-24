<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Flizzy Cloud Mini</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f4f7fe; /* Soft modern background */
            color: #2b3674;
        }
        .bento-card {
            background: white;
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .bento-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
        }
        .stat-icon-wrapper {
            width: 50px;
            height: 50px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .server-status {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 6px;
        }
        .status-running { background: #05cd99; box-shadow: 0 0 10px #05cd99; }
        .status-stopped { background: #ee5d50; }
        .status-provisioning { background: #ffce20; animation: pulse 1.5s infinite; }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        /* Custom Scrollbar for modern look */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #c5cddf; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #a3b1d1; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            
            <?= view('dashboard/sidebar') ?>

            <div class="col-md-10 p-4 p-md-5">
                
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <p class="text-muted mb-0 fw-bold small text-uppercase" style="letter-spacing: 1px;">Overview</p>
                        <h2 class="fw-bolder text-dark mb-0">Wassup, <?= esc(explode(' ', $user['full_name'])[0]) ?>! 👋</h2>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <a href="<?= base_url('dashboard/servers/create') ?>" class="btn btn-dark rounded-pill px-4 py-2 fw-bold shadow-sm d-none d-md-block">
                            <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Quick Deploy
                        </a>
                        <div class="bg-white rounded-circle p-1 shadow-sm border" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                            <span class="fw-bolder text-primary fs-5"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></span>
                        </div>
                    </div>
                </div>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success rounded-4 border-0 shadow-sm mb-4">
                        <i class="bi bi-check-circle-fill me-2"></i> <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>

                <div class="row g-4 mb-4">
                    <div class="col-xl-3 col-sm-6">
                        <div class="card bento-card h-100 p-2">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="stat-icon-wrapper bg-primary bg-opacity-10 text-primary">
                                        <i class="bi bi-gpu-card"></i>
                                    </div>
                                    <span class="badge bg-light text-muted border">Total</span>
                                </div>
                                <h3 class="fw-bolder mb-1"><?= $stats['total_servers'] ?> <span class="fs-6 text-muted fw-normal">Nodes</span></h3>
                                <p class="text-muted small mb-0 fw-medium">Active cloud instances</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-sm-6">
                        <div class="card bento-card h-100 p-2">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="stat-icon-wrapper" style="background: #e6f9f4; color: #05cd99;">
                                        <i class="bi bi-activity"></i>
                                    </div>
                                    <span class="badge" style="background: #05cd99; color: white;">Online</span>
                                </div>
                                <h3 class="fw-bolder mb-1"><?= $stats['running_servers'] ?> <span class="fs-6 text-muted fw-normal">Servers</span></h3>
                                <p class="text-success small mb-0 fw-bold"><i class="bi bi-arrow-up-right"></i> Running smoothly</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-sm-6">
                        <div class="card bento-card h-100 p-2" style="background: linear-gradient(135deg, #2b3674 0%, #1a2255 100%); color: white;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="stat-icon-wrapper bg-white bg-opacity-25 text-white">
                                        <i class="bi bi-wallet2"></i>
                                    </div>
                                    <a href="<?= base_url('dashboard/billing/topup') ?>" class="btn btn-sm btn-light rounded-pill fw-bold" style="font-size: 0.7rem;">Top Up</a>
                                </div>
                                <p class="text-white opacity-75 small mb-1 fw-medium text-uppercase">Wallet Balance</p>
                                <h2 class="fw-bolder mb-0">$<?= number_format($stats['balance'], 2) ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-sm-6">
                        <div class="card bento-card h-100 p-2 border-0" style="background: url('https://www.transparenttextures.com/patterns/cubes.png') #667eea; color: white;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="stat-icon-wrapper bg-white bg-opacity-25 text-white">
                                        <i class="bi bi-fire"></i>
                                    </div>
                                </div>
                                <p class="text-white opacity-75 small mb-1 fw-medium text-uppercase">Burn Rate (Spent)</p>
                                <h2 class="fw-bolder mb-0">$<?= number_format($stats['total_spent'], 2) ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-lg-8">
                        <div class="card bento-card h-100 p-3">
                            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                                <h5 class="fw-bolder mb-0">Resource Usage Analytics</h5>
                                <select class="form-select form-select-sm w-auto rounded-pill shadow-sm border-0 bg-light fw-bold">
                                    <option>This Week</option>
                                    <option>This Month</option>
                                </select>
                            </div>
                            <div class="card-body" style="position: relative; height: 300px;">
                                <canvas id="usageChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card bento-card h-100 p-3">
                            <div class="card-header bg-white border-0">
                                <h5 class="fw-bolder mb-0">System Status</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted fw-bold small">Global CPU Load</span>
                                        <span class="fw-bold text-dark">24%</span>
                                    </div>
                                    <div class="progress rounded-pill" style="height: 10px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 24%"></div>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted fw-bold small">Memory Allocation</span>
                                        <span class="fw-bold text-dark">68%</span>
                                    </div>
                                    <div class="progress rounded-pill" style="height: 10px;">
                                        <div class="progress-bar" role="progressbar" style="width: 68%; background-color: #f6c23e;"></div>
                                    </div>
                                </div>

                                <hr class="my-4 border-light">

                                <h6 class="fw-bold mb-3">Quick Tools</h6>
                                <div class="d-grid gap-2">
                                    <a href="<?= base_url('dashboard/servers/create') ?>" class="btn btn-light fw-bold text-start p-3 rounded-4 border">
                                        <i class="bi bi-box-seam text-primary me-2 fs-5"></i> Create Droplet
                                    </a>
                                    <a href="<?= base_url('dashboard/billing/history') ?>" class="btn btn-light fw-bold text-start p-3 rounded-4 border">
                                        <i class="bi bi-receipt text-success me-2 fs-5"></i> View Invoices
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="card bento-card p-3">
                            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center mb-2">
                                <h5 class="fw-bolder mb-0">Active Infrastructure</h5>
                                <a href="<?= base_url('dashboard/servers') ?>" class="text-decoration-none fw-bold small">View All <i class="bi bi-arrow-right"></i></a>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($servers)): ?>
                                    <div class="text-center py-5 bg-light rounded-4 m-3">
                                        <div class="fs-1 mb-2">🏜️</div>
                                        <h6 class="fw-bold text-dark">So empty here!</h6>
                                        <p class="text-muted small">Deploy a server to see it running.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive px-3 pb-3">
                                        <table class="table table-borderless align-middle mb-0">
                                            <tbody>
                                                <?php foreach (array_slice($servers, 0, 4) as $server): ?>
                                                <tr class="border-bottom border-light">
                                                    <td class="py-3">
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-light rounded-3 p-2 me-3 text-center border" style="width: 45px; height: 45px;">
                                                                <i class="bi bi-hdd-rack fs-5 text-secondary"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="fw-bold mb-0 text-dark"><?= esc($server['server_name']) ?></h6>
                                                                <span class="text-muted small font-monospace"><?= esc($server['ip_address']) ?></span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="py-3 text-end">
                                                        <?php 
                                                            $statusBg = ($server['status'] === 'running') ? '#05cd99' : '#a3b1d1';
                                                            $statusText = ($server['status'] === 'running') ? 'text-white' : 'text-dark';
                                                        ?>
                                                        <span class="badge rounded-pill px-3 py-2 <?= $statusText ?>" style="background: <?= $statusBg ?>; font-weight: 600; letter-spacing: 0.5px;">
                                                            <span class="server-status bg-white opacity-75"></span> <?= strtoupper($server['status']) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card bento-card p-3 h-100">
                            <div class="card-header bg-white border-0 mb-2">
                                <h5 class="fw-bolder mb-0">Recent Transactions</h5>
                            </div>
                            <div class="card-body p-0 px-3">
                                <?php if (empty($transactions)): ?>
                                    <p class="text-muted text-center py-4 bg-light rounded-4">No recent activity 😴</p>
                                <?php else: ?>
                                    <div class="d-flex flex-column gap-3">
                                        <?php foreach (array_slice($transactions, 0, 4) as $trx): ?>
                                        <div class="d-flex align-items-center justify-content-between p-3 rounded-4 border bg-light bg-opacity-50">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="bi <?= $trx['transaction_type'] === 'topup' ? 'bi-arrow-down-left text-success' : 'bi-arrow-up-right text-danger' ?> fs-5"></i>
                                                </div>
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-dark"><?= esc($trx['description']) ?></h6>
                                                    <small class="text-muted fw-medium"><?= date('d M, h:i A', strtotime($trx['created_at'])) ?></small>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <h6 class="fw-bolder mb-0 text-<?= $trx['transaction_type'] === 'topup' ? 'success' : 'dark' ?>">
                                                    <?= $trx['transaction_type'] === 'topup' ? '+' : '-' ?>$<?= number_format($trx['amount'], 2) ?>
                                                </h6>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('usageChart').getContext('2d');
            
            // Membuat gradient untuk area grafik
            let gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(102, 126, 234, 0.4)');
            gradient.addColorStop(1, 'rgba(102, 126, 234, 0.0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Bandwidth (GB)',
                        data: [12, 19, 15, 25, 22, 30, 28],
                        borderColor: '#667eea',
                        backgroundColor: gradient,
                        borderWidth: 3,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#667eea',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4 // Membuat garis melengkung smooth
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { borderDash: [5, 5], color: '#e2e8f0' },
                            border: { display: false }
                        },
                        x: {
                            grid: { display: false },
                            border: { display: false }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>