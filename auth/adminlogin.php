<?php
session_start();
require_once '../config/koneksi.php';

// Aktifkan pelaporan error (debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect jika sudah login sebagai admin
if (isset($_SESSION['login']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'superadmin')) {
    header("Location: ../admin/index.php");
    exit;
}

// Proses login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek kredensial admin tanpa hash
    $query = "SELECT * FROM tb_admin WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    // Debugging
    echo "<!-- Query: $query -->";
    echo "<!-- Jumlah hasil: " . mysqli_num_rows($result) . " -->";

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Debugging
        echo "<!-- Username DB: " . $row['username'] . " -->";
        echo "<!-- Password DB: " . $row['password'] . " -->";
        echo "<!-- Password Input: $password -->";
        echo "<!-- Role: " . $row['role'] . " -->";
        
        // Perbandingan password langsung
        if ($password === $row['password']) {
            // Set session
            $_SESSION['login'] = true;
            $_SESSION['admin_id'] = $row['admin_id']; 
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role']; // 'admin' atau 'superadmin'
            
            // Debugging
            echo "<!-- Login berhasil, mengalihkan ke ../admin/index.php -->";
            
            // Redirect ke dashboard admin
            header("Location: ../admin/index.php");
            exit;
        } else {
            // Debugging
            echo "<!-- Password tidak cocok -->";
        }
    } else {
        // Debugging
        echo "<!-- Username tidak ditemukan -->";
    }

    // Login gagal
    $error = true;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - WARINGIN-IT</title>
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
            background: linear-gradient(45deg, #343a40, #495057);
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
            border-color: #343a40;
            box-shadow: 0 0 0 0.2rem rgba(52, 58, 64, 0.15);
        }

        .input-group-text {
            border: none;
            background: none;
            color: #6c757d;
        }

        .btn-dark {
            padding: 12px;
            font-weight: 600;
            border-radius: 10px;
            background: linear-gradient(45deg, #343a40, #495057);
            border: none;
            transition: all 0.3s ease;
        }

        .btn-dark:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 58, 64, 0.3);
        }

        .alert {
            border-radius: 10px;
            border: none;
            background-color: #dc3545;
            color: white;
            padding: 15px;
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
            color: #343a40;
            transform: translateX(-5px);
        }

        .admin-badge {
            background-color: #343a40;
            color: white;
            font-size: 12px;
            padding: 5px 10px;
            border-radius: 30px;
            display: inline-block;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <h4><i class="bi bi-shield-lock me-2"></i>Panel Admin WARINGIN-IT</h4>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <span class="admin-badge">
                        <i class="bi bi-person-badge"></i> Area Admin
                    </span>
                    <h5>Login Admin</h5>
                    <p class="text-muted small">Masukkan kredensial untuk mengakses panel admin</p>
                </div>

                <?php if (isset($error)) : ?>
                    <div class="alert d-flex align-items-center" role="alert">
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
                                   placeholder="Masukkan username admin" required>
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
                    <button type="submit" name="login" class="btn btn-dark w-100 mb-4">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login
                    </button>
                </form>

                <div class="divider"></div>

                <div class="text-center">
                    <a href="../index.php" class="back-link">
                        <i class="bi bi-arrow-left me-2"></i>
                        Kembali ke Halaman Utama
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tambahkan tombol debug untuk melihat isi session -->
    <?php if (isset($_GET['debug'])) : ?>
    <div style="position: fixed; bottom: 10px; right: 10px; background: rgba(0,0,0,0.8); color: white; padding: 10px; border-radius: 5px; max-width: 400px; font-size: 12px;">
        <h5>Debug Session:</h5>
        <pre><?php print_r($_SESSION); ?></pre>
    </div>
    <?php endif; ?>

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
    </script>
</body>
</html>