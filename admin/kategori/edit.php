<?php
session_start();
require_once '../../config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || !isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    header("Location: ../auth/adminlogin.php");
    exit;
}

// Cek parameter id
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$kategori_id = $_GET['id'];
$kategori = query("SELECT * FROM tb_kategori WHERE kategori_id = $kategori_id")[0];

// Proses edit kategori
if (isset($_POST['edit'])) {
    $nama_kategori = htmlspecialchars($_POST['nama_kategori']);
    
    // Validasi
    if (empty($nama_kategori)) {
        $error = "Nama kategori harus diisi!";
    } else {
        // Cek duplikat nama kategori, kecuali untuk kategori yang sedang diedit
        $cek = query("SELECT * FROM tb_kategori WHERE nama_kategori = '$nama_kategori' AND kategori_id != $kategori_id");
        
        if ($cek) {
            $error = "Nama kategori sudah ada!";
        } else {
            // Update data
            $data = ['nama_kategori' => $nama_kategori];
            
            if (ubah('tb_kategori', $data, "kategori_id = $kategori_id")) {
                $_SESSION['success'] = "Kategori berhasil diupdate!";
                header("Location: index.php");
                exit;
            } else {
                $error = "Gagal mengupdate kategori!";
            }
        }
    }
}

// Include header
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
        background: linear-gradient(45deg, var(--warning-color), #f97316);
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
        font-weight: 600;
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
        color: var(--primary-color);
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
    <h1 class="mt-4">Edit Kategori</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php">Kategori</a></li>
        <li class="breadcrumb-item active">Edit Kategori</li>
    </ol>

    <div class="row">
        <div class="col-xl-6 mx-auto">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-pencil-square me-1"></i>
                    Form Edit Kategori
                </div>
                <div class="card-body">
                    <?php if (isset($error)) : ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                            <div>
                                <strong>Gagal!</strong> <?= $error; ?>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="" method="post" class="needs-validation" novalidate>
                        <div class="mb-4">
                            <label for="nama_kategori" class="form-label">Nama Kategori</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-tag"></i>
                                </span>
                                <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" 
                                       value="<?= htmlspecialchars($kategori['nama_kategori']); ?>" required>
                            </div>
                            <div class="invalid-feedback">
                                Nama kategori harus diisi!
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" name="edit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update
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
    document.getElementById('nama_kategori').focus();
    document.getElementById('nama_kategori').select();
});
</script>

<?php include_once '../includes/footer.php'; ?>