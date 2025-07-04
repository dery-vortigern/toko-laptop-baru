<?php
session_start();
require_once '../../config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || !isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    header("Location: ../auth/adminlogin.php");
    exit;
}

// Ambil data barang untuk dropdown
$barang = query("SELECT * FROM tb_barang ORDER BY nama_barang ASC");

// Proses tambah supplier
if (isset($_POST['tambah'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $alamat = htmlspecialchars($_POST['alamat']);
    $telepon = htmlspecialchars($_POST['telepon']);
    $barang_id = $_POST['barang_id'];
    
    // Validasi
    $error = false;
    
    // Cek nama kosong
    if (empty($nama)) {
        $error = true;
        $error_msg = "Nama supplier harus diisi!";
    }
    
    // Bersihkan format telepon sebelum validasi
    $clean_telepon = str_replace('-', '', $telepon);
    
    // Validasi nomor telepon (10-12 digit)
    if (!preg_match("/^[0-9]{10,12}$/", $clean_telepon)) {
        $error = true;
        $error_msg = "Format nomor telepon tidak valid! Harus 10-12 digit angka.";
    }

    if (!$error) {
        $data = [
            'nama' => $nama,
            'alamat' => $alamat,
            'telepon' => $clean_telepon,
            'barang_id' => $barang_id
        ];
        
        if (tambah('tb_supplier', $data)) {
            $_SESSION['success'] = "Supplier berhasil ditambahkan!";
            header("Location: index.php");
            exit;
        } else {
            $error_msg = "Gagal menambahkan supplier!";
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
    
    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }
    
    .form-text {
        color: #64748b;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }
    
    /* Custom select styling */
    .form-select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%232563eb' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3E%3C/svg%3E");
        background-size: 16px 12px;
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
</style>

<div class="container-fluid px-4">
    <h1 class="mt-4">Tambah Supplier</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php">Supplier</a></li>
        <li class="breadcrumb-item active">Tambah Supplier</li>
    </ol>

    <div class="row">
        <div class="col-xl-6 mx-auto">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-plus-circle me-1"></i>
                    Form Tambah Supplier
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
                            <label for="nama" class="form-label">Nama Supplier</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-building text-primary"></i>
                                </span>
                                <input type="text" class="form-control" id="nama" name="nama" 
                                       value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>" 
                                       placeholder="Masukkan nama supplier" required>
                            </div>
                            <div class="invalid-feedback">
                                Nama supplier harus diisi!
                            </div>
                            <div class="form-text">
                                Contoh: PT Sigma Computer, CV Laptop Jaya, dll.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="alamat" class="form-label">Alamat</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-geo-alt text-primary"></i>
                                </span>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" 
                                          placeholder="Masukkan alamat lengkap"><?= isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : ''; ?></textarea>
                            </div>
                            <div class="form-text">
                                Alamat lengkap supplier. Opsional.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="telepon" class="form-label">Nomor Telepon</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-telephone text-primary"></i>
                                </span>
                                <input type="text" class="form-control" id="telepon" name="telepon" 
                                       value="<?= isset($_POST['telepon']) ? htmlspecialchars($_POST['telepon']) : ''; ?>" 
                                       placeholder="08xxxxxxxxxx" required>
                            </div>
                            <div class="invalid-feedback">
                                Nomor telepon harus diisi dengan format yang benar!
                            </div>
                            <div class="form-text">
                                Format: 081234567890 (10-12 digit tanpa spasi/karakter khusus)
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="barang_id" class="form-label">Produk</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-laptop text-primary"></i>
                                </span>
                                <select class="form-select" id="barang_id" name="barang_id" required>
                                    <option value="">Pilih Produk</option>
                                    <?php foreach ($barang as $item) : ?>
                                        <option value="<?= $item['barang_id']; ?>" 
                                                <?= (isset($_POST['barang_id']) && $_POST['barang_id'] == $item['barang_id']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($item['nama_barang']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="invalid-feedback">
                                Pilih produk yang disupply!
                            </div>
                            <div class="form-text">
                                Pilih laptop yang disediakan oleh supplier ini.
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
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
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

// Phone number formatter
document.getElementById('telepon').addEventListener('input', function(e) {
    // Hapus semua karakter non-digit
    let value = this.value.replace(/\D/g, '');
    
    // Batasi panjang input maksimal 12 digit
    value = value.substring(0, 12);
    
    // Update nilai input
    this.value = value;
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
    document.getElementById('nama').focus();
});
</script>

