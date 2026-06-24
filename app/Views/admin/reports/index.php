<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revenue Analytics - Flizzy Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f2f5; color: #1e293b; }
        .bento-card { background: white; border-radius: 28px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
        .stat-icon { width: 50px; height: 50px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 15px; }
        .bg-indigo-soft { background: rgba(99, 102, 241, 0.1); color: #6366f1; }
        .bg-emerald-soft { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .bg-amber-soft { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .bg-purple-soft { background: rgba(168, 85, 247, 0.1); color: #a855f7; }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0">
        <?= view('admin/sidebar') ?>

        <div class="col-md-10 p-4 p-md-5">
            <div class="mb-5">
                <h2 class="fw-800 text-dark mb-1">Revenue Analytics 📈</h2>
                <p class="text-muted small fw-medium">Monitor your platform's financial growth and performance</p>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="card bento-card p-4">
                        <div class="stat-icon bg-emerald-soft"><i class="bi bi-wallet2"></i></div>
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem;">Total Revenue</small>
                        <h3 class="fw-800 text-dark mb-0">$<?= number_format($total_revenue, 2) ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bento-card p-4">
                        <div class="stat-icon bg-indigo-soft"><i class="bi bi-cash-coin"></i></div>
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem;">Total Deposits</small>
                        <h3 class="fw-800 text-dark mb-0">$<?= number_format($total_deposits, 2) ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bento-card p-4">
                        <div class="stat-icon bg-amber-soft"><i class="bi bi-people-fill"></i></div>
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem;">Total Users</small>
                        <h3 class="fw-800 text-dark mb-0"><?= $user_count ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bento-card p-4">
                        <div class="stat-icon bg-purple-soft"><i class="bi bi-hdd-stack-fill"></i></div>
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem;">Total Servers</small>
                        <h3 class="fw-800 text-dark mb-0"><?= $server_count ?></h3>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-8">
                    <div class="card bento-card p-4 p-md-5">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-800 mb-0">Revenue Trend (Last 7 Days)</h5>
                            <span class="badge bg-light text-primary border-0 rounded-pill px-3 py-2 fw-bold">Daily Growth</span>
                        </div>
                        <canvas id="revenueChart" style="max-height: 350px;"></canvas>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bento-card p-4 h-100">
                        <h5 class="fw-800 mb-4">Top Performance</h5>
                        <div class="d-flex flex-column gap-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-light rounded-3 p-2"><i class="bi bi-cpu text-primary"></i></div>
                                    <div><p class="mb-0 fw-bold small">CPU Load</p><small class="text-muted">Avg. Global</small></div>
                                </div>
                                <span class="fw-800 text-success">14%</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-light rounded-3 p-2"><i class="bi bi-memory text-purple"></i></div>
                                    <div><p class="mb-0 fw-bold small">RAM Usage</p><small class="text-muted">Total Alloc.</small></div>
                                </div>
                                <span class="fw-800 text-warning">62GB</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    // Gradient Background
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(99, 102, 241, 0.4)');
    gradient.addColorStop(1, 'rgba(99, 102, 241, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chart_labels) ?>,
            datasets: [{
                label: 'Revenue ($)',
                data: <?= json_encode($chart_data) ?>,
                borderColor: '#6366f1',
                borderWidth: 4,
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#6366f1',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, grid: { display: false }, ticks: { font: { weight: 'bold' } } },
                x: { grid: { display: false }, ticks: { font: { weight: 'bold' } } }
            }
        }
    });
</script>

</body>
</html>