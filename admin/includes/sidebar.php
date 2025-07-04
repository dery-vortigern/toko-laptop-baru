<?php
// Get current page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="sidebar-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= $current_page === 'index.php' ? 'active' : ''; ?>" href="../index.php">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($current_page, 'barang') !== false ? 'active' : ''; ?>" href="../barang/index.php">
                    <i class="bi bi-laptop"></i>
                    Produk
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($current_page, 'kategori') !== false ? 'active' : ''; ?>" href="../kategori/index.php">
                    <i class="bi bi-tags"></i>
                    Kategori
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($current_page, 'merk') !== false ? 'active' : ''; ?>" href="../merk/index.php">
                    <i class="bi bi-bookmark"></i>
                    Merk
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($current_page, 'supplier') !== false ? 'active' : ''; ?>" href="../supplier/index.php">
                    <i class="bi bi-truck"></i>
                    Supplier
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($current_page, 'penjualan') !== false ? 'active' : ''; ?>" href="../penjualan/index.php">
                    <i class="bi bi-cart"></i>
                    Penjualan
                </a>
            </li>
            <li class="nav-item">
    <a class="nav-link <?= strpos($current_page, 'custom_order') !== false ? 'active' : ''; ?>" href="../custom_order/index.php">
        <i class="bi bi-tools"></i>
        Custom Order
    </a>
</li>
        </ul>

        <!-- Divider -->
        <hr class="my-3">

        <!-- Admin Profile -->
        <div class="small text-muted">Logged in as:</div>
<div class="fw-bold">
    <?= $admin['nama']; ?> 
    <?php if ($admin['role'] == 'superadmin'): ?>
        <span class="badge bg-danger">Super Admin</span>
    <?php else: ?>
        <span class="badge bg-primary">Admin</span>
    <?php endif; ?>
</div>
</nav>