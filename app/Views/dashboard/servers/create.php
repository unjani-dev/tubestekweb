<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deploy New Instance - Flizzy Cloud Mini</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f4f7fe;
            color: #2b3674;
        }
        .bento-card {
            background: white;
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        }
        
        /* Interactive Selection Cards */
        .selection-card {
            cursor: pointer;
            border: 2px solid #f1f4f9;
            border-radius: 15px;
            transition: all 0.2s ease-in-out;
            background: white;
            position: relative;
            overflow: hidden;
        }
        .selection-card:hover {
            border-color: #cbd5e1;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }
        .radio-hidden {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        .radio-hidden:checked + .selection-card {
            border-color: #667eea;
            background-color: #f0f5ff;
            box-shadow: 0 0 0 1px #667eea, 0 10px 20px rgba(102, 126, 234, 0.15);
        }
        .radio-hidden:checked + .selection-card .check-icon {
            display: block !important;
            color: #667eea;
            animation: bounceIn 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        @keyframes bounceIn {
            0% { transform: scale(0); }
            100% { transform: scale(1); }
        }

        .os-logo { font-size: 2.5rem; transition: transform 0.3s; }
        .selection-card:hover .os-logo { transform: scale(1.1); }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #c5cddf; border-radius: 10px; }

        @media (max-width: 767.98px) {
            .mobile-sidebar { width: 280px; }
            .content-area { margin-top: 60px; }
        }
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
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0" style="background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);">
             <?= view('dashboard/sidebar') ?>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            
            <?= view('dashboard/sidebar') ?>

            <div class="col-md-10 p-4 p-md-5 content-area">
                
                <div class="d-flex align-items-center mb-5 border-bottom pb-3">
                    <a href="<?= base_url('dashboard/servers') ?>" class="btn btn-white shadow-sm rounded-circle me-3 d-flex align-items-center justify-content-center border" style="width: 45px; height: 45px;">
                        <i class="bi bi-arrow-left fw-bold"></i>
                    </a>
                    <div>
                        <p class="text-muted mb-0 fw-bold small text-uppercase" style="letter-spacing: 1px;">Deployment</p>
                        <h2 class="fw-bolder text-dark mb-0">Deploy New Instance</h2>
                    </div>
                </div>

                <?php if (session()->has('errors')): ?>
                    <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4">
                        <h6 class="fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Deployment Failed</h6>
                        <ul class="mb-0 small fw-medium">
                            <?php foreach (session('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('dashboard/servers/store') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row g-4">
                        <div class="col-lg-8">
                            
                            <div class="card bento-card mb-4">
                                <div class="card-body p-4 p-md-5">
                                    <h5 class="fw-bolder mb-4 text-dark d-flex align-items-center">
                                        <span class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px; font-size: 1rem;">1</span> 
                                        Choose an OS Image
                                    </h5>
                                    
                                    <div class="row g-3">
                                        <?php 
                                        $os_options = [
                                            ['name' => 'Ubuntu 22.04 LTS', 'icon' => 'bi-ubuntu', 'color' => '#E95420', 'sub' => 'LTS'],
                                            ['name' => 'Debian 12', 'icon' => 'bi-record-circle', 'color' => '#A80030', 'sub' => 'Bookworm'],
                                            ['name' => 'AlmaLinux 9', 'icon' => 'bi-hdd-rack-fill', 'color' => '#3a5894', 'sub' => 'Enterprise'],
                                            ['name' => 'Windows Server', 'icon' => 'bi-windows', 'color' => '#00a4ef', 'sub' => '2022 Data']
                                        ];
                                        foreach($os_options as $index => $os): 
                                        ?>
                                        <div class="col-md-3 col-6">
                                            <label class="w-100 h-100">
                                                <input type="radio" name="os" value="<?= $os['name'] ?>" class="radio-hidden" required <?= $index === 0 ? 'checked' : '' ?>>
                                                <div class="card selection-card h-100 text-center p-3">
                                                    <i class="bi bi-check-circle-fill check-icon position-absolute top-0 end-0 m-2 fs-5" style="display:none;"></i>
                                                    <i class="bi <?= $os['icon'] ?> os-logo mb-2" style="color: <?= $os['color'] ?>;"></i>
                                                    <h6 class="mb-0 fw-bold text-dark"><?= explode(' ', $os['name'])[0] ?></h6>
                                                    <small class="text-muted fw-bold" style="font-size: 0.65rem;"><?= $os['sub'] ?></small>
                                                </div>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="card bento-card mb-4">
                                <div class="card-body p-4 p-md-5">
                                    <h5 class="fw-bolder mb-4 text-dark d-flex align-items-center">
                                        <span class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px; font-size: 1rem;">2</span> 
                                        Choose Instance Size
                                    </h5>
                                    
                                    <div class="row g-3">
                                        <?php if(isset($plans) && !empty($plans)): ?>
                                            <?php foreach($plans as $index => $plan): ?>
                                            <div class="col-12">
                                                <label class="w-100">
                                                    <input type="radio" name="plan_id" value="<?= $plan['id'] ?>" class="radio-hidden" data-price="<?= $plan['price_per_month'] ?>" onchange="updateSummary()" <?= $index === 0 ? 'checked' : '' ?> required>
                                                    <div class="card selection-card p-3 p-md-4">
                                                        <i class="bi bi-check-circle-fill check-icon position-absolute top-50 end-0 translate-middle-y me-4 fs-4" style="display:none;"></i>
                                                        <div class="row align-items-center">
                                                            <div class="col-md-4 border-end-md">
                                                                <h5 class="text-dark fw-bolder mb-1"><?= esc($plan['plan_name']) ?></h5>
                                                                <div class="text-primary fw-bolder fs-4">$<?= number_format($plan['price_per_month'], 2) ?><span class="text-muted fw-medium fs-6">/mo</span></div>
                                                            </div>
                                                            <div class="col-md-8 ps-md-4 mt-3 mt-md-0">
                                                                <div class="d-flex gap-4 flex-wrap">
                                                                    <div class="text-center">
                                                                        <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.6rem;">vCPU</div>
                                                                        <div class="fw-bold text-dark"><?= $plan['cpu_cores'] ?> Core</div>
                                                                    </div>
                                                                    <div class="text-center">
                                                                        <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.6rem;">Memory</div>
                                                                        <div class="fw-bold text-dark"><?= $plan['ram_gb'] ?> GB</div>
                                                                    </div>
                                                                    <div class="text-center">
                                                                        <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.6rem;">Storage</div>
                                                                        <div class="fw-bold text-dark"><?= $plan['storage_gb'] ?> GB SSD</div>
                                                                    </div>
                                                                    <div class="text-center">
                                                                        <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.6rem;">Transfer</div>
                                                                        <div class="fw-bold text-dark"><?= $plan['bandwidth_gb'] / 1000 ?> TB</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="p-5 text-center bg-light rounded-4">
                                                <i class="bi bi-slash-circle fs-1 text-muted"></i>
                                                <p class="mt-2 fw-bold text-muted">No plans available in database.</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="card bento-card mb-4">
                                <div class="card-body p-4 p-md-5">
                                    <h5 class="fw-bolder mb-4 text-dark d-flex align-items-center">
                                        <span class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px; font-size: 1rem;">3</span> 
                                        Give it a name
                                    </h5>
                                    <div class="mb-2">
                                        <label class="form-label text-muted fw-bold small text-uppercase">Hostname</label>
                                        <input type="text" name="server_name" class="form-control form-control-lg border-2 rounded-4 fw-bold" placeholder="e.g. project-apollo-01" value="<?= old('server_name') ?>" required pattern="[a-zA-Z0-9-]+" title="Letters, numbers, and hyphens only">
                                    </div>
                                    <small class="text-muted fw-medium"><i class="bi bi-info-circle me-1"></i> Tip: Use a descriptive name to stay organized.</small>
                                </div>
                            </div>

                        </div>

                        <div class="col-lg-4">
                            <div class="card bento-card sticky-top border-top border-5 border-primary" style="top: 100px;">
                                <div class="card-body p-4 p-md-5">
                                    <h5 class="fw-bolder text-dark mb-4 border-bottom pb-3">Review Order</h5>
                                    
                                    <div class="d-flex justify-content-between mb-3 fw-medium">
                                        <span class="text-muted">Type</span>
                                        <span class="text-dark">Standard VPS</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3 fw-medium">
                                        <span class="text-muted">Location</span>
                                        <span class="text-dark"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Singapore (SG-1)</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-4 fw-medium border-bottom pb-3">
                                        <span class="text-muted">Your Wallet</span>
                                        <span class="fw-bold text-success">$<?= number_format(session()->get('balance') ?? 0, 2) ?></span>
                                    </div>

                                    <div class="bg-light rounded-4 p-4 text-center mb-4">
                                        <p class="text-muted fw-bold small text-uppercase mb-1" style="letter-spacing: 1px;">Price per Month</p>
                                        <h1 class="text-primary fw-bolder mb-0 display-4" id="summaryPrice">$0.00</h1>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold rounded-pill py-3 shadow-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                        Deploy Instance
                                    </button>
                                    
                                    <div class="text-center mt-4">
                                        <small class="text-muted fw-medium">Ready in <span class="text-dark fw-bold">~55 seconds</span> ⚡</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
        // Update price in real-time when user selects a plan
        function updateSummary() {
            const selectedPlan = document.querySelector('input[name="plan_id"]:checked');
            if (selectedPlan) {
                const price = parseFloat(selectedPlan.getAttribute('data-price')).toFixed(2);
                document.getElementById('summaryPrice').innerText = '$' + price;
            }
        }
        
        document.addEventListener('DOMContentLoaded', updateSummary);
    </script>
</body>
</html>