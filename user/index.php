<?php
session_start();
require_once '../config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit;
}

// Query utama untuk laptop dengan filter
$query = "SELECT b.*, k.nama_kategori, m.nama_merk 
          FROM tb_barang b 
          LEFT JOIN tb_kategori k ON b.kategori_id = k.kategori_id 
          LEFT JOIN tb_merk m ON b.merk_id = m.merk_id 
          WHERE b.stok > 0";

// Inisialisasi array untuk menyimpan kondisi pencarian
$conditions = [];
$searchParams = [];

// Ambil parameter pencarian
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$merk = isset($_GET['merk']) ? $_GET['merk'] : '';
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : '';

// Pengecekan apakah ada parameter pencarian yang digunakan
$search_used = !empty($keyword) || !empty($kategori) || !empty($merk) || !empty($min_price) || !empty($max_price);

// Tambahkan kondisi pencarian berdasarkan keyword
if (!empty($keyword)) {
    $conditions[] = "(b.nama_barang LIKE ? OR b.jenis_barang LIKE ?)";
    $searchParams[] = "%{$keyword}%";
    $searchParams[] = "%{$keyword}%";
}

// Filter berdasarkan kategori
if (!empty($kategori)) {
    $conditions[] = "b.kategori_id = ?";
    $searchParams[] = $kategori;
}

// Filter berdasarkan merk
if (!empty($merk)) {
    $conditions[] = "b.merk_id = ?";
    $searchParams[] = $merk;
}

// Filter berdasarkan rentang harga
if (!empty($min_price)) {
    $conditions[] = "b.harga_jual >= ?";
    $searchParams[] = $min_price;
}

if (!empty($max_price)) {
    $conditions[] = "b.harga_jual <= ?";
    $searchParams[] = $max_price;
}

// Gabungkan semua kondisi pencarian
if (!empty($conditions)) {
    $query .= " AND " . implode(" AND ", $conditions);
}

// Tambahkan pengurutan
$query .= " ORDER BY b.nama_barang ASC";

// Eksekusi query
$laptops = [];

if ($search_used && !empty($searchParams)) {
    // Jika ada parameter pencarian dengan prepared statement
    $stmt = mysqli_prepare($conn, $query);
    
    if ($stmt) {
        // Buat string tipe parameter untuk bind_param
        $paramTypes = str_repeat('s', count($searchParams));
        
        // Persiapkan array references untuk bind_param
        $bindParams = array();
        $bindParams[] = &$paramTypes;
        
        // Tambahkan references ke searchParams
        foreach ($searchParams as $key => $value) {
            $bindParams[] = &$searchParams[$key];
        }
        
        // Panggil bind_param dengan referensi
        call_user_func_array(array($stmt, 'bind_param'), $bindParams);
        
        // Eksekusi statement
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        // Ambil hasil query
        while ($row = mysqli_fetch_assoc($result)) {
            $laptops[] = $row;
        }
        
        mysqli_stmt_close($stmt);
    }
} else {
    // Jika tidak ada pencarian atau pencarian kosong, tampilkan semua produk
    $laptops = query($query);
}

$categories = query("SELECT * FROM tb_kategori");
$brands = query("SELECT * FROM tb_merk");
$user_id = $_SESSION['user_id'];
$user = query("SELECT * FROM tb_user WHERE user_id = $user_id")[0];

