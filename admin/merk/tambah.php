<?php
session_start();
require_once '../../config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || !isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    header("Location: ../auth/adminlogin.php");
    exit;
}

// Proses tambah merk
if (isset($_POST['tambah'])) {
    $nama_merk = htmlspecialchars($_POST['nama_merk']);
    $deskripsi = htmlspecialchars($_POST['deskripsi']);
    
    // Validasi
    $error = false;
    
    // Cek nama merk kosong
    if (empty($nama_merk)) {
        $error = true;
        $error_msg = "Nama merk harus diisi!";
    }
    
    // Cek duplikat nama merk
    $cek = query("SELECT * FROM tb_merk WHERE nama_merk = '$nama_merk'");
    if ($cek) {
        $error = true;
        $error_msg = "Nama merk sudah ada!";
    }
    
    if (!$error) {
        $data = [
            'nama_merk' => $nama_merk,
            'deskripsi' => $deskripsi
        ];
        
        if (tambah('tb_merk', $data)) {
            $_SESSION['success'] = "Merk berhasil ditambahkan!";
            header("Location: index.php");
            exit;
        } else {
            $error_msg = "Gagal menambahkan merk!";
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
    
    .form-control {
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        border: 1px solid #e2e8f0;
        font-size: 1rem;
        transition: all 0.3s ease;
        color: #1e293b;
        background-color: #f8fafc;
    }
    
    .form-control:focus {
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
    
    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }
    
    .form-text {
        color: #64748b;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }
</style>

<div class="container-fluid px-4">
    <h1 class="mt-4">Tambah Merk</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php">Merk</a></li>
        <li class="breadcrumb-item active">Tambah Merk</li>
    </ol>

    <div class="row">
        <div class="col-xl-6 mx-auto">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-plus-circle me-1"></i>
                    Form Tambah Merk
                </div>
                <div class="card-body">
                    <?php if (isset($error_msg)) : ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                            <div>
                                <strong>Gagal!</strong> <?= $error_msg ?>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" class="needs-validation" novalidate>
                        <div class="mb-4">
                            <label for="nama_merk" class="form-label">Nama Merk</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-award text-primary"></i>
                                </span>
                                <input type="text" class="form-control" id="nama_merk" name="nama_merk" 
                                       value="<?= isset($_POST['nama_merk']) ? htmlspecialchars($_POST['nama_merk']) : '' ?>" 
                                       placeholder="Masukkan nama merk" required>
                            </div>
                            <div class="invalid-feedback">
                                Nama merk harus diisi!
                            </div>
                            <div class="form-text text-muted">
                                Contoh: Lenovo, Asus, HP, Dell, dll.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" 
                                      placeholder="Masukkan deskripsi atau informasi tentang merk ini"><?= isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : '' ?></textarea>
                            <div class="form-text text-muted">
                                Deskripsi singkat tentang merk. Opsional.
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
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
})()

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    document.querySelectorAll('.alert').forEach(function(alert) {
        var bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);

// Focus on input field when page loads
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('nama_merk').focus();
});
</script>

<?php include_once '../includes/footer.php'; ?>