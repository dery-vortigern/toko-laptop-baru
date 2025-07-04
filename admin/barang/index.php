<?php
session_start();
require_once '../../config/koneksi.php';

// Existing PHP code remains unchanged
if (!isset($_SESSION['login']) || !isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    header("Location: ../auth/adminlogin.php");
    exit;
}

$query = "SELECT b.*, k.nama_kategori, m.nama_merk 
          FROM tb_barang b 
          LEFT JOIN tb_kategori k ON b.kategori_id = k.kategori_id 
          LEFT JOIN tb_merk m ON b.merk_id = m.merk_id
          ORDER BY b.barang_id DESC";
$barang = query($query);

include_once '../includes/header.php';
?>

<!-- Custom CSS -->
<style>
    :root {
        --primary-color: #2563eb;
        --primary-hover: #1d4ed8;
        --secondary-color: #475569;
        --success-color: #10b981;
        --danger-color: #ef4444;
        --warning-color: #f59e0b;
        --border-radius: 0.75rem;
        --box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
        --transition: all 0.3s ease;
    }

    body {
        font-family: 'Inter', 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        background-color: #f8fafc;
    }

    .page-header {
        background: linear-gradient(135deg, var(--primary-color), #3b82f6);
        color: white;
        padding: 2rem;
        border-radius: var(--border-radius);
        margin-bottom: 2rem;
        box-shadow: var(--box-shadow);
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        bottom: -50%;
        left: -50%;
        background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
        transform: rotate(45deg);
        z-index: 0;
    }

    .page-header * {
        position: relative;
        z-index: 1;
    }

    .breadcrumb-item a {
        color: rgba(255,255,255,0.9);
        text-decoration: none;
        font-weight: 500;
        transition: var(--transition);
    }

    .breadcrumb-item a:hover {
        color: white;
        text-decoration: underline;
    }

    .breadcrumb-item.active {
        color: white;
        font-weight: 400;
    }

    .card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        transition: var(--transition);
        overflow: hidden;
    }

    .card:hover {
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .card-header {
        background: white;
        border-bottom: 1px solid #e2e8f0;
        padding: 1.25rem 1.5rem;
        font-weight: 600;
        color: var(--dark-color);
    }

    .table {
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table th {
        background: #1e293b;
        color: white;
        font-weight: 500;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border: none;
    }

    .table th:first-child {
        border-top-left-radius: 0.5rem;
    }

    .table th:last-child {
        border-top-right-radius: 0.5rem;
    }

    .table td {
        vertical-align: middle;
        padding: 1rem 0.75rem;
        border-top: none;
        border-bottom: 1px solid #e2e8f0;
        transition: var(--transition);
    }

    .table tr:hover td {
        background-color: #f8fafc;
    }

    .table tr:last-child td {
        border-bottom: none;
    }

    .table tr:last-child td:first-child {
        border-bottom-left-radius: 0.5rem;
    }

    .table tr:last-child td:last-child {
        border-bottom-right-radius: 0.5rem;
    }

    .img-thumbnail {
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: transform 0.2s;
        border: 2px solid #e2e8f0;
        padding: 0.25rem;
        object-fit: cover;
    }

    .img-thumbnail:hover {
        transform: scale(1.05);
        border-color: var(--primary-color);
    }

    .badge {
        padding: 0.5em 1em;
        font-weight: 500;
        border-radius: 2rem;
        letter-spacing: 0.5px;
        transition: var(--transition);
    }

    .badge:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1);
    }

    .btn {
        padding: 0.6rem 1.2rem;
        border-radius: 0.5rem;
        font-weight: 500;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-sm {
        padding: 0.4rem 0.8rem;
        font-size: 0.875rem;
    }

    .btn-primary {
        background: var(--primary-color);
        border: none;
        color: white;
    }

    .btn-primary:hover, .btn-primary:focus {
        background: var(--primary-hover);
        box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.5), 0 2px 4px -2px rgba(37, 99, 235, 0.5);
        transform: translateY(-2px);
    }

    .btn-warning {
        background: var(--warning-color);
        border: none;
        color: white;
    }

    .btn-warning:hover, .btn-warning:focus {
        background: #ea580c;
        color: white;
        box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.5), 0 2px 4px -2px rgba(245, 158, 11, 0.5);
        transform: translateY(-2px);
    }

    .btn-danger {
        background: var(--danger-color);
        border: none;
    }

    .btn-danger:hover, .btn-danger:focus {
        background: #dc2626;
        box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.5), 0 2px 4px -2px rgba(239, 68, 68, 0.5);
        transform: translateY(-2px);
    }

    .btn-light {
        background: white;
        border: 1px solid #e2e8f0;
        color: var(--secondary-color);
    }

    .btn-light:hover, .btn-light:focus {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: var(--dark-color);
    }

    .alert {
        border: none;
        border-radius: var(--border-radius);
        padding: 1rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .alert-success {
        background-color: #ecfdf5;
        color: #065f46;
    }

    .alert-danger {
        background-color: #fef2f2;
        color: #991b1b;
    }

    .alert-dismissible .btn-close {
        color: inherit;
        opacity: 0.8;
    }

    /* Image preview styling */
    .preview-container {
        position: relative;
        display: inline-block;
    }
    
    .preview-badge {
        position: absolute;
        top: 5px;
        right: 5px;
        z-index: 2;
    }
    
    /* Lighbox Style */
    .lightbox {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 1050;
        padding: 30px;
        box-sizing: border-box;
    }
    
    .lightbox-content {
        max-width: 90%;
        max-height: 80vh;
        background-color: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        position: relative;
        animation: none;
    }
    
    .lightbox-close {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 24px;
        color: white;
        cursor: pointer;
        z-index: 1051;
        background-color: rgba(0, 0, 0, 0.3);
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: background-color 0.2s;
    }
    
    .lightbox-close:hover {
        background-color: rgba(0, 0, 0, 0.5);
    }
    
    .lightbox-img {
        display: block;
        max-width: 100%;
        max-height: 80vh;
        margin: 0 auto;
        box-shadow: none;
        border-radius: 8px;
    }
    
    .lightbox-title {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 15px;
        font-weight: bold;
        text-align: center;
    }

    /* DataTables customization */
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #e2e8f0;
        border-radius: 0.375rem;
        padding: 0.5rem 2.5rem 0.5rem 1rem;
        font-size: 0.875rem;
        background-position: right 0.5rem center;
    }

    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #e2e8f0;
        border-radius: 0.375rem;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        min-width: 250px;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        outline: none;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 0.375rem;
        border: 1px solid #e2e8f0;
        background: white;
        color: var(--secondary-color) !important;
        font-weight: 500;
        padding: 0.5rem 1rem;
        margin: 0 0.25rem;
        transition: var(--transition);
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        border-color: var(--primary-color);
        background: #f1f5f9;
        color: var(--primary-color) !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: white !important;
    }

    .dataTables_wrapper .dataTables_info {
        padding-top: 1rem;
        font-size: 0.875rem;
        color: var(--secondary-color);
    }

    /* Notifikasi stok */
    .badge.bg-danger {
        background-color: #fecaca !important;
        color: #991b1b;
    }

    .badge.bg-success {
        background-color: #d1fae5 !important;
        color: #065f46;
    }

    .badge.bg-info {
        background-color: #dbeafe !important;
        color: #1e40af;
    }

    .badge.bg-light {
        background-color: #f3f4f6 !important;
        color: #374151;
    }

    /* Animasi untuk alert */
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert {
        animation: fadeInDown 0.5s ease-out;
    }

    /* Tombol export */
    .dt-buttons {
        margin-bottom: 1rem;
    }

    .dt-buttons .btn {
        margin-right: 0.5rem;
    }
