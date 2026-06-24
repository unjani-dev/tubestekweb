<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TLD Pricing Matrix - AdminCore</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f2f5; color: #1e293b; }
        .bento-card { background: white; border-radius: 28px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.02); transition: 0.3s; }
        .bento-card:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(99, 102, 241, 0.05); }
        
        .table thead th { background: #f8fafc; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 1.5px; font-weight: 800; color: #94a3b8; padding: 20px; border: none; }
        .table tbody td { padding: 20px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        
        .status-pill { padding: 5px 12px; border-radius: 50px; font-weight: 700; font-size: 0.65rem; text-transform: uppercase; }
        .st-active { background: #05cd99; color: white; }
        .st-inactive { background: #94a3b8; color: white; }

        .form-control { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 16px; font-weight: 500; }
        .form-control:focus { box-shadow: none; border-color: #6366f1; background-color: #fff; }
        .form-label { font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }

        @media (max-width: 768px) { .content-area { padding: 20px !important; } }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0">
        <?= view('admin/sidebar') ?>

        <div class="col-md-10 p-4 p-md-5 content-area">
            
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
                <div>
                    <h2 class="fw-800 text-dark mb-1">TLD Pricing Matrix 🏷️</h2>
                    <p class="text-muted small fw-medium mb-0">Configure extension prices for domain registration</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm border-0" onclick="document.getElementById('addFormContainer').classList.toggle('d-none')" style="background: #6366f1;">
                        <i class="bi bi-plus-lg me-2"></i> Add New TLD
                    </button>
                </div>
            </div>

            <?php if(session()->getFlashdata('success')): ?>
                <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success fw-bold rounded-4 mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i> <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>
            <?php if(session()->getFlashdata('error')): ?>
                <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger fw-bold rounded-4 mb-4">
                    <i class="bi bi-exclamation-circle-fill me-2"></i> <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>
            <?php if(session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger fw-bold rounded-4 mb-4">
                    <?php foreach(session()->getFlashdata('errors') as $error): ?>
                        <div><i class="bi bi-exclamation-circle-fill me-2"></i><?= esc($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div id="addFormContainer" class="card bento-card border-start border-4 border-primary mb-5 d-none">
                <div class="card-body p-4 p-md-5">
                    <h5 class="fw-800 mb-4">Add Extension Pricing</h5>
                    <form action="<?= base_url('admin/domains/pricing/store') ?>" method="POST">
                        <?= csrf_field() ?>
                        <div class="row g-4">
                            <div class="col-md-3">
                                <label class="form-label">Extension (TLD)</label>
                                <input type="text" name="tld" class="form-control" placeholder="e.g. .com" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Register Price (USD)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light text-muted fw-bold">$</span>
                                    <input type="number" name="register_price" class="form-control border-light" placeholder="9.99" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Renew Price (USD)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light text-muted fw-bold">$</span>
                                    <input type="number" name="renew_price" class="form-control border-light" placeholder="10.99" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-dark w-100 rounded-pill fw-bold py-2" style="background: #1e293b;">
                                    Save Configuration
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card bento-card overflow-hidden">
                <div class="card-header bg-white border-0 py-4 px-4">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="fw-800 mb-0">Active Pricing Plans</h5>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">TLD</th>
                                    <th>Register Price</th>
                                    <th>Renew Price</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($tlds)): ?>
                                    <tr><td colspan="5" class="text-center py-5 text-muted fw-bold">No TLD pricing configured.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($tlds as $tld): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-800 text-dark fs-5"><?= esc($tld['tld']) ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark font-monospace">$<?= number_format($tld['register_price'], 2, '.', ',') ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark font-monospace">$<?= number_format($tld['renew_price'], 2, '.', ',') ?></div>
                                        </td>
                                        <td>
                                            <span class="status-pill st-<?= $tld['status'] ?>">
                                                <?= $tld['status'] ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button type="button" data-bs-toggle="modal" data-bs-target="#editTldModal<?= $tld['id'] ?>" class="btn btn-sm btn-light text-primary fw-bold rounded-pill px-3 shadow-sm">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button onclick="deleteTld(<?= $tld['id'] ?>)" class="btn btn-sm btn-light text-danger fw-bold rounded-pill px-3 shadow-sm">
                                                <i class="bi bi-trash3-fill"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if(!empty($tlds)): ?>
                <?php foreach ($tlds as $tld): ?>
                    <div class="modal fade" id="editTldModal<?= $tld['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 rounded-4 shadow">
                                <form action="<?= base_url('admin/domains/pricing/update/' . $tld['id']) ?>" method="POST">
                                    <?= csrf_field() ?>
                                    <div class="modal-header border-0 p-4 pb-0">
                                        <div>
                                            <h5 class="modal-title fw-800 text-dark">Edit <?= esc($tld['tld']) ?> Pricing</h5>
                                            <p class="text-muted small mb-0">Prices are global and will be used by the user domain checkout.</p>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="mb-3">
                                            <label class="form-label">Register Price (USD)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-light text-muted fw-bold">$</span>
                                                <input type="number" name="register_price" value="<?= esc($tld['register_price']) ?>" class="form-control border-light" step="0.01" min="0" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Renew Price (USD)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-light text-muted fw-bold">$</span>
                                                <input type="number" name="renew_price" value="<?= esc($tld['renew_price']) ?>" class="form-control border-light" step="0.01" min="0" required>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-control" required>
                                                <option value="active" <?= $tld['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                                <option value="inactive" <?= $tld['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 p-4 pt-0">
                                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Save Pricing</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function deleteTld(id) {
        if(confirm('Delete this TLD pricing? This action cannot be undone.')) {
            fetch(`<?= base_url('admin/domains/pricing/delete/') ?>${id}`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) location.reload();
                else alert(data.message);
            });
        }
    }
</script>

</body>
</html>