// Ambil wishlist user untuk cek barang sudah ada di wishlist atau belum
$wishlist_query = "SELECT barang_id FROM tb_wishlist WHERE user_id = $user_id";
$wishlist_items = query($wishlist_query);
$wishlist_array = [];
foreach ($wishlist_items as $item) {
    $wishlist_array[] = $item['barang_id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WARINGIN-IT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        :root {
    --primary-color: #4361ee;
    --primary-gradient: linear-gradient(135deg, #4361ee, #3a0ca3);
    --secondary-color: #3a0ca3;
    --accent-color: #4cc9f0;
    --hover-color: #3b82f6;
    --success-color: #10b981;
    --card-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
    --card-hover-shadow: 0 15px 30px rgba(67, 97, 238, 0.15);
    --border-radius: 16px;
    --transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
}

body {
    background-color: #f8fafc;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    padding-bottom: 30px;
}

/* Navbar Enhanced */
.navbar {
    box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1);
    background: var(--primary-gradient) !important;
    padding: 15px 0;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.navbar-brand {
    font-weight: 700;
    font-size: 1.35rem;
    letter-spacing: -0.5px;
    position: relative;
}

.navbar-brand::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    width: 50%;
    height: 3px;
    background: white;
    border-radius: 5px;
    opacity: 0;
    transform: translateY(5px);
    transition: var(--transition);
}

.navbar-brand:hover::after {
    opacity: 1;
    transform: translateY(0);
    width: 100%;
}

.navbar-nav .nav-link {
    font-weight: 500;
    padding: 8px 16px;
    margin: 0 3px;
    border-radius: 8px;
    transition: var(--transition);
}

.navbar-nav .nav-link:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
}

.navbar-nav .nav-link.active {
    background: rgba(255, 255, 255, 0.2);
    font-weight: 600;
}

/* Badge Enhancement */
.badge {
    font-weight: 600;
    padding: 0.4em 0.75em;
    border-radius: 6px;
    position: relative;
    top: -3px;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
}

/* Dropdown Animation */
.dropdown-menu {
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    animation: slideDown 0.3s ease forwards;
    transform-origin: top center;
    padding: 10px;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px) scale(0.98);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.dropdown-item {
    border-radius: 8px;
    padding: 8px 16px;
    transition: var(--transition);
}

.dropdown-item:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}

.dropdown-item i {
    transition: var(--transition);
}

.dropdown-item:hover i {
    transform: scale(1.2);
}

/* Filter Section Enhanced */
.card {
    border-radius: var(--border-radius);
    border: none;
    box-shadow: var(--card-shadow);
    transition: var(--transition);
    overflow: hidden;
}

.search-box {
    background: white;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.search-box:hover {
    box-shadow: var(--card-hover-shadow);
}

.form-select, .form-control {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 12px 15px;
    transition: var(--transition);
    box-shadow: none;
    font-size: 0.95rem;
}

.form-select:focus, .form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
}

.form-select option:checked {
    background: var(--primary-color);
    color: white;
}

/* Custom Select Style */
.form-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%234361ee' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3E%3C/svg%3E");
    background-position: right 0.75rem center;
    background-size: 12px;
}

/* Button Enhancement */
.btn-primary {
    background: var(--primary-gradient);
    border: none;
    border-radius: 12px;
    padding: 10px 20px;
    font-weight: 600;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
    z-index: 1;
}

.btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #3a0ca3, #4361ee);
    transition: var(--transition);
    z-index: -1;
    opacity: 0;
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 15px rgba(67, 97, 238, 0.25);
}

.btn-primary:hover::before {
    opacity: 1;
}

.btn-primary i {
    transition: var(--transition);
}

.btn-primary:hover i {
    transform: translateX(3px);
}

.btn-outline-secondary {
    border: 2px solid #64748b;
    color: #64748b;
    background: white;
    transition: var(--transition);
    border-radius: 12px;
    padding: 10px 20px;
    font-weight: 600;
}

.btn-outline-secondary:hover {
    background-color: rgba(100, 116, 139, 0.1);
    color: #475569;
    transform: translateY(-3px);
}

/* Product Card Enhanced */
.product-card {
    border-radius: var(--border-radius);
    overflow: hidden;
    transition: var(--transition);
    position: relative;
    z-index: 1;
    background: white;
    height: 100%; /* Ensure equal heights */
    display: flex;
    flex-direction: column;
}

.product-card::before {
    content: '';
    position: absolute;
    top: -5px;
    left: -5px;
    right: -5px;
    bottom: -5px;
    z-index: -1;
    background: var(--primary-gradient);
    border-radius: calc(var(--border-radius) + 5px);
    opacity: 0;
    transition: var(--transition);
    transform: scale(0.98);
}

