<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User - Flizzy Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #f0f2f5; 
            color: #1e293b; 
        }
        .bento-card { 
            background: white; 
            border-radius: 28px; 
            border: none; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.02); 
        }
        .form-control, .form-select { 
            border-radius: 14px; 
            padding: 12px 20px; 
            border: 2px solid #f1f5f9; 
            font-weight: 500; 
            transition: 0.3s; 
            background-color: #fcfdfe;
        }
        .form-control:focus, .form-select:focus { 
            border-color: #6366f1; 
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); 
            background-color: #fff;
        }
        .input-label {
            font-size: 0.65rem;
            text-transform: uppercase;
            font-weight: 800;
            color: #94a3b8;
            letter-spacing: 1.5px;
            margin-bottom: 8px;
            display: block;
        }
        .btn-gradient {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            border: none;
            color: white;
            transition: 0.3s;
        }
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
            color: white;
        }
        @media (max-width: 768px) { 
            .content-area { padding: 20px !important; } 
        }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0">
        <?= view('admin/sidebar') ?>

        <div class="col-md-10 p-4 p-md-5 content-area">
            <div class="d-flex align-items-center mb-5">
                <a href="<?= base_url('admin/users') ?>" class="btn btn-white shadow-sm rounded-circle me-3 border d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-arrow-left fw-bold text-dark"></i>
                </a>
                <div>
                    <h2 class="fw-800 text-dark mb-0">Create User 👤</h2>
                    <p class="text-muted small fw-medium mb-0">Onboard a new customer to the platform</p>
                </div>
            </div>

            <?php if (session()->has('errors')): ?>
                <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4 p-4">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
                        <h6 class="fw-bold mb-0">Registration Failed</h6>
                    </div>
                    <ul class="mb-0 small fw-medium ps-3">
                        <?php foreach (session('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card bento-card">
                <div class="card-body p-4 p-md-5">
                    <form action="<?= base_url('admin/users/store') ?>" method="post">
                        <?= csrf_field() ?>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="input-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control" placeholder="e.g. Febriansah Dirgantara" value="<?= old('full_name') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="input-label">Username</label>
                                <input type="text" name="username" class="form-control" placeholder="e.g. vj007" value="<?= old('username') ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="input-label">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="name@example.com" value="<?= old('email') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="input-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control" placeholder="+62..." value="<?= old('phone') ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="input-label">Account Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" class="form-control" placeholder="Minimum 8 characters" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="input-label">Account Role</label>
                                <select name="role" class="form-select">
                                    <option value="user" <?= old('role') == 'user' ? 'selected' : '' ?>>Standard User</option>
                                    <option value="admin" <?= old('role') == 'admin' ? 'selected' : '' ?>>Administrator</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="input-label">Initial Balance ($)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0 fw-bold">$</span>
                                    <input type="number" name="balance" class="form-control border-start-0" value="<?= old('balance', '0.00') ?>" step="0.01">
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 pt-4 border-top d-flex justify-content-end gap-3">
                            <a href="<?= base_url('admin/users') ?>" class="btn btn-light rounded-pill px-4 fw-bold">Cancel</a>
                            <button type="submit" class="btn btn-gradient rounded-pill px-5 fw-bold shadow-lg py-3">
                                <i class="bi bi-person-check-fill me-2"></i> Deploy User Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-4 p-3 bg-white bg-opacity-50 rounded-4 border border-white small">
                <i class="bi bi-shield-lock-fill text-primary me-2"></i>
                <span class="text-muted fw-medium">Password will be automatically hashed using BCRYPT algorithm upon submission.</span>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>