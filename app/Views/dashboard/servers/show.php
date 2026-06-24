<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($server['server_name']) ?> - Management | Flizzy Cloud</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
            box-shadow: 0 10px 30px rgba(0,0,0,0.02);
            transition: 0.3s;
        }
        
        /* Glassmorphism Badge */
        .status-pill { 
            padding: 8px 20px; 
            border-radius: 50px; 
            font-weight: 800; 
            font-size: 0.75rem; 
            letter-spacing: 1px; 
            display: inline-flex; 
            align-items: center; 
        }
        .glow-running { 
            background: #e6f9f4; 
            color: #05cd99; 
            border: 1px solid #05cd99; 
            box-shadow: 0 0 15px rgba(5, 205, 153, 0.2); 
        }
        .glow-stopped { 
            background: #fef2f2; 
            color: #ee5d50; 
            border: 1px solid #ee5d50; 
        }
        
        /* Terminal Console Styling */
        .console-box { 
            background: #0d1117; 
            color: #58a6ff; 
            font-family: 'Fira Code', 'Courier New', monospace; 
            border-radius: 20px; 
            padding: 20px; 
            font-size: 0.85rem; 
            height: 320px; 
            overflow-y: auto; 
            border: 1px solid #30363d; 
        }
        .console-line { margin-bottom: 4px; padding-left: 10px; border-left: 2px solid transparent; }
        .console-success { color: #3fb950; border-left-color: #3fb950; }
        .console-warn { color: #d29922; border-left-color: #d29922; }
        .console-error { color: #f85149; border-left-color: #f85149; }
        
        /* Buttons Custom */
        .btn-action { 
            border-radius: 16px; 
            padding: 12px 22px; 
            font-weight: 800; 
            transition: 0.3s; 
            border: none; 
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-action:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .btn-action:disabled { opacity: 0.6; transform: none; }
        
        /* Mobile Viewport Fixes */
        @media (max-width: 767.98px) {
            .content-area { margin-top: 70px; }
            .mobile-sidebar { width: 280px; }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 10px; }
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
                
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
                    <div class="d-flex align-items-center">
                        <a href="<?= base_url('dashboard/servers') ?>" class="btn btn-white shadow-sm rounded-circle me-3 border d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-arrow-left fw-bold fs-5 text-dark"></i>
                        </a>
                        <div>
                            <p class="text-muted mb-0 fw-bold small text-uppercase" style="letter-spacing: 1px;">Compute Engine</p>
                            <h2 class="fw-bolder mb-0 text-dark"><?= esc($server['server_name']) ?></h2>
                            <div class="d-flex align-items-center mt-1 gap-2">
                                <span id="main-status-badge" class="status-pill <?= $server['status'] === 'running' ? 'glow-running' : 'glow-stopped' ?>">
                                    <i class="bi bi-lightning-charge-fill me-2"></i><span id="status-text"><?= strtoupper($server['status']) ?></span>
                                </span>
                                <span class="text-muted fw-bold small px-2 py-1 bg-white rounded border border-light shadow-sm">
                                    <i class="bi bi-globe-americas me-1"></i> <?= esc($server['ip_address'] ?? 'Assigning...') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 bg-white p-2 rounded-4 shadow-sm border align-items-center">
                        <button onclick="controlServer('start')" id="btn-start" class="btn-action btn-light text-success <?= $server['status'] === 'running' ? 'd-none' : '' ?>" title="Power On">
                            <i class="bi bi-play-fill fs-4"></i>
                        </button>
                        <button onclick="controlServer('stop')" id="btn-stop" class="btn-action btn-light text-danger <?= $server['status'] !== 'running' ? 'd-none' : '' ?>" title="Shut Down">
                            <i class="bi bi-power fs-4"></i>
                        </button>
                        <button onclick="controlServer('restart')" id="btn-restart" class="btn-action btn-light text-warning" title="Reboot">
                            <i class="bi bi-arrow-clockwise fs-4"></i>
                        </button>
                        <div class="vr mx-2 opacity-10"></div>
                        <button onclick="confirmDestroy()" class="btn-action btn-light text-muted" title="Destroy Server">
                            <i class="bi bi-trash3 fs-5"></i>
                        </button>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-4 order-lg-2">
                        <div class="card bento-card p-4 mb-4">
                            <h6 class="fw-bolder text-uppercase small text-muted mb-4" style="letter-spacing: 1px;">Configuration Details</h6>
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted fw-medium small">Operating System</span>
                                    <span class="fw-bold text-dark"><i class="bi bi-ubuntu text-warning me-1"></i> <?= $server['os'] ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted fw-medium small">Service Tier</span>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border-0 rounded-pill px-3 py-2 fw-bold"><?= $server['plan_name'] ?></span>
                                </div>
                                <hr class="my-1 opacity-5">
                                <div class="row g-2">
                                    <div class="col-4">
                                        <div class="bg-light p-3 rounded-4 text-center border">
                                            <small class="d-block text-muted fw-bold mb-1" style="font-size: 0.55rem;">vCPU</small>
                                            <span class="fw-bolder small"><?= $server['cpu_cores'] ?> Core</span>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="bg-light p-3 rounded-4 text-center border">
                                            <small class="d-block text-muted fw-bold mb-1" style="font-size: 0.55rem;">RAM</small>
                                            <span class="fw-bolder small"><?= $server['ram_gb'] ?> GB</span>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="bg-light p-3 rounded-4 text-center border">
                                            <small class="d-block text-muted fw-bold mb-1" style="font-size: 0.55rem;">STORAGE</small>
                                            <span class="fw-bolder small"><?= $server['storage_gb'] ?>G</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card bento-card p-4 bg-dark text-white shadow-lg overflow-hidden position-relative">
                            <div class="position-absolute top-0 end-0 p-3 opacity-10">
                                <i class="bi bi-credit-card-2-front display-1"></i>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2 z-1">
                                <h6 class="fw-bold mb-0 opacity-75 small">Monthly Cost Est.</h6>
                                <i class="bi bi-info-circle opacity-50"></i>
                            </div>
                            <h2 class="fw-bolder mb-0 z-1">$<?= number_format($server['price_per_hour'] * 720, 2) ?></h2>
                            <small class="opacity-50 fw-medium">Rate: $<?= $server['price_per_hour'] ?> / hour</small>
                        </div>
                    </div>

                    <div class="col-lg-8 order-lg-1">
                        <div class="card bento-card p-4 mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bolder mb-0">Resource Monitoring</h5>
                                <span class="badge bg-light text-primary border shadow-sm px-3 py-2 rounded-pill small fw-bold">Real-time Stream</span>
                            </div>
                            <div style="height: 280px;">
                                <canvas id="realtimeChart"></canvas>
                            </div>
                        </div>

                        <div class="card bento-card p-4 overflow-hidden" style="background: #0d1117;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold text-white opacity-75 mb-0"><i class="bi bi-terminal-fill me-2 text-primary"></i> Instance System Logs</h6>
                                <div class="d-flex gap-1 opacity-50">
                                    <span class="rounded-circle bg-danger" style="width: 10px; height: 10px;"></span>
                                    <span class="rounded-circle bg-warning" style="width: 10px; height: 10px;"></span>
                                    <span class="rounded-circle bg-success" style="width: 10px; height: 10px;"></span>
                                </div>
                            </div>
                            <div class="console-box" id="console">
                                </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div id="csrf-token-area">
        <?= csrf_field() ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // ==========================================
        // 1. FAKE CONSOLE LOGIC (Simulasi Booting)
        // ==========================================
        const consoleEl = document.getElementById('console');
        const logs = [
            { t: "[  OK  ] Initializing Hypervisor interface...", c: "console-success" },
            { t: "[  OK  ] Mounting virtual disk /dev/vda1 (NVMe)", c: "console-success" },
            { t: "[ INFO ] Starting Cloud-Init network configuration...", c: "" },
            { t: "[  OK  ] Assigned IPv4: <?= $server['ip_address'] ?>", c: "console-success" },
            { t: "[ WARN ] Entropy pool is low, seeding from hardware...", c: "console-warn" },
            { t: "[  OK  ] SSH Key injection completed.", c: "console-success" },
            { t: "[  OK  ] Systemd: All services operational.", c: "console-success" },
            { t: "[ INFO ] Instance '<?= $server['server_name'] ?>' is ready.", c: "fw-bold text-white" }
        ];

        function addLogLine(text, className = "") {
            const line = document.createElement('div');
            line.className = 'console-line ' + className;
            line.innerHTML = `<span class="opacity-25 me-2">${new Date().toLocaleTimeString()}</span> ${text}`;
            consoleEl.appendChild(line);
            consoleEl.scrollTop = consoleEl.scrollHeight;
        }

        // Jalankan animasi log satu per satu
        let logIndex = 0;
        const logTimer = setInterval(() => {
            if (logIndex < logs.length) {
                addLogLine(logs[logIndex].t, logs[logIndex].c);
                logIndex++;
            } else {
                addLogLine("<span class='text-primary fw-bold'>root@flizzy-cloud:~#</span> <span class='placeholder-glow'>_</span>");
                clearInterval(logTimer);
            }
        }, 900);


        // ==========================================
        // 2. SERVER CONTROL AJAX (DENGAN FIX CSRF)
        // ==========================================
        async function controlServer(action) {
            const btn = event.currentTarget;
            const originalContent = btn.innerHTML;
            
            // AMBIL TOKEN CSRF TERBARU (Sangat Penting!)
            const csrfInput = document.querySelector('input[name="<?= csrf_token() ?>"]');
            const csrfToken = csrfInput.value;

            // UI Feedback
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
            addLogLine(`[ INFO ] Sending ${action.toUpperCase()} command to hypervisor...`, "console-warn");

            try {
                const response = await fetch(`<?= base_url('dashboard/servers/'.$server['id']) ?>/${action}`, {
                    method: 'POST',
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken 
                    }
                });
                
                const res = await response.json();

                if (res.success) {
                    Swal.fire({ 
                        icon: 'success', 
                        title: 'Success!', 
                        text: res.message, 
                        background: '#fff',
                        timer: 2000, 
                        showConfirmButton: false 
                    });
                    
                    addLogLine(`[  OK  ] Operation ${action.toUpperCase()} processed successfully.`, "console-success");
                    
                    // Update Status Badge & Buttons Dynamically
                    updateUI(action);
                } else {
                    Swal.fire({ icon: 'error', title: 'Action Failed', text: res.message });
                    addLogLine(`[ ERROR ] Command rejected: ${res.message}`, "console-error");
                }
            } catch (err) {
                console.error(err);
                Swal.fire({ icon: 'error', title: 'Security/Network Error', text: 'Permintaan ditolak. Coba refresh halaman.' });
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        }

        function updateUI(action) {
            const badge = document.getElementById('main-status-badge');
            const text = document.getElementById('status-text');
            const btnStart = document.getElementById('btn-start');
            const btnStop = document.getElementById('btn-stop');

            if (action === 'stop') {
                badge.className = 'status-pill glow-stopped';
                text.innerText = 'STOPPED';
                btnStart.classList.remove('d-none');
                btnStop.classList.add('d-none');
            } else if (action === 'start') {
                badge.className = 'status-pill glow-running';
                text.innerText = 'RUNNING';
                btnStop.classList.remove('d-none');
                btnStart.classList.add('d-none');
            } else if (action === 'restart') {
                addLogLine("[ INFO ] System reboot sequence initiated...", "console-warn");
            }
        }

        // ==========================================
        // 3. DESTROY/DELETE CONFIRMATION
        // ==========================================
        function confirmDestroy() {
            Swal.fire({
                title: 'Destroy Server?',
                text: "Warning: This action is permanent. All disk data will be wiped!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ee5d50',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Destroy Forever',
                cancelButtonText: 'Keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                    controlServer('delete').then(() => {
                        setTimeout(() => window.location.href = '<?= base_url('dashboard/servers') ?>', 2000);
                    });
                }
            });
        }

        // ==========================================
        // 4. REALTIME CHART (Chart.js)
        // ==========================================
        const ctx = document.getElementById('realtimeChart').getContext('2d');
        let chartGradient = ctx.createLinearGradient(0, 0, 0, 300);
        chartGradient.addColorStop(0, 'rgba(102, 126, 234, 0.2)');
        chartGradient.addColorStop(1, 'rgba(102, 126, 234, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['12m', '10m', '8m', '6m', '4m', '2m', 'Now'],
                datasets: [{
                    label: 'CPU Usage %',
                    data: [5, 12, 8, 22, 18, 25, 14],
                    borderColor: '#667eea',
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    fill: true,
                    backgroundColor: chartGradient,
                    tension: 0.4
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 100, grid: { color: '#f0f0f0' } },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
</body>
</html>