.product-card:hover {
    transform: translateY(-15px);
    box-shadow: var(--card-hover-shadow);
}

.product-card:hover::before {
    opacity: 0.7;
    transform: scale(1);
}

.product-image-container {
    position: relative;
    overflow: hidden;
    width: 100%;
    height: 200px; /* Fixed height for all images */
    background: #f8f9fa;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: var(--transition);
    transform-origin: center;
    padding: 15px;
}

.product-card:hover .product-image {
    transform: scale(1.05);
}

.card-body {
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    flex-grow: 1; /* Allow body to grow and fill card */
}

.card-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 10px;
    transition: var(--transition);
    height: 2.6rem; /* Fixed height for titles */
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.product-card:hover .card-title {
    color: var(--primary-color);
}

/* Product Tag Icons */
.card-body small i {
    color: var(--primary-color);
    transition: var(--transition);
}

.product-card:hover .card-body small i {
    transform: scale(1.2);
}

/* Price Styling */
.price-wrapper {
    margin-top: auto; /* Push price to bottom */
    margin-bottom: 1rem;
}

.fw-bold.text-primary {
    font-size: 1.25rem;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    display: inline-block;
    padding: 5px 0;
}

/* Description Toggle Animation */
.description-container {
    position: relative;
    margin-bottom: 1rem;
}

.description-text {
    transition: max-height 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    overflow: hidden;
    line-height: 1.6;
    font-size: 0.95rem;
    height: 3em; /* Fixed height for description */
    color: #64748b;
}

.description-text.collapsed {
    max-height: 4.8em;
}

.description-text.expanded {
    max-height: 500px;
}

.btn-link {
    color: var(--primary-color);
    padding: 0;
    font-size: 0.9rem;
    text-decoration: none;
    background: none;
    border: none;
    cursor: pointer;
    display: inline-block;
    margin-top: 5px;
    font-weight: 600;
    transition: var(--transition);
}

.btn-link:hover {
    color: var(--secondary-color);
    transform: translateY(-2px);
}

.btn-link::after {
    content: '';
    display: block;
    width: 0;
    height: 2px;
    background: var(--primary-color);
    transition: width 0.3s;
}

.btn-link:hover::after {
    width: 100%;
}

/* Action Buttons Container */
.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

/* Button group for cart and wishlist */
.button-group {
    display: flex;
    gap: 0.5rem;
}

/* Out of Stock Button */
.btn-secondary {
    background: #94a3b8;
    border: none;
    border-radius: 12px;
    padding: 10px 20px;
    font-weight: 600;
    transition: var(--transition);
}

.btn-secondary:disabled {
    background: #cbd5e1;
    color: #475569;
    opacity: 0.9;
    cursor: not-allowed;
}

/* Wishlist Button */
.btn-outline-primary {
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
    background: white;
    transition: var(--transition);
    min-width: 46px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}

.btn-outline-primary:hover {
    background-color: rgba(67, 97, 238, 0.1);
    color: var(--primary-color);
    transform: translateY(-3px);
}

.btn-outline-primary.active {
    background-color: rgba(67, 97, 238, 0.1);
    color: var(--primary-color);
}

.btn-outline-primary.active i {
    color: #ef4444;
}

/* Cart Button */
.cart-button {
    flex: 1;
}

/* Buy Now Button */
.btn-success {
    background: linear-gradient(135deg, #10b981, #059669);
    border: none;
    border-radius: 12px;
    padding: 10px 20px;
    font-weight: 600;
    transition: var(--transition);
    color: white;
}

.btn-success:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 15px rgba(16, 185, 129, 0.25);
    color: white;
}

/* Limited Stock Badge */
.badge.bg-warning {
    background: linear-gradient(135deg, #fbbf24, #f59e0b) !important;
    color: #7c2d12 !important;
    font-size: 0.75rem;
    padding: 0.5em 0.75em;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(251, 191, 36, 0.3);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
    100% {
        transform: scale(1);
    }
}

/* Overlay for product image */
.product-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.05);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: var(--transition);
    z-index: 2;
}

.product-card:hover .product-overlay {
    opacity: 1;
}

