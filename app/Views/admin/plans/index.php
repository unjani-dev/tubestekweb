<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan Management - Flizzy Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f2f5; color: #1e293b; }
        .bento-card { background: white; border-radius: 28px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
        .plan-icon { width: 45px; height: 45px; border-radius: 14px; background: #f1f5f9; color: #6366f1; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .badge-status { padding: 6px 12px; border-radius: 50px; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; }
        .bg-active-soft { background: #e6f9f4; color: #05cd99; }
        .bg-inactive-soft { background: #fff1f0; color: #ff4d4f; }
        .table thead th { background: #f8fafc; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 1.5px; font-weight: 800; color: #94a3b8; padding: 20px; border: none; }
        .table tbody td { padding: 20px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .btn-action { width: 38px; height: 38px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; background: white; transition: 0.2s; color: #64748b; }
        .btn-action:hover { background: #f8fafc; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
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
                    <h2 class="fw-800 text-dark mb-1">Cloud Plans 🏷️</h2>
                    <p class="text-muted small fw-medium mb-0">Manage your hosting tiers and hourly pricing</p>
                </div>
                <div>
                    <a href="<?= base_url('admin/plans/create') ?>" class="btn btn-primary rounded-pill px-4 fw-bold shadow-lg border-0 py-2" style="background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);">
                        <i class="bi bi-plus-lg me-2"></i> Create New Plan
                    </a>
                </div>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 fw-bold p-3">
                    <i class="bi bi-check-circle-fill me-2"></i> <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>

            <div class="card bento-card overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Package Name</th>
                                    <th>Specifications</th>
                                    <th>Hourly Rate</th>
                                    <th>Est. Monthly</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Manage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($plans)): ?>
                                    <tr><td colspan="6" class="text-center py-5 text-muted fw-bold">No active plans found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($plans as $p): ?>
                                    <tr id="row-<?= $p['id'] ?>">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="plan-icon me-3">
                                                    <i class="bi bi-box-seam-fill"></i>
                                                </div>
                                                <div>
                                                    <h6 class="fw-bolder text-dark mb-0"><?= esc($p['plan_name']) ?></h6>
                                                    <small class="text-muted fw-medium"><?= esc($p['description']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <span class="badge bg-light text-primary border-0 rounded-pill px-2"><?= $p['cpu_cores'] ?> vCPU</span>
                                                <span class="badge bg-light text-primary border-0 rounded-pill px-2"><?= $p['ram_gb'] ?>G RAM</span>
                                            </div>
                                        </td>
                                        <td><span class="fw-800 text-dark">$<?= number_format($p['price_per_hour'], 4) ?></span></td>
                                        <td><span class="fw-bold text-muted">$<?= number_format($p['price_per_month'], 2) ?></span></td>
                                        <td>
                                            <span class="badge-status <?= $p['status'] === 'active' ? 'bg-active-soft' : 'bg-inactive-soft' ?>">
                                                <?= strtoupper($p['status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="<?= base_url('admin/plans/'.$p['id'].'/edit') ?>" class="btn-action text-primary" title="Edit Plan">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>
                                                <button onclick="confirmDelete(<?= $p['id'] ?>)" class="btn-action text-danger" title="Delete Plan">
                                                    <i class="bi bi-trash3-fill"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="csrf-holder">
    <?= csrf_field() ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    /**
     * confirmDelete - AJAX Delete dengan standar keamanan X-CSRF-TOKEN
     */
    async function confirmDelete(id) {
        const result = await Swal.fire({
            title: 'Delete this plan?',
            text: "Warning: If this plan is currently used by active servers, deletion will fail.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff4d4f',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Delete Plan'
        });

        if (result.isConfirmed) {
            // State: Loading
            Swal.fire({ title: 'Deleting...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

            // AMBIL TOKEN CSRF TERBARU DARI ELEMEN HIDDEN
            const csrfInput = document.querySelector('#csrf-holder input');
            const csrfHash = csrfInput.value;

            try {
                // SINKRONISASI: Menggunakan method DELETE sesuai rute admin/plans/(:num)
                const response = await fetch(`<?= base_url('admin/plans/') ?>${id}`, {
                    method: 'DELETE',
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfHash // INI KUNCI AGAR TIDAK "NOT ALLOWED"
                    }
                });

                if (response.status === 403) {
                    throw new Error("Security token mismatch (403). Please refresh the page.");
                }

                const data = await response.json();

                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Removed!', text: data.message, timer: 1500, showConfirmButton: false });
                    document.getElementById(`row-${id}`).remove();
                } else {
                    Swal.fire('Action Denied', data.message, 'error');
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Connection Error', err.message, 'error');
            }
        }
    }
</script>

</body>
</html>