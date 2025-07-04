<?php
session_start();
require_once '../../config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || !isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    header("Location: ../auth/adminlogin.php");
    exit;
}

// Ambil data admin dengan query langsung
$admin_query = "SELECT * FROM tb_admin ORDER BY admin_id DESC";
$admin_list = query($admin_query);

// Ambil ID admin saat ini
$current_admin_id = $_SESSION['admin_id'] ?? 0;

include_once '../includes/header.php';
?>

<style>
    /* Main Styles */
    .page-header {
        background: linear-gradient(135deg, #2563eb, #3b82f6);
        color: white;
        padding: 2rem;
        border-radius: 0.75rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
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
        transition: all 0.3s ease;
    }

    .breadcrumb-item a:hover {
        color: white;
        text-decoration: underline;
    }

    .breadcrumb-item.active {
        color: white;
        font-weight: 400;
    }

    /* Card Styles */
    .card {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        overflow: hidden;
        animation: fadeIn 0.5s ease-out;
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
        color: #475569;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-header-title {
        display: flex;
        align-items: center;
    }

    .card-header-title i {
        font-size: 1.25rem;
        color: #2563eb;
        margin-right: 0.75rem;
    }

    .card-body {
        padding: 1.5rem;
    }

    /* Button Styles */
    .btn {
        padding: 0.625rem 1.25rem;
        border-radius: 0.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-sm {
        padding: 0.4rem 0.8rem;
        font-size: 0.875rem;
    }

    .btn-primary {
        background: #2563eb;
        border: none;
        color: white;
    }

    .btn-primary:hover, .btn-primary:focus {
        background: #1d4ed8;
        box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.5), 0 2px 4px -2px rgba(37, 99, 235, 0.5);
        transform: translateY(-2px);
    }

    .btn-warning {
        background: #f59e0b;
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
        background: #ef4444;
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
        color: #475569;
    }

    .btn-light:hover, .btn-light:focus {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #1e293b;
        transform: translateY(-2px);
    }

    /* Table Styles */
    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-bottom: 0;
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
        transition: all 0.3s ease;
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

    /* Badge Styles */
    .badge {
        padding: 0.5em 1em;
        font-weight: 500;
        border-radius: 2rem;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }

    .badge-primary {
        background-color: #dbeafe;
        color: #1e40af;
    }

    .badge-success {
        background-color: #d1fae5;
        color: #065f46;
    }

    .badge-secondary {
        background-color: #e2e8f0;
        color: #475569;
    }

    .badge-info {
        background-color: #dbeafe;
        color: #1e40af;
    }

    /* Alert Styles */
    .alert {
        border: none;
        border-radius: 0.75rem;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        animation: fadeInDown 0.5s ease-out;
    }

    .alert-success {
        background-color: #ecfdf5;
        color: #065f46;
    }

    .alert-danger {
        background-color: #fef2f2;
        color: #991b1b;
    }

    /* Admin Icon */
    .admin-avatar {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background-color: #eff6ff;
        color: #2563eb;
        transition: all 0.3s ease;
        font-weight: bold;
        margin-right: 1rem;
    }

    .table tr:hover .admin-avatar {
        background-color: #2563eb;
        color: white;
    }

    /* Animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

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

    /* Section Header */
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
    }

    .page-title {
        font-size: 1.875rem;
        font-weight: 700;
        color: white;
        margin-bottom: 0.5rem;
    }

    /* Role Label */
    .role-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .role-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
    }

    /* Status indicator */
    .status-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }

    .status-dot.active {
        background-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
    }

    .status-dot.inactive {
        background-color: #94a3b8;
        box-shadow: 0 0 0 3px rgba(148, 163, 184, 0.2);
    }

    /* Action Buttons Container */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }

    /* Refresh Button Animation */
    .btn-refresh i {
        transition: transform 0.5s ease;
    }

    .btn-refresh:hover i {
        transform: rotate(180deg);
    }

    /* Last Updated */
    .last-updated {
        display: flex;
        align-items: center;
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.875rem;
    }

    .last-updated i {
        margin-right: 0.5rem;
    }

    /* Username Container */
    .username-container {
        display: flex;
        align-items: center;
    }

    /* Responsive Table */
    .table-responsive {
        overflow-x: auto;
        border-radius: 0.75rem;
    }

    @media (max-width: 768px) {
        .page-header {
            padding: 1.5rem;
        }
        
        .page-title {
            font-size: 1.5rem;
        }
        
        .section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .btn {
            width: 100%;
        }
        
        .action-buttons {
            width: 100%;
            justify-content: space-between;
        }
    }