/* Detail button in overlay */
.btn-view-detail {
    background: linear-gradient(135deg, #3b82f6, #4f46e5);
    color: white;
    font-weight: 600;
    padding: 8px 16px;
    border-radius: 10px;
    transition: var(--transition);
    text-decoration: none;
    display: inline-block;
    border: none;
}

.btn-view-detail:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 15px rgba(59, 130, 246, 0.25);
    color: white;
}

.btn-view-detail i {
    margin-right: 6px;
    transition: var(--transition);
}

.btn-view-detail:hover i {
    transform: translateX(3px);
}

/* Animation for Page Load */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.row > .col {
    opacity: 0;
    animation: fadeIn 0.5s ease forwards;
}

/* Staggered animation for cards */
.row > .col:nth-child(1) { animation-delay: 0.1s; }
.row > .col:nth-child(2) { animation-delay: 0.2s; }
.row > .col:nth-child(3) { animation-delay: 0.3s; }
.row > .col:nth-child(4) { animation-delay: 0.4s; }
.row > .col:nth-child(5) { animation-delay: 0.5s; }
.row > .col:nth-child(6) { animation-delay: 0.6s; }
.row > .col:nth-child(7) { animation-delay: 0.7s; }
.row > .col:nth-child(8) { animation-delay: 0.8s; }

/* Toast Notification Styles */
.toast-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
}

.toast {
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    margin-bottom: 10px;
    overflow: hidden;
    opacity: 1;
    transition: all 0.3s ease;
    padding: 15px 20px;
    display: flex;
    align-items: center;
}

@keyframes toastIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.toast {
    animation: toastIn 0.3s ease;
}

.toast.success {
    border-left: 4px solid #10b981;
}

.toast.danger {
    border-left: 4px solid #ef4444;
}

.toast.hide {
    opacity: 0;
    transform: translateX(100%);
}

.toast i {
    margin-right: 10px;
    font-size: 1.2rem;
}

.toast.success i {
    color: #10b981;
}

.toast.danger i {
    color: #ef4444;
}

