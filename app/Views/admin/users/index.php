<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Flizzy Cloud Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f2f5; color: #1e293b; }
        .bento-card { background: white; border-radius: 24px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
        
        /* Table Styling */
        .table thead th {
            background-color: #f8f9fc;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 1.5px;
            font-weight: 800;
            color: #94a3b8;
            padding: 20px;
            border: none;
        }
        .table tbody td { padding: 20px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        
        /* Custom UI */
        .user-avatar {
            width: 45px; height: 45px; border-radius: 14px;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            color: white; display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 1.2rem;
        }
        .badge-status { padding: 6px 12px; border-radius: 50px; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; }
        .bg-active-soft { background: #e6f9f4; color: #05cd99; }
        .bg-suspended-soft { background: #fff1f0; color: #ff4d4f; }
        
        .btn-action { width: 36px; height: 36px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; background: white; transition: 0.2s; }
        .btn-action:hover { background: #f8fafc; transform: translateY(-2px); }

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
                    <h2 class="fw-800 text-dark mb-1">User Management 👥</h2>
                    <p class="text-muted fw-medium mb-0">Review, manage, and adjust customer accounts</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-white border rounded-pill px-4 fw-bold shadow-sm"><i class="bi bi-search me-2"></i> Search</button>
                    <a href="<?= base_url('admin/users/create') ?>" class="btn btn-dark rounded-pill px-4 fw-bold shadow-sm border-0" style="background: #2b3674;">
                        <i class="bi bi-person-plus-fill me-2"></i> Add New User
                    </a>
                </div>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 fw-bold">
                    <i class="bi bi-check-circle-fill me-2"></i> <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>

            <div class="card bento-card overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">User Details</th>
                                    <th>Role & Access</th>
                                    <th>Wallet Balance</th>
                                    <th>Account Status</th>
                                    <th class="text-end pe-4">Quick Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                <tr id="row-<?= $u['id'] ?>">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-3">
                                                <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <h6 class="fw-bolder text-dark mb-0"><?= esc($u['full_name']) ?></h6>
                                                <small class="text-muted fw-medium">@<?= esc($u['username']) ?> • <?= esc($u['email']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill fw-bold border px-3 py-2 <?= $u['role'] === 'admin' ? 'bg-dark text-white' : 'bg-light text-dark' ?>">
                                            <i class="bi <?= $u['role'] === 'admin' ? 'bi-shield-lock-fill' : 'bi-person-fill' ?> me-1"></i>
                                            <?= strtoupper($u['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-800 text-dark">$<?= number_format($u['balance'], 2) ?></div>
                                        <button onclick="openBalanceModal(<?= $u['id'] ?>, '<?= esc($u['full_name']) ?>')" class="btn btn-link p-0 text-primary small fw-bold text-decoration-none">+ Adjust</button>
                                    </td>
                                    <td>
                                        <?php 
                                            $statusClass = $u['status'] === 'active' ? 'bg-active-soft' : 'bg-suspended-soft';
                                        ?>
                                        <span id="status-badge-<?= $u['id'] ?>" class="badge-status <?= $statusClass ?>">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> <?= $u['status'] ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="<?= base_url('admin/users/'.$u['id'].'/edit') ?>" class="btn-action text-primary" title="Edit Profile">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            
                                            <?php if($u['status'] === 'active'): ?>
                                                <button onclick="changeStatus(<?= $u['id'] ?>, 'suspend')" id="btn-status-<?= $u['id'] ?>" class="btn-action text-warning" title="Suspend Account">
                                                    <i class="bi bi-slash-circle"></i>
                                                </button>
                                            <?php else: ?>
                                                <button onclick="changeStatus(<?= $u['id'] ?>, 'activate')" id="btn-status-<?= $u['id'] ?>" class="btn-action text-success" title="Activate Account">
                                                    <i class="bi bi-play-circle"></i>
                                                </button>
                                            <?php endif; ?>

                                            <button onclick="confirmDelete(<?= $u['id'] ?>)" class="btn-action text-danger" title="Delete User">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="balanceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 28px;">
            <div class="modal-body p-5">
                <h4 class="fw-800 text-dark mb-1">Adjust Wallet 💸</h4>
                <p class="text-muted small fw-medium mb-4" id="modal-user-name"></p>
                
                <form id="balanceForm">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Action Type</label>
                        <select name="type" class="form-select border-2 rounded-4">
                            <option value="add">Add Balance (+)</option>
                            <option value="subtract">Subtract Balance (-)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Amount ($)</label>
                        <input type="number" name="amount" class="form-control border-2 rounded-4" step="0.01" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase">Reason</label>
                        <input type="text" name="reason" class="form-control border-2 rounded-4" placeholder="e.g. Compensation, Promo">
                    </div>
                    <input type="hidden" name="user_id" id="modal-user-id">
                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold rounded-pill shadow-lg border-0 py-3" style="background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);">Update Wallet</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="csrf-token-holder"><?= csrf_field() ?></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // AJAX Headers Helper
    const getHeaders = () => {
        const token = document.querySelector('input[name="<?= csrf_token() ?>"]').value;
        return { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token };
    };

    // 1. BALANCE MODAL LOGIC
    const balanceModal = new Bootstrap.Modal(document.getElementById('balanceModal'));
    function openBalanceModal(id, name) {
        document.getElementById('modal-user-name').innerText = "Modifying wallet for " + name;
        document.getElementById('modal-user-id').value = id;
        balanceModal.show();
    }

    document.getElementById('balanceForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const userId = formData.get('user_id');

        try {
            const response = await fetch(`<?= base_url('admin/users/') ?>${userId}/adjust-balance`, {
                method: 'POST',
                headers: getHeaders(),
                body: formData
            });
            const data = await response.json();
            if(data.success) {
                Swal.fire('Updated!', data.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (err) { Swal.fire('Error', 'Server connection failed', 'error'); }
    });

    // 2. STATUS TOGGLE (Suspend/Activate)
    async function changeStatus(id, action) {
        const confirmText = action === 'suspend' ? 'Account will be locked!' : 'Account will be restored!';
        const result = await Swal.fire({
            title: 'Are you sure?', text: confirmText, icon: 'warning',
            showCancelButton: true, confirmButtonColor: action === 'suspend' ? '#ff4d4f' : '#05cd99'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`<?= base_url('admin/users/') ?>${id}/${action}`, {
                    method: 'POST',
                    headers: getHeaders()
                });
                const data = await response.json();
                if(data.success) {
                    Swal.fire('Success', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Failed', data.message, 'error');
                }
            } catch (err) { Swal.fire('Error', 'Security token invalid. Refreshing...', 'error'); }
        }
    }

    // 3. DELETE USER
    function confirmDelete(id) {
        Swal.fire({
            title: 'Delete User?', text: 'All servers and data will be destroyed! This is PERMANENT.',
            icon: 'error', showCancelButton: true, confirmButtonColor: '#ff4d4f'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const response = await fetch(`<?= base_url('admin/users/') ?>${id}`, { method: 'DELETE', headers: getHeaders() });
                const data = await response.json();
                if(data.success) {
                    document.getElementById(`row-${id}`).remove();
                    Swal.fire('Deleted!', data.message, 'success');
                }
            }
        });
    }
</script>

</body>
</html>