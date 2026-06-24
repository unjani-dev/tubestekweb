<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain Manifest - AdminCore</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f2f5; color: #1e293b; }
        .bento-card { background: white; border-radius: 24px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.03); }
        .table thead th { background: #f8fafc; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 1.5px; font-weight: 800; color: #94a3b8; padding: 18px; border: none; }
        .table tbody td { padding: 18px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .user-mini-avatar { width: 35px; height: 35px; border-radius: 10px; background: #6366f1; color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.8rem; }
        .status-pill { padding: 5px 12px; border-radius: 50px; font-weight: 700; font-size: 0.65rem; text-transform: uppercase; }
        .st-active { background: #05cd99; color: white; }
        .st-pending { background: #faad14; color: white; }
        .st-suspended { background: #ff4d4f; color: white; }
        .st-expired { background: #94a3b8; color: white; }
        .ns-badge { font-family: monospace; font-size: 0.7rem; background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 6px; }
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
                    <h2 class="fw-800 text-dark mb-1">Domain Manifest</h2>
                    <p class="text-muted small fw-medium mb-0">Manage client domains, registry status, nameservers, and lifecycle flags.</p>
                </div>
            </div>

            <?php if(session()->getFlashdata('success')): ?>
                <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success fw-bold rounded-4 mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i><?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>
            <?php if(session()->getFlashdata('error')): ?>
                <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger fw-bold rounded-4 mb-4">
                    <i class="bi bi-exclamation-circle-fill me-2"></i><?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>
            <?php if(session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger fw-bold rounded-4 mb-4">
                    <?php foreach(session()->getFlashdata('errors') as $error): ?>
                        <div><i class="bi bi-exclamation-circle-fill me-2"></i><?= esc($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="card bento-card overflow-hidden">
                <div class="card-header bg-white border-0 py-4 px-4">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="fw-800 mb-0">Registered Domains</h5>
                        </div>
                        <div class="col-auto">
                            <div class="input-group input-group-sm" style="width: 260px;">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                                <input type="text" id="domainSearch" class="form-control bg-light border-0" placeholder="Search domain or owner...">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="domainTable">
                            <thead>
                                <tr>
                                    <th class="ps-4">Domain Name</th>
                                    <th>Owner</th>
                                    <th>Nameservers</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($domains)): ?>
                                    <tr><td colspan="6" class="text-center py-5 text-muted fw-bold">No domains registered yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($domains as $domain): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark fs-6"><?= esc($domain['domain_name']) ?></div>
                                            <div class="small mt-1">
                                                <span class="text-muted">Auto-renew:</span>
                                                <?= $domain['auto_renew'] ? '<span class="text-success fw-bold">ON</span>' : '<span class="text-secondary fw-bold">OFF</span>' ?>
                                                <span class="text-muted ms-2">Lock:</span>
                                                <?= !empty($domain['domain_lock']) ? '<span class="text-success fw-bold">ON</span>' : '<span class="text-secondary fw-bold">OFF</span>' ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-mini-avatar me-2"><?= strtoupper(substr($domain['username'], 0, 1)) ?></div>
                                                <div>
                                                    <div class="fw-bold text-dark"><?= esc($domain['username']) ?></div>
                                                    <div class="text-muted small"><?= esc($domain['email']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                <span class="ns-badge"><?= esc($domain['ns1']) ?></span>
                                                <span class="ns-badge"><?= esc($domain['ns2']) ?></span>
                                            </div>
                                        </td>
                                        <td><div class="fw-medium text-dark"><?= date('d M Y', strtotime($domain['expiry_date'])) ?></div></td>
                                        <td><span class="status-pill st-<?= esc($domain['status']) ?>"><?= esc($domain['status']) ?></span></td>
                                        <td class="text-end pe-4">
                                            <button type="button" class="btn btn-sm btn-light text-primary fw-bold rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#editDomainModal<?= $domain['id'] ?>">Edit</button>
                                            <?php if($domain['status'] === 'active'): ?>
                                                <button onclick="updateStatus(<?= $domain['id'] ?>, 'suspend')" class="btn btn-sm btn-light text-danger fw-bold rounded-pill px-3 shadow-sm">Suspend</button>
                                            <?php else: ?>
                                                <button onclick="updateStatus(<?= $domain['id'] ?>, 'activate')" class="btn btn-sm btn-light text-success fw-bold rounded-pill px-3 shadow-sm">Activate</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if(!empty($domains)): ?>
                <?php foreach ($domains as $domain): ?>
                    <div class="modal fade" id="editDomainModal<?= $domain['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content border-0 rounded-4 shadow">
                                <form action="<?= base_url('admin/domains/update/' . $domain['id']) ?>" method="POST">
                                    <?= csrf_field() ?>
                                    <div class="modal-header border-0 p-4 pb-0">
                                        <div>
                                            <h5 class="modal-title fw-800 text-dark">Edit <?= esc($domain['domain_name']) ?></h5>
                                            <p class="text-muted small mb-0">Changes here are reflected on the user's domain management screen.</p>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-muted text-uppercase">Status</label>
                                                <select name="status" class="form-select rounded-3" required>
                                                    <?php foreach(['pending', 'active', 'expired', 'suspended'] as $status): ?>
                                                        <option value="<?= $status ?>" <?= $domain['status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-muted text-uppercase">Expiry Date</label>
                                                <input type="date" name="expiry_date" value="<?= date('Y-m-d', strtotime($domain['expiry_date'])) ?>" class="form-control rounded-3" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-muted text-uppercase">Nameserver 1</label>
                                                <input type="text" name="ns1" value="<?= esc($domain['ns1']) ?>" class="form-control rounded-3 font-monospace" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-muted text-uppercase">Nameserver 2</label>
                                                <input type="text" name="ns2" value="<?= esc($domain['ns2']) ?>" class="form-control rounded-3 font-monospace" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-muted text-uppercase">Auto Renew</label>
                                                <select name="auto_renew" class="form-select rounded-3">
                                                    <option value="1" <?= !empty($domain['auto_renew']) ? 'selected' : '' ?>>On</option>
                                                    <option value="0" <?= empty($domain['auto_renew']) ? 'selected' : '' ?>>Off</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-muted text-uppercase">Domain Lock</label>
                                                <select name="domain_lock" class="form-select rounded-3">
                                                    <option value="1" <?= !empty($domain['domain_lock']) ? 'selected' : '' ?>>Locked</option>
                                                    <option value="0" <?= empty($domain['domain_lock']) ? 'selected' : '' ?>>Unlocked</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 p-4 pt-0">
                                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Save Changes</button>
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
    document.getElementById('domainSearch').addEventListener('keyup', function() {
        let value = this.value.toLowerCase();
        let rows = document.querySelectorAll('#domainTable tbody tr');
        rows.forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
        });
    });

    function updateStatus(id, action) {
        if(confirm(`Are you sure you want to ${action} this domain?`)) {
            fetch(`<?= base_url('admin/domains/') ?>${action}/${id}`, {
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
