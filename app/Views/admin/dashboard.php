<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Core Admin - Flizzy Cloud Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.3);
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #f0f2f5; 
            color: #1e293b;
            overflow-x: hidden;
        }
        .bento-card { 
            background: var(--glass-bg); 
            backdrop-filter: blur(10px);
            border-radius: 28px; 
            border: 1px solid var(--glass-border); 
            box-shadow: 0 10px 30px rgba(0,0,0,0.02); 
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
        }
        .bento-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 20px 40px rgba(99, 102, 241, 0.08); 
            border-color: rgba(99, 102, 241, 0.2);
        }
        .stat-icon { 
            width: 55px; 
            height: 55px; 
            border-radius: 18px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .revenue-card {
            background: #0f172a;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .revenue-card::before {
            content: "";
            position: absolute;
            top: -20%;
            right: -10%;
            width: 150px;
            height: 150px;
            background: rgba(99, 102, 241, 0.2);
            border-radius: 50%;
            filter: blur(40px);
        }
        .activity-item {
            padding: 15px;
            border-radius: 20px;
            background: rgba(255,255,255,0.4);
            margin-bottom: 12px;
            border: 1px solid transparent;
            transition: 0.3s;
        }
        .activity-item:hover {
            background: white;
            border-color: #e2e8f0;
        }
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
            100% { opacity: 1; transform: scale(1); }
        }
        /* Mobile adjustment */
        @media (max-width: 768px) {
            .content-area { padding: 20px !important; }
        }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0">
        <?= view('admin/sidebar') ?>

        <div class="col-md-10 p-4 p-md-5 content-area">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
                <div>
                    <h1 class="fw-800 text-dark mb-1" style="letter-spacing: -1px;">System Overview 🏢</h1>
                    <p class="text-muted fw-medium mb-0">
                        <span class="status-dot bg-success"></span> Infrastructure Heartbeat: <span class="text-success fw-bold">Optimal</span>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <div class="bg-white p-2 rounded-4 shadow-sm border px-4 d-flex align-items-center gap-3">
                        <div class="text-end">
                            <small class="text-muted d-block fw-bold" style="font-size: 0.6rem; letter-spacing: 1px;">GLOBAL TIME</small>
                            <span class="fw-bolder"><?= date('H:i') ?> <span class="text-primary">UTC</span></span>
                        </div>
                        <i class="bi bi-cpu-fill fs-4 text-primary"></i>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-xl-3 col-sm-6">
                    <div class="card bento-card p-4 h-100">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h2 class="fw-800 mb-1"><?= number_format($stats['total_users']) ?></h2> <p class="text-muted small fw-bold text-uppercase" style="letter-spacing: 1px;">Total Customers</p>
                        <div class="mt-2 fw-bold text-success small">
                            <i class="bi bi-arrow-up-short"></i> <?= $stats['active_users'] ?> Active Now </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card bento-card p-4 h-100">
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="bi bi-hdd-network-fill"></i>
                        </div>
                        <h2 class="fw-800 mb-1"><?= number_format($stats['total_servers']) ?></h2> <p class="text-muted small fw-bold text-uppercase" style="letter-spacing: 1px;">Nodes Deployed</p>
                        <div class="mt-2 fw-bold text-primary small">
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2"><?= $stats['running_servers'] ?> RUNNING</span> </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card bento-card p-4 h-100 revenue-card">
                        <div class="stat-icon bg-white bg-opacity-10 text-white">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <h2 class="fw-800 mb-1 text-white">$<?= number_format($stats['total_revenue'], 2) ?></h2> <p class="text-white text-opacity-50 small fw-bold text-uppercase" style="letter-spacing: 1px;">Total Revenue</p>
                        <div class="mt-2 fw-bold text-info small">Lifetime Platform Earnings</div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card bento-card p-4 h-100 border-2 border-primary border-opacity-25">
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <h2 class="fw-800 mb-1 text-success">$<?= number_format($stats['today_revenue'], 2) ?></h2> <p class="text-muted small fw-bold text-uppercase" style="letter-spacing: 1px;">Today's Inflow</p>
                        <div class="mt-2 fw-bold text-dark small">Automated Charges</div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card bento-card p-4 p-md-5">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-800 mb-0">Financial Analytics</h5>
                            <span class="badge bg-light text-dark border fw-bold px-3 py-2 rounded-pill">7 Days Trend</span>
                        </div>
                        <div style="height: 350px;">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card bento-card p-4 h-100">
                        <h5 class="fw-800 mb-4">Recent Activity 🪵</h5>
                        <div class="activity-feed">
                            <?php if(empty($activities)): ?>
                                <div class="text-center py-5 opacity-50">
                                    <i class="bi bi-slash-circle display-4"></i>
                                    <p class="mt-2 fw-bold small">No logs found</p>
                                </div>
                            <?php else: ?>
                                <?php foreach($activities as $act): ?> <div class="activity-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <span class="badge bg-primary bg-opacity-10 text-primary small rounded-pill mb-2 px-2 py-1" style="font-size: 0.6rem;">
                                            <i class="bi bi-person-circle"></i> <?= esc($act['username'] ?? 'System') ?> </span>
                                        <span class="text-muted fw-bold" style="font-size: 0.6rem;"><?= date('H:i', strtotime($act['created_at'])) ?></span> </div>
                                    <div class="fw-bold text-dark small"><?= esc($act['description']) ?></div> <div class="text-muted mt-1" style="font-size: 0.65rem;"><i class="bi bi-geo-alt"></i> <?= esc($act['ip_address'] ?? 'Internal') ?></div> </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Modern Gradient Chart Configuration
    const ctx = document.getElementById('revenueChart').getContext('2d');
    let chartGradient = ctx.createLinearGradient(0, 0, 0, 400);
    chartGradient.addColorStop(0, 'rgba(99, 102, 241, 0.4)');
    chartGradient.addColorStop(1, 'rgba(99, 102, 241, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Revenue',
                data: [650, 890, 700, 1200, 1100, 1450, 1800],
                borderColor: '#6366f1',
                borderWidth: 4,
                backgroundColor: chartGradient,
                fill: true,
                tension: 0.4,
                pointRadius: 6,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#6366f1',
                pointBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [5, 5], color: '#e2e8f0' }, border: { display: false } },
                x: { grid: { display: false }, border: { display: false } }
            }
        }
    });
</script>
</body>
</html>