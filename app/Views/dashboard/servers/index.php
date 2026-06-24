<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Servers - Flizzy Cloud Mini</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f7fe; color: #2b3674; }
        .bento-card { background: white; border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.03); transition: 0.3s; }
        .bento-card:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(0,0,0,0.05); }
        
        .server-status { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 6px; }
        .status-running { background: #05cd99; box-shadow: 0 0 10px #05cd99; }
        .status-stopped { background: #ee5d50; box-shadow: 0 0 10px #ee5d50; }
        .status-provisioning { background: #ffce20; animation: pulse 1.5s infinite; }
        
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
        
        /* Custom Button Style */
        .btn-power { width: 38px; height: 38px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; transition: 0.2s; border: 1px solid #eee; background: white; }
        .btn-power:hover { transform: scale(1.1); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .btn-power:disabled { opacity: 0.5; cursor: not-allowed; }

        @media (max-width: 767.98px) { .mobile-sidebar { width: 280px; } .content-area { margin-top: 60px; } }
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
            <h5 class="offcanvas-title fw-bold"><i class="bi bi-cloud-fill me-2"></i> Menu</h5>
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
                
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 border-bottom pb-3 gap-3">
                    <div>
                        <p class="text-muted mb-0 fw-bold small text-uppercase" style="letter-spacing: 1px;">Infrastructure</p>
                        <h2 class="fw-bolder text-dark mb-0"><i class="bi bi-server text-primary"></i> My Instances</h2>
                    </div>
                    <div>
                        <a href="<?= base_url('dashboard/servers/create') ?>" class="btn btn-dark rounded-pill px-4 shadow-sm fw-bold py-2">
                            <i class="bi bi-plus-lg text-warning me-1"></i> Deploy New Instance
                        </a>
                    </div>
                </div>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success rounded-4 border-0 shadow-sm mb-4 fw-bold">
                        <i class="bi bi-check-circle-fill me-2"></i> <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>

                <div class="card bento-card overflow-hidden">
                    <div class="card-body p-0">
                        <?php if (empty($servers)): ?>
                            <div class="text-center py-5 my-5">
                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-4 shadow-sm" style="width: 120px; height: 120px;">
                                    <i class="bi bi-hdd-network text-muted opacity-50" style="font-size: 4rem;"></i>
                                </div>
                                <h4 class="text-dark fw-bolder">No active instances found</h4>
                                <p class="text-muted mb-4 max-w-sm mx-auto">Build something amazing today by deploying your first server.</p>
                                <a href="<?= base_url('dashboard/servers/create') ?>" class="btn btn-primary btn-lg px-5 rounded-pill shadow-sm fw-bold" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                    Deploy Now
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light border-bottom">
                                        <tr>
                                            <th class="ps-4 py-3 text-uppercase text-muted small" style="letter-spacing: 1.5px;">Server</th>
                                            <th class="py-3 text-uppercase text-muted small" style="letter-spacing: 1.5px;">Specifications</th>
                                            <th class="py-3 text-uppercase text-muted small" style="letter-spacing: 1.5px;">Networking</th>
                                            <th class="py-3 text-uppercase text-muted small" style="letter-spacing: 1.5px;">Status</th>
                                            <th class="text-end pe-4 py-3 text-uppercase text-muted small" style="letter-spacing: 1.5px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="server-table-body">
                                        <?php foreach ($servers as $server): ?>
                                            <tr class="border-bottom border-light" id="row-<?= $server['id'] ?>">
                                                <td class="ps-4 py-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-primary bg-opacity-10 rounded-3 p-2 me-3 border text-center" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                                                            <?php if(stripos($server['os'], 'ubuntu') !== false): ?>
                                                                <i class="bi bi-ubuntu fs-3" style="color: #E95420;"></i>
                                                            <?php else: ?>
                                                                <i class="bi bi-terminal-fill fs-3 text-dark"></i>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div>
                                                            <a href="<?= base_url('dashboard/servers/' . $server['id']) ?>" class="text-decoration-none">
                                                                <h6 class="mb-0 fw-bolder text-dark hover-primary"><?= esc($server['server_name']) ?></h6>
                                                            </a>
                                                            <span class="text-muted small fw-medium"><?= esc($server['os']) ?></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <span class="badge bg-light text-muted border fw-bold"><?= $server['cpu_cores'] ?> vCPU</span>
                                                        <span class="badge bg-light text-muted border fw-bold"><?= $server['ram_gb'] ?>G RAM</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <code class="text-primary fw-bold"><?= esc($server['ip_address'] ?? '0.0.0.0') ?></code>
                                                </td>
                                                <td id="status-cell-<?= $server['id'] ?>">
                                                    <?php 
                                                        $statusClass = 'status-' . $server['status'];
                                                        $textClass = ($server['status'] === 'running') ? 'text-success' : (($server['status'] === 'stopped') ? 'text-danger' : 'text-warning');
                                                    ?>
                                                    <div class="d-flex align-items-center fw-bolder <?= $textClass ?>" style="font-size: 0.8rem;">
                                                        <span class="server-status <?= $statusClass ?>"></span>
                                                        <?= strtoupper($server['status']) ?>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <div class="d-flex justify-content-end gap-2" id="action-cell-<?= $server['id'] ?>">
                                                        <button onclick="controlInstance(<?= $server['id'] ?>, 'start')" 
                                                                class="btn-power text-success <?= $server['status'] === 'running' ? 'd-none' : '' ?>" 
                                                                title="Power On" id="btn-start-<?= $server['id'] ?>">
                                                            <i class="bi bi-play-fill fs-5"></i>
                                                        </button>

                                                        <button onclick="controlInstance(<?= $server['id'] ?>, 'stop')" 
                                                                class="btn-power text-danger <?= $server['status'] !== 'running' ? 'd-none' : '' ?>" 
                                                                title="Power Off" id="btn-stop-<?= $server['id'] ?>">
                                                            <i class="bi bi-power fs-5"></i>
                                                        </button>

                                                        <a href="<?= base_url('dashboard/servers/' . $server['id']) ?>" class="btn-power text-primary" title="Manage Settings">
                                                            <i class="bi bi-gear-fill fs-6"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card bento-card p-3 border-0 bg-primary bg-opacity-10">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-info-circle-fill text-primary fs-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Management Tip</h6>
                                    <p class="text-muted small mb-0">Klik pada nama server untuk melihat grafik performa dan akses konsol secara langsung.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    <div id="csrf-token-holder">
        <?= csrf_field() ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <script>
        /**
         * AJAX Server Controller Logic
         * Menjalankan start/stop tanpa refresh halaman
         */
        async function controlInstance(id, action) {
            const btn = document.getElementById(`btn-${action}-${id}`);
            const originalHTML = btn.innerHTML;
            
            // Ambil Token CSRF
            const csrfInput = document.querySelector('input[name="<?= csrf_token() ?>"]');
            const csrfToken = csrfInput.value;

            // Loading state
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            try {
                const response = await fetch(`<?= base_url('dashboard/servers') ?>/${id}/${action}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false,
                        position: 'top-end',
                        toast: true
                    });

                    // Update Tampilan Baris Secara Dinamis
                    updateRowUI(id, action);
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Security Error', 'Silakan refresh halaman (Token Expired)', 'warning');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        }

        /**
         * Fungsi untuk memperbarui badge status dan tombol aksi di dalam baris tabel
         */
        function updateRowUI(id, action) {
            const statusCell = document.getElementById(`status-cell-${id}`);
            const btnStart = document.getElementById(`btn-start-${id}`);
            const btnStop = document.getElementById(`btn-stop-${id}`);

            if (action === 'start') {
                statusCell.innerHTML = `
                    <div class="d-flex align-items-center fw-bolder text-success" style="font-size: 0.8rem;">
                        <span class="server-status status-running"></span> RUNNING
                    </div>
                `;
                btnStart.classList.add('d-none');
                btnStop.classList.remove('d-none');
            } else if (action === 'stop') {
                statusCell.innerHTML = `
                    <div class="d-flex align-items-center fw-bolder text-danger" style="font-size: 0.8rem;">
                        <span class="server-status status-stopped"></span> STOPPED
                    </div>
                `;
                btnStop.classList.add('d-none');
                btnStart.classList.remove('d-none');
            }
        }
    </script>
</body>
</html>