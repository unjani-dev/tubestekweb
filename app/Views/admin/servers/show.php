<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instance Detail - Flizzy Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f2f5; color: #1e293b; }
        .bento-card { background: white; border-radius: 28px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
        .spec-box { background: #f8fafc; border-radius: 20px; padding: 20px; border: 1px solid #f1f5f9; }
        .status-pill { padding: 8px 16px; border-radius: 50px; font-weight: 800; font-size: 0.7rem; text-transform: uppercase; }
        .st-running { background: #e6f9f4; color: #05cd99; }
        .st-stopped { background: #fff1f0; color: #ff4d4f; }
        .owner-card { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: white; border-radius: 24px; }
        .info-label { font-size: 0.65rem; text-transform: uppercase; font-weight: 800; color: #94a3b8; letter-spacing: 1px; }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0">
        <?= view('admin/sidebar') ?>

        <div class="col-md-10 p-4 p-md-5">
            <div class="d-flex align-items-center mb-5">
                <a href="<?= base_url('admin/servers') ?>" class="btn btn-white shadow-sm rounded-circle me-3 border d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-arrow-left fw-bold text-dark"></i>
                </a>
                <div>
                    <h2 class="fw-800 text-dark mb-0">Instance Inspector 🔍</h2>
                    <p class="text-muted small fw-medium mb-0">Deep dive analysis for <strong><?= esc($server['server_name']) ?></strong></p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-8">
                    <div class="card bento-card p-4 p-md-5 h-100">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <span class="status-pill st-<?= $server['status'] ?> mb-3 d-inline-block">
                                    <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> <?= $server['status'] ?>
                                </span>
                                <h3 class="fw-800 text-dark"><?= esc($server['server_name']) ?></h3>
                                <p class="text-muted">OS Image: <span class="text-dark fw-bold"><?= esc($server['os']) ?></span></p>
                            </div>
                            <div class="text-end">
                                <p class="info-label mb-1">IP Address</p>
                                <h4 class="fw-800 text-primary"><?= $server['ip_address'] ?? '0.0.0.0' ?></h4>
                            </div>
                        </div>

                        <hr class="my-4 opacity-5">

                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="spec-box">
                                    <p class="info-label mb-2"><i class="bi bi-cpu"></i> Processor</p>
                                    <h5 class="fw-800 mb-0"><?= $server['p_cpu'] ?> vCPU</h5>
                                    <small class="text-muted">Dedicated Cores</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="spec-box">
                                    <p class="info-label mb-2"><i class="bi bi-memory"></i> Memory</p>
                                    <h5 class="fw-800 mb-0"><?= $server['p_ram'] ?> GB</h5>
                                    <small class="text-muted">DDR4 ECC RAM</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="spec-box">
                                    <p class="info-label mb-2"><i class="bi bi-hdd-network"></i> Storage</p>
                                    <h5 class="fw-800 mb-0"><?= $server['storage_gb'] ?> GB</h5>
                                    <small class="text-muted">NVMe SSD Storage</small>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 p-4 bg-light rounded-4">
                            <h6 class="fw-bold text-dark mb-3">Deployment Metadata</h6>
                            <div class="row text-center">
                                <div class="col-6 border-end">
                                    <p class="info-label mb-1">Created At</p>
                                    <span class="fw-bold small"><?= date('d M Y, H:i', strtotime($server['created_at'])) ?></span>
                                </div>
                                <div class="col-6">
                                    <p class="info-label mb-1">Current Plan</p>
                                    <span class="badge bg-primary rounded-pill px-3"><?= esc($server['plan_name']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card owner-card p-4 mb-4 border-0 shadow-lg">
                        <p class="info-label text-white opacity-50 mb-3">Account Owner</p>
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-white text-dark rounded-circle d-flex align-items-center justify-content-center fw-800" style="width: 50px; height: 50px; font-size: 1.2rem;">
                                <?= strtoupper(substr($server['username'], 0, 1)) ?>
                            </div>
                            <div class="ms-3">
                                <h6 class="fw-800 mb-0">@<?= esc($server['username']) ?></h6>
                                <small class="opacity-75"><?= esc($server['email']) ?></small>
                            </div>
                        </div>
                        <a href="<?= base_url('admin/users') ?>" class="btn btn-white btn-sm w-100 rounded-pill fw-bold py-2 mt-2">View User Profile</a>
                    </div>

                    <div class="card bento-card p-4">
                        <h6 class="fw-800 text-dark mb-3"><i class="bi bi-shield-check me-2 text-primary"></i>Audit Info</h6>
                        <ul class="list-unstyled small fw-medium text-muted">
                            <li class="mb-3 d-flex justify-content-between">
                                <span>Network Access</span>
                                <span class="text-success fw-bold">Public IP</span>
                            </li>
                            <li class="mb-3 d-flex justify-content-between">
                                <span>Firewall Status</span>
                                <span class="text-dark">Default Active</span>
                            </li>
                            <li class="d-flex justify-content-between">
                                <span>Billing Model</span>
                                <span class="text-dark">Hourly PAYG</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="mt-4 p-3 bg-white bg-opacity-50 rounded-4 border text-center">
                <p class="text-muted small fw-medium mb-0">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                    Admin Mode: Modifikasi instans ini hanya diizinkan melalui panel otorisasi khusus atau Database Manager.
                </p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>