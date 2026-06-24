<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Flizzy Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f2f5; color: #1e293b; }
        .bento-card { background: white; border-radius: 28px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
        .form-control, .form-select { border-radius: 14px; padding: 12px 20px; border: 2px solid #f1f5f9; font-weight: 500; transition: 0.3s; }
        .form-control:focus { border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); }
        .status-pill { padding: 5px 15px; border-radius: 50px; font-weight: 800; font-size: 0.65rem; text-transform: uppercase; }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0">
        <?= view('admin/sidebar') ?>

        <div class="col-md-10 p-4 p-md-5 content-area">
            <div class="d-flex align-items-center mb-5">
                <a href="<?= base_url('admin/users') ?>" class="btn btn-white shadow-sm rounded-circle me-3 border d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                    <i class="bi bi-arrow-left fw-bold"></i>
                </a>
                <div>
                    <h2 class="fw-800 text-dark mb-0">Modify Account 🛠️</h2>
                    <p class="text-muted small fw-medium mb-0">Updating profile for <strong><?= esc($user['username']) ?></strong></p>
                </div>
            </div>

            <div class="card bento-card">
                <div class="card-body p-4 p-md-5">
                    <form action="<?= base_url('admin/users/'.$user['id'].'/update') ?>" method="post">
                        <?= csrf_field() ?>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase opacity-50">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?= old('full_name', esc($user['full_name'])) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase opacity-50">Email Address (Read-Only)</label>
                                <input type="email" class="form-control bg-light" value="<?= esc($user['email']) ?>" readonly disabled>
                                <small class="text-muted">Email changes require system re-verification</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase opacity-50">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="<?= old('phone', esc($user['phone'])) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small text-uppercase opacity-50">Account Role</label>
                                <select name="role" class="form-select">
                                    <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>Standard User</option>
                                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Administrator</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small text-uppercase opacity-50">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" <?= $user['status'] == 'active' ? 'selected' : '' ?>>🟢 Active</option>
                                    <option value="suspended" <?= $user['status'] == 'suspended' ? 'selected' : '' ?>>🔴 Suspended</option>
                                </select>
                            </div>
                            
                            <div class="col-12 mt-4 pt-4 border-top">
                                <h6 class="fw-bold text-danger mb-3"><i class="bi bi-shield-lock me-2"></i>Security Override</h6>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-uppercase opacity-50">New Password</label>
                                    <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 d-flex justify-content-end gap-2">
                            <a href="<?= base_url('admin/users') ?>" class="btn btn-light rounded-pill px-4 fw-bold">Discard</a>
                            <button type="submit" class="btn btn-dark rounded-pill px-5 fw-bold shadow-lg py-3">Commit Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>