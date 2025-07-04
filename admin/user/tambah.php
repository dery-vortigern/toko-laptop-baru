<?php
session_start();
require_once '../../config/koneksi.php';

// Cek login - FIXED: Allow both admin and superadmin roles
if (!isset($_SESSION['login']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header("Location: ../../auth/adminlogin.php");
    exit;
}

// Proses tambah admin
if (isset($_POST['tambah'])) {
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $nama = htmlspecialchars($_POST['nama']);
    $email = htmlspecialchars($_POST['email']);
    $role = $_POST['role'];
    $status = isset($_POST['status']) ? 1 : 0;
    
    // Validasi
    $error = false;
    
    // Cek username kosong
    if (empty($username)) {
        $error = true;
        $error_msg = "Username harus diisi!";
    }
    
    // Cek apakah username sudah ada
    $cek_username = query("SELECT * FROM tb_admin WHERE username = '$username'");
    if ($cek_username) {
        $error = true;
        $error_msg = "Username sudah digunakan!";
    }
    
    // Cek email valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = true;
        $error_msg = "Format email tidak valid!";
    }
    
    // Cek apakah email sudah ada
    $cek_email = query("SELECT * FROM tb_admin WHERE email = '$email'");
    if ($cek_email) {
        $error = true;
        $error_msg = "Email sudah digunakan!";
    }
    
    // Validasi password
    if (strlen($password) < 6) {
        $error = true;
        $error_msg = "Password minimal 6 karakter!";
    }
    
    // Cek konfirmasi password
    if ($password !== $password_confirm) {
        $error = true;
        $error_msg = "Konfirmasi password tidak sesuai!";
    }

    if (!$error) {

        
        $data = [
            'username' => $username,
            'password' => $password,
            'nama' => $nama,
            'email' => $email,
            'role' => $role,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        if (tambah('tb_admin', $data)) {
            $_SESSION['success'] = "Admin berhasil ditambahkan!";
            header("Location: index.php");
            exit;
        } else {
            $error_msg = "Gagal menambahkan admin!";
        }
    }
}

include_once '../includes/header.php';
?>


<style>
    :root {
        --primary-color: #2563eb;
        --primary-hover: #1d4ed8;
        --primary-light: #dbeafe;
        --secondary-color: #475569;
        --success-color: #10b981;
        --danger-color: #ef4444;
        --warning-color: #f59e0b;
        --border-radius: 0.75rem;
        --box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
        --transition: all 0.3s ease;
    }
    
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        background-color: #f8fafc;
        color: #334155;
        min-height: 100vh;
        line-height: 1.5;
    }
    
    .container-fluid {
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    h1 {
        color: #1e293b;
        font-weight: 700;
        font-size: 1.875rem;
        margin-bottom: 0.5rem;
    }
    
    .breadcrumb {
        margin-bottom: 2rem;
    }
    
    .breadcrumb-item a {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 500;
        transition: var(--transition);
    }
    
    .breadcrumb-item a:hover {
        color: var(--primary-hover);
        text-decoration: underline;
    }
    
    .breadcrumb-item.active {
        color: var(--secondary-color);
        font-weight: 400;
    }
    
    .card {
        border: none;
        box-shadow: var(--box-shadow);
        border-radius: var(--border-radius);
        overflow: hidden;
        transition: var(--transition);
        height: 100%;
        animation: fadeIn 0.5s ease-out;
    }
    
    .card:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    .card-header {
        background: linear-gradient(45deg, var(--primary-color), #3b82f6);
        color: white;
        border-bottom: none;
        padding: 1.25rem 1.5rem;
        font-weight: 600;
        border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
    }
    
    .card-header i {
        font-size: 1.25rem;
        vertical-align: middle;
        margin-right: 0.5rem;
    }
    
    .card-body {
        padding: 2rem;
        background-color: white;
    }
    
    .form-label {
        font-weight: 500;
        color: #1e293b;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }
    
    .form-control, .form-select {
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        border: 1px solid #e2e8f0;
        font-size: 1rem;
        transition: all 0.3s ease;
        color: #1e293b;
        background-color: #f8fafc;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.15);
        background-color: white;
    }
    
    .input-group-text {
        border-radius: 0.5rem 0 0 0.5rem;
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-right: none;
        color: #475569;
        font-weight: 500;
    }
    
    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 500;
        font-size: 1rem;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-primary {
        background: var(--primary-color);
        border: none;
        color: white;
    }
    
    .btn-primary:hover, .btn-primary:focus {
        background: var(--primary-hover);
        box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3), 0 2px 4px -2px rgba(37, 99, 235, 0.3);
        transform: translateY(-2px);
    }
    
    .btn-secondary {
        background: white;
        border: 1px solid #e2e8f0;
        color: var(--secondary-color);
    }
    
    .btn-secondary:hover, .btn-secondary:focus {
        background: #f8fafc;
        color: #1e293b;
        border-color: #cbd5e1;
        transform: translateY(-2px);
    }
    
    .alert {
        border: none;
        border-radius: var(--border-radius);
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .alert-danger {
        background-color: #fef2f2;
        color: #b91c1c;
    }
    
    .invalid-feedback {
        font-size: 0.875rem;
        color: var(--danger-color);
        margin-top: 0.375rem;
    }
    
    /* Animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(37, 99, 235, 0); }
        100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
    }
    
    .pulse {
        animation: pulse 2s infinite;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .container-fluid {
            padding: 1rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        h1 {
            font-size: 1.5rem;
        }
        
        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 1rem;
        }
        
        .btn {
            width: 100%;
        }
    }
    
    /* Password strength meter */
    .password-strength-meter {
        height: 0.5rem;
        background-color: #e2e8f0;
        border-radius: 1rem;
        margin-top: 0.5rem;
        overflow: hidden;
    }
    
    .password-strength-meter-bar {
        height: 100%;
        border-radius: 1rem;
        transition: width 0.3s ease, background-color 0.3s ease;
    }
    
    .password-strength-text {
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }
    
    .very-weak { width: 20%; background-color: #ef4444; }
    .weak { width: 40%; background-color: #f59e0b; }
    .medium { width: 60%; background-color: #f59e0b; }
    .strong { width: 80%; background-color: #10b981; }
    .very-strong { width: 100%; background-color: #10b981; }
    
    /* Form field indicator */
    .form-control.is-valid, .form-select.is-valid {
        border-color: var(--success-color);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2310b981'%3E%3Cpath d='M20.285 2l-11.285 11.567-5.286-5.011-3.714 3.716 9 8.728 15-15.285z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 1rem;
    }
    
    .form-control.is-invalid, .form-select.is-invalid {
        border-color: var(--danger-color);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23ef4444'%3E%3Cpath d='M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-1.5 5h3v10h-3v-10zm1.5 14.25c-.69 0-1.25-.56-1.25-1.25s.56-1.25 1.25-1.25 1.25.56 1.25 1.25-.56 1.25-1.25 1.25z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 1rem;
    }
    
    .form-text {
        color: #64748b;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }
    
    /* Toggle password visibility */
    .toggle-password {
        cursor: pointer;
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        z-index: 10;
    }
</style>

<div class="container-fluid px-4">
    <h1 class="mt-4">Tambah Admin</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php">Admin</a></li>
        <li class="breadcrumb-item active">Tambah Admin</li>
    </ol>

    <div class="row">
        <div class="col-xl-6 mx-auto">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-plus-circle me-1"></i>
                    Form Tambah Admin
                </div>
                <div class="card-body">
                    <?php if (isset($error_msg)) : ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                            <div>
                                <strong>Gagal!</strong> <?= $error_msg; ?>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="" method="post" class="needs-validation" novalidate>
                        <div class="mb-4">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-person text-primary"></i>
                                </span>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                       placeholder="Masukkan username" required>
                            </div>
                            <div class="invalid-feedback">
                                Username harus diisi!
                            </div>
                            <div class="form-text">
                                Username akan digunakan untuk login.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group position-relative">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-key text-primary"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Masukkan password" required>
                                <i class="bi bi-eye-slash toggle-password" id="togglePassword"></i>
                            </div>
                            <div class="invalid-feedback">
                                Password harus diisi!
                            </div>
                            <div class="password-strength-meter mt-2">
                                <div class="password-strength-meter-bar" id="passwordStrengthBar"></div>
                            </div>
                            <div class="password-strength-text" id="passwordStrengthText"></div>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirm" class="form-label">Konfirmasi Password</label>
                            <div class="input-group position-relative">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-key-fill text-primary"></i>
                                </span>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" 
                                       placeholder="Konfirmasi password" required>
                                <i class="bi bi-eye-slash toggle-password" id="toggleConfirmPassword"></i>
                            </div>
                            <div class="invalid-feedback">
                                Konfirmasi password harus diisi!
                            </div>
                            <div id="passwordMatch" class="form-text"></div>
                        </div>

                        <div class="mb-4">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-person-badge text-primary"></i>
                                </span>
                                <input type="text" class="form-control" id="nama" name="nama" 
                                       value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>" 
                                       placeholder="Masukkan nama lengkap" required>
                            </div>
                            <div class="invalid-feedback">
                                Nama lengkap harus diisi!
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-envelope text-primary"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                       placeholder="Masukkan email" required>
                            </div>
                            <div class="invalid-feedback">
                                Email harus diisi dengan format yang benar!
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="role" class="form-label">Level Akses</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-shield text-primary"></i>
                                </span>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Pilih Level Akses</option>
                                    <option value="admin" <?= (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                    <option value="superadmin" <?= (isset($_POST['role']) && $_POST['role'] == 'superadmin') ? 'selected' : ''; ?>>Super Admin</option>
                                </select>
                            </div>
                            <div class="invalid-feedback">
                                Level akses harus dipilih!
                            </div>
                            <div class="form-text">
                                Super Admin memiliki akses penuh ke semua fitur.
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="status" name="status" checked>
                                <label class="form-check-label" for="status">Aktifkan Akun</label>
                            </div>
                            <div class="form-text">
                                Pengguna hanya dapat login jika akun aktif.
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" name="tambah" class="btn btn-primary pulse">
                                <i class="bi bi-save"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form Validation
(function () {
    'use strict';
    
    // Fetch all forms we want to apply validation styles to
    var forms = document.querySelectorAll('.needs-validation');
    
    // Loop over them and prevent submission
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var password = document.getElementById('password');
            var confirmPassword = document.getElementById('password_confirm');
            
            // Validasi password match
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity("Passwords tidak cocok");
            } else {
                confirmPassword.setCustomValidity("");
            }
            
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('input', () => {
                if (input.checkValidity()) {
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                } else {
                    input.classList.remove('is-valid');
                    input.classList.add('is-invalid');
                }
            });
        });
    });
})();

// Toggle Password Visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordField = document.getElementById('password');
    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordField.setAttribute('type', type);
    this.classList.toggle('bi-eye');
    this.classList.toggle('bi-eye-slash');
});

document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
    const passwordField = document.getElementById('password_confirm');
    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordField.setAttribute('type', type);
    this.classList.toggle('bi-eye');
    this.classList.toggle('bi-eye-slash');
});

// Password Strength Meter
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('passwordStrengthBar');
    const strengthText = document.getElementById('passwordStrengthText');
    
    // Calculate password strength
    let strength = 0;
    
    // Length check
    if (password.length >= 6) strength += 1;
    if (password.length >= 10) strength += 1;
    
    // Character type checks
    if (/[A-Z]/.test(password)) strength += 1;
    if (/[0-9]/.test(password)) strength += 1;
    if (/[^A-Za-z0-9]/.test(password)) strength += 1;
    
    // Update UI
    strengthBar.className = 'password-strength-meter-bar';
    
    if (password.length === 0) {
        strengthBar.style.width = '0%';
        strengthText.textContent = '';
    } else if (strength < 2) {
        strengthBar.classList.add('very-weak');
        strengthText.textContent = 'Password sangat lemah';
        strengthText.style.color = '#ef4444';
    } else if (strength < 3) {
        strengthBar.classList.add('weak');
        strengthText.textContent = 'Password lemah';
        strengthText.style.color = '#f59e0b';
    } else if (strength < 4) {
        strengthBar.classList.add('medium');
        strengthText.textContent = 'Password sedang';
        strengthText.style.color = '#f59e0b';
    } else if (strength < 5) {
        strengthBar.classList.add('strong');
        strengthText.textContent = 'Password kuat';
        strengthText.style.color = '#10b981';
    } else {
        strengthBar.classList.add('very-strong');
        strengthText.textContent = 'Password sangat kuat';
        strengthText.style.color = '#10b981';
    }
});

// Password Match Checker
document.getElementById('password_confirm').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    const matchText = document.getElementById('passwordMatch');
    
    if (confirmPassword.length === 0) {
        matchText.textContent = '';
    } else if (password === confirmPassword) {
        matchText.textContent = 'Password cocok';
        matchText.style.color = '#10b981';
    } else {
        matchText.textContent = 'Password tidak cocok';
        matchText.style.color = '#ef4444';
    }
});

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    document.querySelectorAll('.alert').forEach(function(alert) {
        var bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);

// Focus on input field when page loads
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('username').focus();
});
</script>

<?php include_once '../includes/footer.php'; ?>