<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Infrastructure Overview - Flizzy Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f2f5; color: #1e293b; }
        .bento-card { background: white; border-radius: 28px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.02); transition: 0.3s; }
        .status-pill { padding: 6px 14px; border-radius: 50px; font-weight: 800; font-size: 0.65rem; text-transform: uppercase; }
        .st-running { background: #e6f9f4; color: #05cd99; }
        .st-stopped { background: #fff1f0; color: #ff4d4f; }
        .st-provisioning { background: #fffbe6; color: #faad14; }
        
        .table thead th { background: #f8fafc; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 1.5px; font-weight: 800; color: #94a3b8; padding: 20px; border: none; }
        .table tbody td { padding: 20px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        
        .user-link { color: #6366f1; text-decoration: none; font-weight: 700; }
        .user-link:hover { text-decoration: underline; }
        
        .read-only-badge { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; padding: 4px 12px; border-radius: 8px; font-size: 0.7rem; font-weight: 800; }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0">
        <?= view('admin/sidebar') ?>

        <div class="col-md-10 p-4 p-md-5 content-area">
            
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <h2 class="fw-800 text-dark mb-0">Infrastructure Monitor 📡</h2>
                        <span class="read-only-badge"><i class="bi bi-eye-fill"></i> READ-ONLY MODE</span>
                    </div>
                    <p class="text-muted small fw-medium mb-0">Real-time overview of all deployed instances across the platform</p>
                </div>
                <div class="d-flex gap-3">
                    <div class="bg-white p-3 rounded-4 shadow-sm border text-center" style="min-width: 100px;">
                        <small class="text-muted fw-bold d-block small">ACTIVE</small>
                        <span class="fw-800 text-success"><?= $stats['running'] ?></span>
                    </div>
                    <div class="bg-white p-3 rounded-4 shadow-sm border text-center" style="min-width: 100px;">
                        <small class="text-muted fw-bold d-block small">STOPPED</small>
                        <span class="fw-800 text-danger"><?= $stats['stopped'] ?></span>
                    </div>
                </div>
            </div>

            <div class="card bento-card overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Instance Name</th>
                                    <th>Owner</th>
                                    <th>Plan / Specs</th>
                                    <th>Network (IP)</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($servers)): ?>
                                    <tr><td colspan="6" class="text-center py-5 text-muted fw-bold">No instances deployed yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($servers as $s): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bolder text-dark mb-0"><?= esc($s['server_name']) ?></div>
                                            <small class="text-muted fw-medium">OS: <?= esc($s['os']) ?></small>
                                        </td>
                                        <td>
                                            <a href="#" class="user-link">@<?= esc($s['username']) ?></a>
                                            <div class="small text-muted"><?= esc($s['full_name']) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-primary border-0 rounded-pill px-2"><?= esc($s['plan_name']) ?></span>
                                            <div class="small text-muted mt-1"><?= $s['cpu_cores'] ?> Core / <?= $s['ram_gb'] ?>GB</div>
                                        </td>
                                        <td>
                                            <code class="text-dark fw-bold"><?= $s['ip_address'] ?? '0.0.0.0' ?></code>
                                            <div class="small text-muted mt-1"><i class="bi bi-geo-alt"></i> Singapore-1</div>
                                        </td>
                                        <td>
                                            <span class="status-pill st-<?= $s['status'] ?>">
                                                <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> <?= strtoupper($s['status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="<?= base_url('admin/servers/'.$s['id']) ?>" class="btn btn-light btn-sm rounded-pill px-3 fw-bold border-0">
                                                View Info
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

            <div class="mt-4 p-3 bg-warning bg-opacity-10 rounded-4 border border-warning border-opacity-20">
                <small class="text-dark fw-bold"><i class="bi bi-info-circle-fill me-2"></i> Admin Security Policy:</small>
                <p class="text-muted small mb-0 mt-1">Anda berada di mode pemantauan. Untuk mematikan atau menghapus server, gunakan panel terminasi khusus atau hubungi pemilik akun secara langsung.</p>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>