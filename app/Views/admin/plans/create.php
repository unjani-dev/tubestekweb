<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Plan - Flizzy Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f2f5; color: #1e293b; }
        .bento-card { background: white; border-radius: 28px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
        .form-control, .form-select { 
            border-radius: 14px; padding: 12px 20px; border: 2px solid #f1f5f9; 
            font-weight: 500; transition: 0.3s; background-color: #fcfdfe;
        }
        .form-control:focus, .form-select:focus { 
            border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); background-color: #fff;
        }
        .input-label { font-size: 0.65rem; text-transform: uppercase; font-weight: 800; color: #94a3b8; letter-spacing: 1.5px; margin-bottom: 8px; display: block; }
        .btn-gradient { background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); border: none; color: white; transition: 0.3s; }
        .btn-gradient:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3); color: white; }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0">
        <?= view('admin/sidebar') ?>

        <div class="col-md-10 p-4 p-md-5 content-area">
            <div class="d-flex align-items-center mb-5">
                <a href="<?= base_url('admin/plans') ?>" class="btn btn-white shadow-sm rounded-circle me-3 border d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-arrow-left fw-bold text-dark"></i>
                </a>
                <div>
                    <h2 class="fw-800 text-dark mb-0">New Cloud Plan 🏷️</h2>
                    <p class="text-muted small fw-medium mb-0">Define a new resource tier for your customers</p>
                </div>
            </div>

            <?php if (session()->has('errors')): ?>
                <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4 p-4">
                    <ul class="mb-0 small fw-bold">
                        <?php foreach (session('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card bento-card">
                <div class="card-body p-4 p-md-5">
                    <form action="<?= base_url('admin/plans/store') ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="input-label">Plan Display Name</label>
                                <input type="text" name="plan_name" class="form-control" placeholder="e.g. Starter Node, Enterprise Pro" value="<?= old('plan_name') ?>" required>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="input-label">vCPU Cores</label>
                                <input type="number" name="cpu_cores" class="form-control" value="<?= old('cpu_cores', 1) ?>" min="1" required>
                            </div>
                            <div class="col-md-3">
                                <label class="input-label">RAM (GB)</label>
                                <input type="number" name="ram_gb" class="form-control" value="<?= old('ram_gb', 1) ?>" min="1" required>
                            </div>
                            <div class="col-md-3">
                                <label class="input-label">Storage (GB NVMe)</label>
                                <input type="number" name="storage_gb" class="form-control" value="<?= old('storage_gb', 20) ?>" min="1" required>
                            </div>
                            <div class="col-md-3">
                                <label class="input-label">Bandwidth (GB)</label>
                                <input type="number" name="bandwidth_gb" class="form-control" value="<?= old('bandwidth_gb', 1000) ?>" min="1" required>
                            </div>

                            <div class="col-md-6">
                                <label class="input-label">Price Per Hour ($)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-2 border-end-0 fw-bold" style="border-radius: 14px 0 0 14px;">$</span>
                                    <input type="number" id="hourly_input" name="price_per_hour" class="form-control border-start-0" step="0.0001" value="<?= old('price_per_hour', '0.010') ?>" required oninput="calcMonthly()">
                                </div>
                                <div class="mt-2 p-2 bg-light rounded-3">
                                    <small class="text-muted fw-bold">EST. MONTHLY COST: <span id="monthly_preview" class="text-primary">$7.20</span></small>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="input-label">Plan Description</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Briefly describe what this plan is best for..."><?= old('description') ?></textarea>
                            </div>
                        </div>

                        <div class="mt-5 pt-4 border-top d-flex justify-content-end gap-3">
                            <a href="<?= base_url('admin/plans') ?>" class="btn btn-light rounded-pill px-4 fw-bold">Discard</a>
                            <button type="submit" class="btn btn-gradient rounded-pill px-5 fw-bold shadow-lg py-3">Publish Plan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function calcMonthly() {
        const hourly = document.getElementById('hourly_input').value;
        const monthly = (hourly * 720).toFixed(2);
        document.getElementById('monthly_preview').innerText = '$' + monthly;
    }
    // Initialize on load
    calcMonthly();
</script>
</body>
</html>