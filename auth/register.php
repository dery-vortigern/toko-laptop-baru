<?php
session_start();
require_once '../config/koneksi.php';

// Jika sudah login, redirect
if (isset($_SESSION['login'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/index.php");
    } else {
        header("Location: ../user/index.php");
    }
    exit;
}

// Proses registrasi
if (isset($_POST['register'])) {
    // Ambil data dari form
    $nama = htmlspecialchars($_POST['nama']);
    $password = $_POST['password'];
    $alamat = htmlspecialchars($_POST['alamat']);
    $telepon = htmlspecialchars($_POST['telepon']);
    
    $error = false;
    
    // Validasi input
    if (empty($nama) || empty($password) || empty($alamat) || empty($telepon)) {
        $error = true;
        $error_msg = "Semua field harus diisi!";
    }
    
    // Validasi password
    if (strlen($password) < 6) {
        $error = true;
        $error_msg = "Password minimal 6 karakter!";
    }
    
    // Validasi nomor telepon
    if (!preg_match("/^[0-9]{10,15}$/", $telepon)) {
        $error = true;
        $error_msg = "Format nomor telepon tidak valid!";
    }
    
    // Cek username sudah dipakai atau belum
    $check_query = "SELECT user_id FROM tb_user WHERE nama = '$nama'";
    if (mysqli_num_rows(mysqli_query($conn, $check_query)) > 0) {
        $error = true;
        $error_msg = "Username sudah digunakan!";
    }
    
    // Jika tidak ada error, proses registrasi
    if (!$error) {
        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Data untuk disimpan ke database
        $data = [
            'nama' => $nama,
            'password' => $password_hash,
            'alamat' => $alamat,
            'telepon' => $telepon
        ];
        
        // Simpan ke database
        if (tambah('tb_user', $data)) {
            $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
            header("Location: login.php");
            exit;
        } else {
            $error_msg = "Terjadi kesalahan. Silakan coba lagi!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - WARINGIN-IT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)),
                        url('../assets/img/hero.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 40px 0;
        }

        .register-container {
            max-width: 550px;
            margin: auto;
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
            border: none;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        }

        .input-group-text {
            background: none;
            border: 2px solid #e9ecef;
            border-right: none;
        }

        .input-group .form-control {
            border-left: none;
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
        }

        .back-link {
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            font-weight: 500;
        }

        .back-link:hover {
            color: #0dcaf0;
            transform: translateX(-5px);
        }

        .login-link {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .login-link:hover {
            color: #0043a8;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-laptop me-2"></i>
                        WARINGIN-IT
                    </h4>
                </div>
                <div class="card-body p-4">
                    <h5 class="text-center mb-4">Daftar Akun Baru</h5>

                    <?php if (isset($error_msg)) : ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?= $error_msg; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="" method="post" id="registerForm">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" name="nama" 
                                       value="<?= isset($nama) ? $nama : ''; ?>" 
                                       placeholder="Masukkan username" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" name="password" id="password"
                                       placeholder="Masukkan password" required>
                                <span class="input-group-text" style="border-left: none; cursor: pointer;"
                                      onclick="togglePassword()">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </span>
                            </div>
                            <small class="text-muted">Minimal 6 karakter</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                <textarea class="form-control" name="alamat" rows="3" 
                                          placeholder="Masukkan alamat lengkap" required><?= isset($alamat) ? $alamat : ''; ?></textarea>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Nomor Telepon</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                <input type="text" class="form-control" name="telepon" 
                                       value="<?= isset($telepon) ? $telepon : ''; ?>" 
                                       placeholder="Contoh: 081234567890" required>
                            </div>
                        </div>

                        <button type="submit" name="register" class="btn btn-primary w-100 mb-4">
                            <i class="bi bi-person-plus me-2"></i>Daftar Sekarang
                        </button>

                        <div class="text-center">
                            <p class="mb-0">Sudah punya akun? 
                                <a href="login.php" class="login-link">Login sekarang</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="../index.php" class="back-link">
                    <i class="bi bi-arrow-left me-2"></i>
                    Kembali ke Halaman Utama
                </a>
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

        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const telepon = document.getElementById('telepon').value;
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password minimal 6 karakter!');
                return;
            }
            
            if (!/^[0-9]{10,15}$/.test(telepon)) {
                e.preventDefault();
                alert('Nomor telepon tidak valid!');
                return;
            }
        });
    </script>
</body>
</html>