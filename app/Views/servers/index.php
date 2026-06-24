<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Server - Cloud Platform Mini</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .plan-card {
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
            cursor: pointer;
        }
        .plan-card:hover {
            border-color: #667eea;
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }
        .plan-card.selected {
            border-color: #667eea;
            background: #f8f9ff;
        }
        .plan-price {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><i class="bi bi-plus-circle"></i> Create New Server</h2>
                        <p class="text-muted">Choose your server configuration</p>
                    </div>
                    <a href="<?= base_url('dashboard/servers') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('dashboard/servers/store') ?>" method="post" id="createServerForm">
                    <?= csrf_field() ?>
                    
                    <!-- Step 1: Choose Plan -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-1-circle"></i> Choose Server Plan</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($plans as $plan): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="plan-card p-4 rounded h-100" data-plan-id="<?= $plan['id'] ?>" onclick="selectPlan(<?= $plan['id'] ?>)">
                                        <h4 class="text-center mb-3"><?= esc($plan['plan_name']) ?></h4>
                                        <div class="plan-price text-center mb-3">
                                            $<?= number_format($plan['price_per_month'], 2) ?>
                                            <small class="text-muted d-block" style="font-size: 0.9rem;">
                                                $<?= number_format($plan['price_per_hour'], 4) ?>/hour
                                            </small>
                                        </div>
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <i class="bi bi-cpu text-primary"></i> 
                                                <strong><?= $plan['cpu_cores'] ?></strong> CPU Core<?= $plan['cpu_cores'] > 1 ? 's' : '' ?>
                                            </li>
                                            <li class="mb-2">
                                                <i class="bi bi-memory text-success"></i> 
                                                <strong><?= $plan['ram_gb'] ?> GB</strong> RAM
                                            </li>
                                            <li class="mb-2">
                                                <i class="bi bi-hdd text-warning"></i> 
                                                <strong><?= $plan['storage_gb'] ?> GB</strong> Storage
                                            </li>
                                            <li class="mb-2">
                                                <i class="bi bi-speedometer text-info"></i> 
                                                <strong><?= $plan['bandwidth_gb'] ?> GB</strong> Bandwidth
                                            </li>
                                        </ul>
                                        <p class="text-muted small mb-0"><?= esc($plan['description']) ?></p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="plan_id" id="plan_id" required>
                        </div>
                    </div>

                    <!-- Step 2: Server Details -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-2-circle"></i> Server Configuration</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Server Name *</label>
                                    <input type="text" name="server_name" class="form-control" required 
                                           placeholder="my-production-server" value="<?= old('server_name') ?>">
                                    <small class="text-muted">Use lowercase, numbers, and hyphens only</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Operating System *</label>
                                    <select name="os" class="form-select" required>
                                        <option value="">Select OS...</option>
                                        <?php foreach ($os_options as $os): ?>
                                        <option value="<?= esc($os) ?>" <?= old('os') == $os ? 'selected' : '' ?>>
                                            <?= esc($os) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> 
                                <strong>Note:</strong> Your server will be provisioned immediately and start charging hourly once created.
                                Make sure you have sufficient balance.
                            </div>
                        </div>
                    </div>

                    <!-- Summary & Submit -->
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-check-circle"></i> Review & Create</h5>
                        </div>
                        <div class="card-body">
                            <div id="planSummary" class="mb-3">
                                <p class="text-muted">Please select a plan first</p>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="agree" required>
                                <label class="form-check-label" for="agree">
                                    I understand that I will be charged hourly for this server and agree to the 
                                    <a href="#" target="_blank">Terms of Service</a>
                                </label>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-rocket-takeoff"></i> Create Server
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const plans = <?= json_encode($plans) ?>;
        
        function selectPlan(planId) {
            // Remove previous selection
            document.querySelectorAll('.plan-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selection
            document.querySelector(`[data-plan-id="${planId}"]`).classList.add('selected');
            
            // Set hidden input
            document.getElementById('plan_id').value = planId;
            
            // Update summary
            const plan = plans.find(p => p.id == planId);
            if (plan) {
                document.getElementById('planSummary').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Selected Plan:</strong> ${plan.plan_name}</p>
                            <p><strong>Resources:</strong> ${plan.cpu_cores} CPU, ${plan.ram_gb}GB RAM, ${plan.storage_gb}GB Storage</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Hourly Cost:</strong> $${parseFloat(plan.price_per_hour).toFixed(4)}</p>
                            <p><strong>Monthly Estimate:</strong> ~$${parseFloat(plan.price_per_month).toFixed(2)}</p>
                        </div>
                    </div>
                `;
            }
        }
    </script>
</body>
</html>