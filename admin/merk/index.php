<?php
session_start();
require_once '../../config/koneksi.php';

// Existing PHP code remains unchanged
if (!isset($_SESSION['login']) || !isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    header("Location: ../auth/adminlogin.php");
    exit;
}

$merk = query("SELECT * FROM tb_merk ORDER BY merk_id DESC");
include_once '../includes/header.php';
?>

<style>
    :root {
        --primary-color: #2563eb;
        --primary-hover: #1d4ed8;
        --secondary-color: #475569;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --danger-color: #ef4444;
        --border-radius: 0.75rem;
        --box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
        --transition: all 0.3s ease;
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
        color: var(--secondary-color);
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
        padding: 0.625rem 1.25rem;
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
        color: white;
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
        color: #1e293b;
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

    .badge.bg-primary {
        background-color: #dbeafe !important;
        color: #1e40af;
    }

    .brand-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background-color: #eff6ff;
        color: var(--primary-color);
        transition: var(--transition);
    }

    .table tr:hover .brand-icon {
        background-color: var(--primary-color);
        color: white;
    }

    .description-cell {
        max-width: 300px;
        position: relative;
    }
    
    .description-text {
        text-overflow: ellipsis;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        line-height: 1.5;
        transition: var(--transition);
    }
    
    .description-text:hover {
        -webkit-line-clamp: initial;
        background-color: #f8fafc;
        padding: 0.5rem;
        border-radius: 0.375rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
        position: relative;
        z-index: 10;
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

    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card {
        animation: fadeInUp 0.5s ease-out;
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
        <h1 class="mb-2 fw-bold">Data Merk</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Merk</li>
            </ol>
        </nav>
    </div>

    <div class="mb-4 d-flex justify-content-between align-items-center">
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Merk
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

    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-bookmark-fill text-primary me-2"></i>
                    <span class="fw-bold">Data Merk</span>
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
                            <th width="80">No</th>
                            <th>Nama Merk</th>
                            <th>Deskripsi</th>
                            <th>Jumlah Produk</th>
                            <th width="200">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($merk as $row) : 
                            $merk_id = $row['merk_id'];
                            $jumlah_produk = query("SELECT COUNT(*) as total FROM tb_barang WHERE merk_id = $merk_id")[0]['total'];
                        ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="brand-icon me-3">
                                        <i class="bi bi-award fs-5"></i>
                                    </div>
                                    <span class="fw-medium"><?= htmlspecialchars($row['nama_merk']); ?></span>
                                </div>
                            </td>
                            <td class="description-cell">
                                <div class="description-text">
                                    <?= empty($row['deskripsi']) ? '<span class="text-muted fst-italic">Tidak ada deskripsi</span>' : htmlspecialchars($row['deskripsi']); ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary">
                                    <i class="bi bi-box me-1"></i>
                                    <?= $jumlah_produk; ?> Produk
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="edit.php?id=<?= $row['merk_id']; ?>" 
                                       class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <a href="#" 
                                       onclick="confirmDelete('hapus.php?id=<?= $row['merk_id']; ?>', '<?= htmlspecialchars($row['nama_merk']); ?>')" 
                                       class="btn btn-danger btn-sm">
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

<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json',
            searchPlaceholder: "Cari merk...",
            search: "", 
            lengthMenu: "_MENU_ data per halaman"
        },
        dom: '<"dt-buttons"B><"clear">lfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-excel me-1"></i>Export Excel',
                className: 'btn btn-success btn-sm me-2',
                exportOptions: {
                    columns: [0, 1, 2, 3]
                }
            },
            {
                extend: 'pdf',
                text: '<i class="bi bi-file-earmark-pdf me-1"></i>Export PDF',
                className: 'btn btn-danger btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3]
                }
            }
        ]
    });

    // Enhanced filter input
    $('.dataTables_filter input').attr('placeholder', 'Cari merk...');
    $('.dataTables_filter input').addClass('form-control-search');
    
    // Make filter input bigger
    $('.dataTables_filter').addClass('mb-3');

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

function confirmDelete(url, nama) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menghapus merk "${nama}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        iconColor: '#ef4444',
        background: '#ffffff',
        customClass: {
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false,
        reverseButtons: true,
        padding: '2rem',
        showClass: {
            popup: 'animate__animated animate__fadeInDown animate__faster'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutUp animate__faster'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}
</script>

<?php include_once '../includes/footer.php'; ?>