/* Loading overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    visibility: hidden;
    opacity: 0;
    transition: all 0.3s ease;
}

.loading-overlay.show {
    visibility: visible;
    opacity: 1;
}

/* Search results info */
.search-results-info {
    background: linear-gradient(135deg, #e0f2fe, #b3e5fc);
    border-radius: 12px;
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
    border-left: 4px solid var(--primary-color);
}

.search-results-info h5 {
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.search-results-info p {
    color: #0277bd;
    margin-bottom: 0;
    font-size: 0.95rem;
}

/* Clear search button */
.btn-clear-search {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    border: none;
    color: white;
    border-radius: 8px;
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    transition: var(--transition);
}

.btn-clear-search:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(245, 158, 11, 0.3);
    color: white;
}

/* Empty state for search */
.empty-search-state {
    text-align: center;
    padding: 3rem 0;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
}

.empty-search-state i {
    font-size: 4rem;
    color: #cbd5e1;
    margin-bottom: 1rem;
}

.empty-search-state h4 {
    color: #475569;
    margin-bottom: 1rem;
}

.empty-search-state p {
    color: #64748b;
    margin-bottom: 1.5rem;
}

/* Media Queries for Better Responsiveness */
@media (max-width: 767.98px) {
    .product-image-container {
        height: 180px;
    }
    
    .card-title {
        font-size: 1.1rem;
    }
    
    .navbar {
        padding: 10px 0;
    }
    
    .fw-bold.text-primary {
        font-size: 1.1rem;
    }
    
    .search-box {
        padding: 1rem;
    }
}

@media (max-width: 575.98px) {
    .product-card:hover {
        transform: translateY(-10px);
    }
    
    .badge.bg-warning {
        font-size: 0.7rem;
        padding: 0.4em 0.6em;
    }
}
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loading-overlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-laptop me-2"></i>WARINGIN-IT
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">Pesanan Saya</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="wishlist.php">
                            <i class="bi bi-heart me-1"></i>Wishlist
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="custom_order.php">
                            <i class="bi bi-tools me-1"></i>Custom Order
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item me-3">
                        <a class="nav-link" href="cart.php">
                            <i class="bi bi-cart-fill me-1"></i>Keranjang
                            <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) : ?>
                                <span class="badge bg-danger rounded-pill"><?= count($_SESSION['cart']); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($user['nama']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="../auth/logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Toast Container untuk notifikasi -->
    <div class="toast-container" id="toast-container"></div>

    <!-- Main Content -->
    <div class="container my-4">
        <!-- Enhanced Search Box -->
        <div class="search-box card mb-4">
            <div class="card-body">
                <form action="" method="get" id="searchForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="keyword" class="form-label fw-semibold">
                                <i class="bi bi-search me-2"></i>Kata Kunci
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="keyword" name="keyword" 
                                       placeholder="Cari laptop berdasarkan nama atau spesifikasi..." 
                                       value="<?= htmlspecialchars($keyword); ?>">
                                <button class="btn btn-outline-secondary" type="button" id="clearKeyword">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="kategori_filter" class="form-label fw-semibold">
                                <i class="bi bi-laptop me-2"></i>Kategori
                            </label>
                            <select name="kategori" id="kategori_filter" class="form-select">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($categories as $category) : ?>
                                    <option value="<?= $category['kategori_id']; ?>" 
                                            <?= ($kategori == $category['kategori_id']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($category['nama_kategori']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="merk_filter" class="form-label fw-semibold">
                                <i class="bi bi-tag me-2"></i>Merk
                            </label>
                            <select name="merk" id="merk_filter" class="form-select">
                                <option value="">Semua Merk</option>
                                <?php foreach ($brands as $brand) : ?>
                                    <option value="<?= $brand['merk_id']; ?>"
                                            <?= ($merk == $brand['merk_id']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($brand['nama_merk']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-currency-dollar me-2"></i>Rentang Harga
                            </label>
                            <div class="row">
                                <div class="col">
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" id="min_price" name="min_price" 
                                               placeholder="Harga Minimum" value="<?= htmlspecialchars($min_price); ?>">
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" id="max_price" name="max_price" 
                                               placeholder="Harga Maksimum" value="<?= htmlspecialchars($max_price); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="w-100">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-grow-1">
                                        <i class="bi bi-search me-2"></i>Cari Produk
                                    </button>
                                    <button type="button" id="resetButton" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-counterclockwise me-2"></i>Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Search Results Info -->
        <?php if ($search_used) : ?>
            <div class="search-results-info">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="mb-2">
                            <i class="bi bi-funnel me-2"></i>Hasil Pencarian
                        </h5>
                        <p class="mb-2">
                            Menampilkan <?= count($laptops); ?> produk
                            <?php if (!empty($keyword)) : ?>
                                untuk "<strong><?= htmlspecialchars($keyword); ?></strong>"
                            <?php endif; ?>
                            <?php 
                            $filters = [];
                            if (!empty($kategori)) {
                                $cat_name = '';
                                foreach ($categories as $cat) {
                                    if ($cat['kategori_id'] == $kategori) {
                                        $cat_name = $cat['nama_kategori'];
                                        break;
                                    }
                                }
                                $filters[] = "Kategori: " . $cat_name;
                            }
                            if (!empty($merk)) {
                                $brand_name = '';
                                foreach ($brands as $brand) {
                                    if ($brand['merk_id'] == $merk) {
                                        $brand_name = $brand['nama_merk'];
                                        break;
                                    }
                                }
                                $filters[] = "Merk: " . $brand_name;
                            }
                            if (!empty($min_price)) {
                                $filters[] = "Min: Rp " . number_format($min_price, 0, ',', '.');
                            }
                            if (!empty($max_price)) {
                                $filters[] = "Max: Rp " . number_format($max_price, 0, ',', '.');
                            }
                            if (!empty($filters)) {
                                echo " dengan filter: " . implode(", ", $filters);
                            }
                            ?>
                        </p>
                    </div>
                    <a href="index.php" class="btn btn-clear-search btn-sm">
                        <i class="bi bi-x me-1"></i>Hapus Filter
                    </a>
                </div>
            </div>
        <?php else : ?>
            <div class="text-center py-3 mb-4">
                <h4 class="fw-bold text-primary">
                    <i class="bi bi-laptop me-2"></i>Semua Produk Laptop
                </h4>
                <p class="text-muted mb-0">Temukan laptop terbaik sesuai kebutuhan Anda</p>
            </div>
        <?php endif; ?>

        <!-- Products Grid -->
        <?php if (empty($laptops) && $search_used) : ?>
            <div class="empty-search-state">
                <i class="bi bi-search"></i>
                <h4>Tidak ada hasil yang ditemukan</h4>
                <p>Tidak menemukan produk yang sesuai dengan kriteria pencarian Anda.<br>
                   Coba ubah kata kunci atau filter pencarian.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="index.php" class="btn btn-primary">
                        <i class="bi bi-arrow-counterclockwise me-2"></i>Reset Pencarian
                    </a>
                    <a href="search.php" class="btn btn-outline-primary">
                        <i class="bi bi-search me-2"></i>Pencarian Lanjutan
                    </a>
                </div>
            </div>
        <?php else : ?>
            <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
                <?php foreach ($laptops as $laptop) : ?>
                <div class="col">
                    <div class="card product-card">
                        <div class="product-image-container">
                            <img src="../assets/img/barang/<?= htmlspecialchars($laptop['gambar'] ?: 'no-image.jpg'); ?>" 
                                 class="product-image" 
                                 alt="<?= htmlspecialchars($laptop['nama_barang']); ?>">
                            <?php if ($laptop['stok'] <= 5) : ?>
                                <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-3">
                                    Stok Terbatas: <?= $laptop['stok']; ?>
                                </span>
                            <?php endif; ?>
                            <!-- Overlay dengan tombol Detail -->
                            <div class="product-overlay">
                                <a href="detail_product.php?id=<?= $laptop['barang_id']; ?>" class="btn btn-view-detail">
                                    <i class="bi bi-eye"></i>Lihat Detail
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <a href="detail_product.php?id=<?= $laptop['barang_id']; ?>" class="text-decoration-none">
                                <h5 class="card-title"><?= htmlspecialchars($laptop['nama_barang']); ?></h5>
                            </a>
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="bi bi-tag me-1"></i><?= htmlspecialchars($laptop['nama_merk']); ?> | 
                                    <i class="bi bi-laptop me-1"></i><?= htmlspecialchars($laptop['nama_kategori']); ?>
                                </small>
                            </div>
                            <div class="description-container">
                                <div class="description-text collapsed" id="desc-<?= $laptop['barang_id']; ?>">
                                    <?= htmlspecialchars($laptop['jenis_barang']); ?>
                                </div>
                            </div>
                            
                            <div class="price-wrapper">
                                <h6 class="fw-bold text-primary">
                                    Rp <?= number_format($laptop['harga_jual'], 0, ',', '.'); ?>
                                </h6>
                            </div>
                            
                            <?php if ($laptop['stok'] > 0) : ?>
                                <div class="action-buttons">
                                    <div class="button-group">
                                        <form action="cart.php" method="post" class="cart-button">
                                            <input type="hidden" name="barang_id" value="<?= $laptop['barang_id']; ?>">
                                            <input type="hidden" name="action" value="add">
                                            <input type="hidden" name="qty" value="1">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="bi bi-cart-plus me-2"></i>Keranjang
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-outline-primary add-to-wishlist" 
                                                data-id="<?= $laptop['barang_id']; ?>"
                                                <?= in_array($laptop['barang_id'], $wishlist_array) ? 'data-in-wishlist="true"' : ''; ?>>
                                            <i class="bi <?= in_array($laptop['barang_id'], $wishlist_array) ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
                                        </button>
                                    </div>
                                    <a href="detail_product.php?id=<?= $laptop['barang_id']; ?>" class="btn btn-success w-100">
                                        <i class="bi bi-lightning-fill me-2"></i>Beli Sekarang
                                    </a>
                                </div>
                            <?php else : ?>
                                <button class="btn btn-secondary w-100" disabled>
                                    <i class="bi bi-x-circle me-2"></i>Stok Habis
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function untuk menampilkan toast notification
        function showToast(type, message) {
            const toastContainer = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const icon = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';
            
            toast.innerHTML = `
                <i class="bi ${icon}"></i>
                <span>${message}</span>
            `;
            
            toastContainer.appendChild(toast);
            
            // Hapus toast setelah 3 detik
            setTimeout(() => {
                toast.classList.add('hide');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Form submit - show loading spinner
            const searchForm = document.getElementById('searchForm');
            const loadingOverlay = document.getElementById('loading-overlay');
            
            if (searchForm) {
                searchForm.addEventListener('submit', function() {
                    loadingOverlay.classList.add('show');
                });
            }

            // Clear keyword button
            const clearKeywordBtn = document.getElementById('clearKeyword');
            const keywordInput = document.getElementById('keyword');
            
            if (clearKeywordBtn && keywordInput) {
                clearKeywordBtn.addEventListener('click', function() {
                    keywordInput.value = '';
                    keywordInput.focus();
                });
            }
            
            // Reset button
            const resetButton = document.getElementById('resetButton');
            
            if (resetButton) {
                resetButton.addEventListener('click', function() {
                    // Clear all form fields
                    document.getElementById('keyword').value = '';
                    document.getElementById('kategori_filter').value = '';
                    document.getElementById('merk_filter').value = '';
                    document.getElementById('min_price').value = '';
                    document.getElementById('max_price').value = '';
                    
                    // Redirect to index without parameters
                    window.location.href = 'index.php';
                });
            }

            // Tambahkan event listener untuk tombol wishlist
            const wishlistButtons = document.querySelectorAll('.add-to-wishlist');
            
            wishlistButtons.forEach(button => {
                // Cek jika barang sudah ada di wishlist
                if (button.getAttribute('data-in-wishlist') === 'true') {
                    button.classList.add('active');
                }
                
                button.addEventListener('click', function() {
                    const productId = this.getAttribute('data-id');
                    const isInWishlist = this.classList.contains('active');
                    const action = isInWishlist ? 'remove' : 'add';
                    
                    // Tampilkan loading
                    loadingOverlay.classList.add('show');
                    
                    // Simpan referensi ke button untuk digunakan di dalam fetch
                    const buttonEl = this;
                    
                    // Kirim request AJAX
                    fetch('wishlist_action.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=${action}&barang_id=${productId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Sembunyikan loading overlay
                        loadingOverlay.classList.remove('show');
                        
                        if (data.status === 'success') {
                            // Tampilkan pesan sukses
                            showToast('success', data.message);
                            
                            // Update tampilan tombol wishlist
                            if (action === 'add') {
                                buttonEl.classList.add('active');
                                buttonEl.querySelector('i').classList.remove('bi-heart');
                                buttonEl.querySelector('i').classList.add('bi-heart-fill');
                                buttonEl.setAttribute('data-in-wishlist', 'true');
                            } else {
                                buttonEl.classList.remove('active');
                                buttonEl.querySelector('i').classList.remove('bi-heart-fill');
                                buttonEl.querySelector('i').classList.add('bi-heart');
                                buttonEl.setAttribute('data-in-wishlist', 'false');
                            }
                        } else {
                            // Tampilkan pesan error
                            showToast('danger', data.message);
                        }
                    })
                    .catch(error => {
                        // Sembunyikan loading overlay
                        loadingOverlay.classList.remove('show');
                        
                        // Tampilkan pesan error
                        showToast('danger', 'Terjadi kesalahan. Silakan coba lagi.');
                        console.error('Error:', error);
                    });
                });
            });

            // Auto-hide loading overlay after page load
            window.addEventListener('load', function() {
                loadingOverlay.classList.remove('show');
            });

            // Add smooth scrolling for search results
            <?php if ($search_used) : ?>
            document.querySelector('.search-results-info').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
            <?php endif; ?>
        });
    </script>
</body>
</html>