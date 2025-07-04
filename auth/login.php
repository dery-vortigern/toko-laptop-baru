<?php
session_start();
require_once '../config/koneksi.php';

// Redirect if already logged in as user
if (isset($_SESSION['login']) && $_SESSION['role'] === 'user') {
    header("Location: ../user/index.php");
    exit;
}

// Redirect if already logged in as admin
if (isset($_SESSION['login']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'superadmin')) {
    header("Location: ../index.php");
    exit;
}

// Handle success message from register
if (isset($_SESSION['success'])) {
    $success_msg = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Process user login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check user credentials
    $query = "SELECT * FROM tb_user WHERE nama = '$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            // Set session
            $_SESSION['login'] = true;
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['nama'];
            $_SESSION['role'] = 'user';
            
            // Redirect to user page
            header("Location: ../user/index.php");
            exit;
        }
    }

    // Login failed
    $error = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WARINGIN-IT</title>
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

        .login-container {
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

        .alert-success {
            background-color: #198754;
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

        .register-link {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .register-link:hover {
            color: #0043a8;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <h4><i class="bi bi-laptop me-2"></i>WARINGIN-IT</h4>
            </div>
            <div class="card-body">
                <h5 class="text-center mb-4">Login Pengguna</h5>

                <?php if (isset($success_msg)) : ?>
                    <div class="alert alert-success d-flex align-items-center alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <div><?= $success_msg; ?></div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)) : ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div>Username atau password salah!</div>
                    </div>
                <?php endif; ?>
                
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
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Masukkan password" required>
                            <span class="input-group-text" onclick="togglePassword()">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </span>
                        </div>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary w-100 mb-4">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login
                    </button>
                </form>

                <div class="divider"></div>

                <div class="text-center">
                    <p class="mb-3">Belum punya akun? 
                        <a href="register.php" class="register-link">Daftar sekarang</a>
                    </p>
                    <a href="../index.php" class="back-link">
                        <i class="bi bi-arrow-left me-2"></i>
                        Kembali ke Halaman Utama
                    </a>
                    <div class="text-center mb-3">
    <a href="forgot_password.php" class="text-primary text-decoration-none">
        <i class="bi bi-question-circle me-1"></i>Lupa Password?
    </a>
</div>
<div class="divider"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
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

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>