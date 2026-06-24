<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Cloud Platform Mini</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #dc3545 0%, #c82333 100%);
            color: white;
        }
        .nav-link {
            color: rgba(255,255,255,0.8);
            transition: all 0.3s;
        }
        .nav-link:hover, .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        .stat-card {
            border-left: 4px solid;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .admin-badge {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Admin Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-4">
                    <h3 class="mb-0"><i class="bi bi-shield-fill-check"></i> Admin Panel</h3>
                    <small>Cloud Platform Mini</small>
                </div>
                
                <nav class="nav flex-column px-3">
                    <a class="nav-link active rounded mb-1" href="<?= base_url('admin') ?>">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a class="nav-link rounded mb-1" href="<?= base_url('admin/users') ?>">
                        <i class="bi bi-people"></i> User Management
                    </a>
                    <a class="nav-link rounded mb-1" href="<?= base_url('admin/plans') ?>">
                        <i class="bi bi-layers"></i> Server Plans
                    </a>
                    <a class="nav-link rounded mb-1" href="<?= base_url('admin/servers') ?>">
                        <i class="bi bi-server"></i> All Servers
                    </a>
                    <a class="nav-link rounded mb-1" href="<?= base_url('admin/billing') ?>">
                        <i class="bi bi-cash-stack"></i> Billing & Revenue
                    </a>
                    <a class="nav-link rounded mb-1" href="<?= base_url('admin/settings') ?>">
                        <i class="bi bi-gear"></i> System Settings
                    </a>
                    <a class="nav-link rounded mb-1" href="<?= base_url('admin/logs') ?>">
                        <i class="bi bi-file-text"></i> Activity Logs
                    </a>
                    
                    <hr class="text-white">
                    
                    <a class="nav-link rounded mb-1" href="<?= base_url('dashboard') ?>">
                        <i class="bi bi-box-arrow-left"></i> User Dashboard
                    </a>
                    <a class="nav-link rounded text-danger bg-white" href="<?= base_url('auth/logout') ?>">
                        <i class="bi bi-power"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2>Admin Dashboard</h2>
                        <p class="text-muted mb-0">System overview and statistics</p>
                    </div>
                    <div>
                        <span class="admin-badge">
                            <i class="bi bi-shield-check"></i> Administrator
                        </span>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1">Total Users</p>
                                        <h3 class="mb-0"><?= number_format($stats['total_users']) ?></h3>
                                        <small class="text-success">
                                            <i class="bi bi-arrow-up"></i> <?= $stats['active_users'] ?> active
                                        </small>
                                    </div>
                                    <div class="text-primary">
                                        <i class="bi bi-people fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1">Total Servers</p>
                                        <h3 class="mb-0"><?= number_format($stats['total_servers']) ?></h3>
                                        <small class="text-success">
                                            <i class="bi bi-play-circle-fill"></i> <?= $stats['running_servers'] ?> running
                                        </small>
                                    </div>
                                    <div class="text-info">
                                        <i class="bi bi-server fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1">Total Revenue</p>
                                        <h3 class="mb-0">$<?= number_format($stats['total_revenue'], 2) ?></h3>
                                        <small class="text-muted">All time</small>
                                    </div>
                                    <div class="text-success">
                                        <i class="bi bi-currency-dollar fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1">Today's Revenue</p>
                                        <h3 class="mb-0">$<?= number_format($stats['today_revenue'], 2) ?></h3>
                                        <small class="text-muted">Last 24 hours</small>
                                    </div>
                                    <div class="text-warning">
                                        <i class="bi bi-graph-up-arrow fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <a href="<?= base_url('admin/users/create') ?>" class="btn btn-outline-primary w-100">
                                            <i class="bi bi-person-plus"></i> Add User
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <a href="<?= base_url('admin/plans/create') ?>" class="btn btn-outline-info w-100">
                                            <i class="bi bi-plus-circle"></i> Add Plan
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <a href="<?= base_url('admin/billing/run-charges') ?>" class="btn btn-outline-success w-100">
                                            <i class="bi bi-credit-card"></i> Run Billing
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <a href="<?= base_url('admin/logs') ?>" class="btn btn-outline-secondary w-100">
                                            <i class="bi bi-file-text"></i> View Logs
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Recent Activity</h5>
                                <a href="<?= base_url('admin/logs') ?>" class="btn btn-sm btn-outline-primary">
                                    View All <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                            <div class="card-body">
                                <?php if (empty($activities)): ?>
                                    <p class="text-muted text-center py-3">No recent activities</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>User</th>
                                                    <th>Action</th>
                                                    <th>Description</th>
                                                    <th>IP Address</th>
                                                    <th>Time</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($activities as $activity): ?>
                                                <tr>
                                                    <td>
                                                        <?= $activity['username'] ?? 'System' ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary"><?= esc($activity['action']) ?></span>
                                                    </td>
                                                    <td><?= esc($activity['description']) ?></td>
                                                    <td><code><?= esc($activity['ip_address']) ?></code></td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <?= date('M d, Y H:i', strtotime($activity['created_at'])) ?>
                                                        </small>
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
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>