</style>

<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Manajemen Admin</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Admin</li>
                </ol>
            </nav>
        </div>
        <div class="last-updated">
            <i class="bi bi-clock-history"></i>
            <span>Terakhir diperbarui: <?= date('d M Y H:i') ?></span>
        </div>
    </div>

    <!-- Action Section -->
    <div class="section-header">
        <h2 class="section-title">Daftar Pengguna Admin</h2>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Admin
        </a>
    </div>

    <!-- Alerts -->
    <?php if (isset($_SESSION['success'])) : ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div>
                <strong>Berhasil!</strong> <?= $_SESSION['success']; ?>
            </div>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])) : ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div>
                <strong>Gagal!</strong> <?= $_SESSION['error']; ?>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Main Card -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="card-header-title">
                <i class="bi bi-people-fill"></i>
                <span>Daftar Admin</span>
            </div>
            <button class="btn btn-light btn-sm btn-refresh" onclick="window.location.reload()">
                <i class="bi bi-arrow-clockwise"></i>
                Refresh
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="60">No</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Level</th>
                            <th>Status</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; ?>
                        <?php foreach ($admin_list as $admin): ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td>
                                <div class="username-container">
                                    <div class="admin-avatar">
                                        <?= strtoupper(substr($admin['username'], 0, 1)); ?>
                                    </div>
                                    <span class="fw-medium"><?= htmlspecialchars($admin['username']); ?></span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($admin['nama']); ?></td>
                            <td><?= isset($admin['email']) ? htmlspecialchars($admin['email']) : '-'; ?></td>
                            <td>
                                <div class="role-label">
                                    <?php if (isset($admin['role']) && $admin['role'] == 'superadmin'): ?>
                                        <span class="badge badge-primary">
                                            <i class="bi bi-shield-fill-check role-icon"></i>
                                            Super Admin
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-info">
                                            <i class="bi bi-person-badge role-icon"></i>
                                            Admin
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php if (isset($admin['status']) && $admin['status'] == 1): ?>
                                    <div class="status-indicator">
                                        <span class="status-dot active"></span>
                                        <span>Aktif</span>
                                    </div>
                                <?php else: ?>
                                    <div class="status-indicator">
                                        <span class="status-dot inactive"></span>
                                        <span>Nonaktif</span>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="edit.php?id=<?= $admin['admin_id']; ?>" 
                                       class="btn btn-warning btn-sm" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    
                                    <?php if (isset($admin['admin_id']) && $admin['admin_id'] != $current_admin_id): ?>
                                    <a href="#" 
                                       onclick="confirmDelete('hapus.php?id=<?= $admin['admin_id']; ?>', '<?= htmlspecialchars($admin['nama']); ?>')" 
                                       class="btn btn-danger btn-sm" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    <?php endif; ?>
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
// Function to confirm delete with a nice modal
function confirmDelete(url, nama) {
    if (confirm('Apakah Anda yakin ingin menghapus admin "' + nama + '"?')) {
        window.location.href = url;
    }
}

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.5s ease';
        setTimeout(() => {
            alert.style.display = 'none';
        }, 500);
    });
}, 5000);
</script>

<?php include_once '../includes/footer.php'; ?>