</style>

<div class="container-fluid px-4">
    <div class="page-header">
        <h1 class="mb-2 fw-bold">Data Laptop</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Laptop</li>
            </ol>
        </nav>
    </div>

    <!-- Tombol Tambah & Pesan -->
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Laptop
        </a>
        <div class="d-flex align-items-center">
            <i class="bi bi-clock-history text-muted me-2"></i>
            <span class="text-muted">Terakhir diperbarui: <?= date('d M Y H:i') ?></span>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div>
                <strong>Berhasil!</strong> <?= $_SESSION['success']; ?>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div>
                <strong>Gagal!</strong> <?= $_SESSION['error']; ?>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Tabel Barang -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-laptop fs-5 text-primary me-2"></i>
                    <span class="fw-bold">Data Laptop</span>
                </div>
                <button class="btn btn-light btn-sm" onclick="window.location.reload()">
                    <i class="bi bi-arrow-clockwise"></i>
                    Refresh
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="dataTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="10%">Gambar</th>
                            <th width="15%">Nama Laptop</th>
                            <th width="8%">Merk</th>
                            <th width="10%">Kategori</th>
                            <th width="20%">Spesifikasi</th>
                            <th width="10%">Harga Beli</th>
                            <th width="10%">Harga Jual</th>
                            <th width="5%">Stok</th>
                            <th width="12%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; ?>
                        <?php foreach ($barang as $row) : ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <?php if ($row['gambar'] && file_exists("../../assets/img/barang/" . $row['gambar'])) : ?>
                                    <div class="preview-container">
                                        <img src="../../assets/img/barang/<?= $row['gambar']; ?>" 
                                             alt="<?= htmlspecialchars($row['nama_barang']); ?>" 
                                             class="img-thumbnail show-lightbox"
                                             style="width: 80px; height: 80px; object-fit: cover;"
                                             data-img="../../assets/img/barang/<?= $row['gambar']; ?>"
                                             data-title="<?= htmlspecialchars($row['nama_barang']); ?>">
                                        <span class="badge bg-info preview-badge">
                                            <i class="bi bi-zoom-in"></i>
                                        </span>
                                    </div>
                                <?php else : ?>
                                    <img src="../../assets/img/no-image.jpg" 
                                         alt="No Image" 
                                         class="img-thumbnail"
                                         style="width: 80px; height: 80px; object-fit: cover;">
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold text-primary"><?= htmlspecialchars($row['nama_barang']); ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light">
                                    <?= htmlspecialchars($row['nama_merk']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <?= htmlspecialchars($row['nama_kategori']); ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted d-block" style="max-height: 80px; overflow: auto;">
                                    <?= htmlspecialchars($row['jenis_barang']); ?>
                                </small>
                            </td>
                            <td>
                                <div class="text-muted">
                                    Rp <?= number_format($row['harga_beli'], 0, ',', '.'); ?>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-success">
                                    Rp <?= number_format($row['harga_jual'], 0, ',', '.'); ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php if ($row['stok'] <= 5) : ?>
                                    <span class="badge bg-danger">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                        <?= $row['stok']; ?>
                                    </span>
                                <?php else : ?>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle-fill me-1"></i>
                                        <?= $row['stok']; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="edit.php?id=<?= $row['barang_id']; ?>" 
                                       class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <a href="hapus.php?id=<?= $row['barang_id']; ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus laptop <?= htmlspecialchars($row['nama_barang']); ?>?')">
                                        <i class="bi bi-trash"></i> Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Custom Lightbox - Menggantikan Modal Bootstrap -->
<div id="customLightbox" class="lightbox">
    <div class="lightbox-close">&times;</div>
    <div class="lightbox-content">
        <img id="lightboxImg" class="lightbox-img" src="" alt="Preview">
        <div id="lightboxTitle" class="lightbox-title"></div>
    </div>
</div>

<!-- DataTables Script with enhanced configuration -->
<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json',
            searchPlaceholder: "Cari laptop...",
            search: "", // Remove search label text
            lengthMenu: "_MENU_ data per halaman"
        },
        responsive: true,
        pageLength: 10,
        dom: '<"dt-buttons"B><"clear">lfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-excel me-1"></i>Export Excel',
                className: 'btn btn-success btn-sm me-2',
                exportOptions: {
                    columns: [0, 2, 3, 4, 5, 6, 7, 8]
                }
            },
            {
                extend: 'pdf',
                text: '<i class="bi bi-file-earmark-pdf me-1"></i>Export PDF',
                className: 'btn btn-danger btn-sm',
                exportOptions: {
                    columns: [0, 2, 3, 4, 5, 6, 7, 8]
                }
            }
        ],
        "order": [[0, "asc"]],
        "columnDefs": [
            { "orderable": false, "targets": [1, 9] }
        ]
    });

    // Enhanced filter input
    $('.dataTables_filter input').attr('placeholder', 'Cari laptop...');
    $('.dataTables_filter input').addClass('form-control-search');
    
    // Make filter input bigger
    $('.dataTables_filter').addClass('mb-3');
    
    // Custom lightbox implementation - mengganti modal Bootstrap
    const lightbox = document.getElementById('customLightbox');
    const lightboxImg = document.getElementById('lightboxImg');
    const lightboxTitle = document.getElementById('lightboxTitle');
    const lightboxClose = document.querySelector('.lightbox-close');
    
    // Preload semua gambar
    function preloadImages() {
        const images = document.querySelectorAll('.show-lightbox');
        images.forEach(img => {
            const imgSrc = img.getAttribute('data-img');
            const preloadImage = new Image();
            preloadImage.src = imgSrc;
        });
    }
    
    // Panggil preload saat halaman dimuat
    preloadImages();
    
    // Tampilkan lightbox saat gambar diklik
    document.querySelectorAll('.show-lightbox').forEach(img => {
        img.addEventListener('click', function() {
            const imgSrc = this.getAttribute('data-img');
            const imgTitle = this.getAttribute('data-title');
            
            // Set konten lightbox
            lightboxImg.src = imgSrc;
            lightboxTitle.textContent = imgTitle;
            
            // Tampilkan lightbox dengan gentle fade
            lightbox.style.display = 'flex';
            setTimeout(() => {
                lightbox.style.opacity = '1';
            }, 10);
        });
    });
    
    // Tutup lightbox
    lightboxClose.addEventListener('click', closeLightbox);
    lightbox.addEventListener('click', function(e) {
        if (e.target === lightbox) {
            closeLightbox();
        }
    });
    
    // Fungsi untuk menutup lightbox
    function closeLightbox() {
        lightbox.style.opacity = '0';
        setTimeout(() => {
            lightbox.style.display = 'none';
        }, 300);
    }
    
    // Tombol ESC untuk menutup lightbox
    document.addEventListener('keyup', function(e) {
        if (e.key === 'Escape' && lightbox.style.display === 'flex') {
            closeLightbox();
        }
    });
});

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    $('.alert').fadeOut('slow');
}, 5000);
</script>

<style>
/* Tambahan style untuk lightbox dengan transisi halus */
.lightbox {
    opacity: 0;
    transition: opacity 0.3s ease;
}
</style>

<?php include_once '../includes/footer.php'; ?>