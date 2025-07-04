<?php
session_start();
require_once '../config/koneksi.php';

// Redirect if already logged in
if (isset($_SESSION['login'])) {
    if ($_SESSION['role'] === 'user') {
        header("Location: ../user/index.php");
    } else {
        header("Location: ../index.php");
    }
    exit;
}

$step = isset($_GET['step']) ? $_GET['step'] : 1;

// Step 1: Verifikasi identitas
if (isset($_POST['verify'])) {
    $username = htmlspecialchars($_POST['username']);
    $telepon = htmlspecialchars($_POST['telepon']);
    
    // Validasi input
    if (empty($username) || empty($telepon)) {
        $error = "Semua field harus diisi!";
    } else {
        // Periksa kecocokan username dan telepon
        $query = "SELECT * FROM tb_user WHERE nama = '$username' AND telepon = '$telepon'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) === 1) {
            // Data ditemukan, simpan data di session untuk digunakan di step 2
            $row = mysqli_fetch_assoc($result);
            $_SESSION['reset_user_id'] = $row['user_id'];
            $_SESSION['reset_username'] = $row['nama'];
            
            // Redirect ke step 2
            header("Location: forgot_password.php?step=2");
            exit;
        } else {
            $error = "Username atau nomor telepon tidak ditemukan!";
        }
    }
}

// Step 2: Reset password
if (isset($_POST['reset'])) {
    // Pastikan session reset_user_id ada
    if (!isset($_SESSION['reset_user_id'])) {
        header("Location: forgot_password.php");
        exit;
    }
    
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi password
    if (strlen($new_password) < 6) {
        $error = "Password minimal 6 karakter!";
    } elseif ($new_password !== $confirm_password) {
        $error = "Konfirmasi password tidak cocok!";
    } else {
        $user_id = $_SESSION['reset_user_id'];
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password
        $query = "UPDATE tb_user SET password = '$password_hash' WHERE user_id = $user_id";
        
        if (mysqli_query($conn, $query)) {
            // Hapus session reset
            unset($_SESSION['reset_user_id']);
            unset($_SESSION['reset_username']);
            
            // Set pesan sukses
            $_SESSION['success'] = "Password berhasil diubah! Silakan login dengan password baru.";
            header("Location: login.php");
            exit;
        } else {
            $error = "Terjadi kesalahan. Silakan coba lagi!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Unesa Laptop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), 
                        url('../assets/img/hero.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .reset-container {
            width: 100%;
            max-width: 450px;
        }

        .card {
            background-color: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
        }

        .card-header {
            background: linear-gradient(45deg, #0d6efd, #0dcaf0);
            color: white;
            text-align: center;
            border-radius: 15px 15px 0 0 !important;
            padding: 25px;
        }

        .card-header h4 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .card-body {
            padding: 40px;
        }

        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 8px;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        }

        .input-group-text {
            border: none;
            background: none;
            color: #6c757d;
        }

        .btn-primary {
            padding: 12px;
            font-weight: 600;
            border-radius: 10px;
            background: linear-gradient(45deg, #0d6efd, #0dcaf0);
            border: none;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px;
        }

        .alert-danger {
            background-color: #dc3545;
            color: white;
        }

        .divider {
            height: 1px;
            background: #e9ecef;
            margin: 25px 0;
        }

        .back-link {
            color: #6c757d;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
        }

        .back-link:hover {
            color: #0d6efd;
            transform: translateX(-5px);
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 25px;
        }
        
        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin: 0 15px;
            position: relative;
        }
        
        .step.active {
            background: linear-gradient(45deg, #0d6efd, #0dcaf0);
            color: white;
        }
        
        .step:not(:last-child):after {
            content: '';
            position: absolute;
            height: 2px;
            width: 40px;
            background-color: #e9ecef;
            right: -40px;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .step.active:not(:last-child):after {
            background: linear-gradient(45deg, #0d6efd, #0dcaf0);
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="card">
            <div class="card-header">
                <h4><i class="bi bi-laptop me-2"></i>Unesa Laptop</h4>
            </div>
            <div class="card-body">
                <h5 class="text-center mb-3">Reset Password</h5>
                
                <div class="step-indicator">
                    <div class="step <?= $step == 1 ? 'active' : ''; ?>">1</div>
                    <div class="step <?= $step == 2 ? 'active' : ''; ?>">2</div>
                </div>

                <?php if (isset($error)) : ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div><?= $error; ?></div>
                    </div>
                <?php endif; ?>
                
                <?php if ($step == 1) : ?>
                    <!-- Step 1: Verifikasi Identitas -->
                    <form action="" method="post">
                        <div class="mb-4">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" class="form-control" id="username" name="username" 
                                       placeholder="Masukkan username" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="telepon" class="form-label">Nomor Telepon</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-phone"></i>
                                </span>
                                <input type="text" class="form-control" id="telepon" name="telepon" 
                                       placeholder="Masukkan nomor telepon" required>
                            </div>
                        </div>
                        <button type="submit" name="verify" class="btn btn-primary w-100 mb-4">
                            <i class="bi bi-check-circle me-2"></i>Verifikasi
                        </button>
                    </form>

                <?php elseif ($step == 2) : ?>
                    <!-- Step 2: Reset Password -->
                    <?php if (!isset($_SESSION['reset_user_id'])) : ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Sesi verifikasi telah berakhir. Silakan coba lagi.
                        </div>
                        <a href="forgot_password.php" class="btn btn-primary w-100">
                            <i class="bi bi-arrow-left me-2"></i>Kembali
                        </a>
                    <?php else : ?>
                        <div class="alert alert-info d-flex align-items-center" role="alert">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <div>Halo, <strong><?= $_SESSION['reset_username']; ?></strong>. Silakan buat password baru Anda.</div>
                        </div>
                        <form action="" method="post" id="resetForm">
                            <div class="mb-4">
                                <label for="new_password" class="form-label">Password Baru</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="new_password" name="new_password" 
                                           placeholder="Masukkan password baru" required>
                                    <span class="input-group-text" onclick="togglePassword('new_password', 'toggleIcon1')">
                                        <i class="bi bi-eye" id="toggleIcon1"></i>
                                    </span>
                                </div>
                                <small class="text-muted">Minimal 6 karakter</small>
                            </div>
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock-fill"></i>
                                    </span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           placeholder="Konfirmasi password baru" required>
                                    <span class="input-group-text" onclick="togglePassword('confirm_password', 'toggleIcon2')">
                                        <i class="bi bi-eye" id="toggleIcon2"></i>
                                    </span>
                                </div>
                            </div>
                            <button type="submit" name="reset" class="btn btn-primary w-100 mb-4">
                                <i class="bi bi-check2-circle me-2"></i>Simpan Password Baru
                            </button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>

                <div class="divider"></div>

                <div class="text-center">
                    <a href="login.php" class="back-link">
                        <i class="bi bi-arrow-left me-2"></i>
                        Kembali ke Halaman Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }

        <?php if ($step == 2 && isset($_SESSION['reset_user_id'])) : ?>
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword.length < 6) {
                e.preventDefault();
                alert('Password minimal 6 karakter!');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Konfirmasi password tidak cocok!');
                return;
            }
        });
        <?php endif; ?>

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                if (!alert.classList.contains('alert-info')) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);
    </script>
</body>
</html>