<?php
session_start();
require_once '../config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user = query("SELECT * FROM tb_user WHERE user_id = $user_id")[0];

// Default query untuk semua produk
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

// Inisialisasi $products sebagai array kosong
$products = [];

// Pengecekan apakah ada parameter pencarian yang digunakan
$search_used = isset($_GET['keyword']) || isset($_GET['kategori']) || isset($_GET['merk']) || isset($_GET['min_price']) || isset($_GET['max_price']);

// Hanya jalankan query jika ada parameter pencarian
if ($search_used) {
    // Jika ada parameter pencarian
    if (!empty($searchParams)) {
        // Eksekusi query dengan prepared statement
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
                $products[] = $row;
            }
            
            mysqli_stmt_close($stmt);
        }
    } else {
        // Jika tidak ada parameter khusus, gunakan query biasa
        $products = query($query);
    }
}

// Ambil data kategori dan merk untuk filter
$categories = query("SELECT * FROM tb_kategori");
$brands = query("SELECT * FROM tb_merk");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencarian Produk - WARINGIN-IT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-gradient: linear-gradient(135deg, #4361ee, #3a0ca3);
            --secondary-color: #3a0ca3;
            --accent-color: #4cc9f0;
            --hover-color: #3b82f6;
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

        /* Search Box Enhanced */
        .search-box {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            overflow: hidden;
            background: white;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .search-box:hover {
            box-shadow: var(--card-hover-shadow);
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 12px 15px;
            transition: var(--transition);
            box-shadow: none;
            font-size: 0.95rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
        }

        /* Product Card Enhanced */
        .product-card {
            border-radius: var(--border-radius);
            overflow: hidden;
            transition: var(--transition);
            position: relative;
            z-index: 1;
            background: white;
            height: 100%;
            border: none;
            box-shadow: var(--card-shadow);
            cursor: pointer;
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
            width: 100%;
            height: 200px;
            position: relative;
            overflow: hidden;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-image {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transition: var(--transition);
            transform-origin: center;
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .card-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
            transition: var(--transition);
        }

        .product-card:hover .card-title {
            color: var(--primary-color);
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

        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            background: white;
            transition: var(--transition);
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 600;
        }

        .btn-outline-primary:hover,
        .btn-outline-primary.active {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
            transform: translateY(-3px);
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

        /* Lihat Detail Button */
        .btn-detail {
            margin-top: 0.5rem;
            display: inline-block;
            width: 100%;
            text-align: center;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 600;
            transition: var(--transition);
            text-decoration: none;
        }

        .btn-detail:hover {
            background-color: rgba(67, 97, 238, 0.1);
            transform: translateY(-3px);
            color: var(--primary-color);
        }

        .btn-detail i {
            margin-right: 6px;
            transition: var(--transition);
        }

        .btn-detail:hover i {
            transform: translateX(3px);
        }

        /* Responsive Adjustments */
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
        }

        /* Loading Spinner */
        .spinner-overlay {
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

        .spinner-overlay.show {
            visibility: visible;
            opacity: 1;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid var(--primary-color);
            border-top: 5px solid transparent;
            border-radius: 50%;
            animation: spinner 1s linear infinite;
        }

        @keyframes spinner {
            to {transform: rotate(360deg);}
        }

        /* Empty state */
        .empty-results {
            text-align: center;
            padding: 3rem 0;
        }

        .empty-results i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-results h4 {
            color: #475569;
            margin-bottom: 1rem;
        }

        .empty-results p {
            color: #64748b;
            margin-bottom: 1.5rem;
        }

        /* Description text */
        .description-text {
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            font-size: 0.9rem;
            color: #64748b;
        }

        /* Toast styles */
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast {
            background: white;
            border-radius: 12px;
            padding: 15px 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            animation: toastIn 0.3s ease;
        }

        .toast.success {
            border-left: 4px solid #10b981;
        }

        .toast.success i {
            color: #10b981;
        }

        .toast.danger {
            border-left: 4px solid #ef4444;
        }

        .toast.danger i {
            color: #ef4444;
        }

        .toast i {
            margin-right: 10px;
            font-size: 1.2rem;
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

        .toast.hide {
            animation: toastOut 0.3s ease forwards;
        }

        @keyframes toastOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
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
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="search.php">Pencarian</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">Pesanan Saya</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="wishlist.php">Wishlist</a>
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

    <!-- Loading Spinner -->
    <div class="spinner-overlay" id="spinner">
        <div class="spinner"></div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toast-container"></div>

    <!-- Main Content -->
    <div class="container my-4">
        <h2 class="mb-4 fw-bold">
            <i class="bi bi-search me-2"></i>Pencarian Produk
        </h2>

        <!-- Search Form -->
        <div class="search-box mb-4">
            <form action="" method="get" id="searchForm">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="keyword" class="form-label">Kata Kunci</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="keyword" name="keyword" 
                                       placeholder="Masukkan nama atau spesifikasi laptop..." 
                                       value="<?= htmlspecialchars($keyword); ?>">
                                <button class="btn btn-outline-secondary" type="button" id="clearKeyword">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="kategori" class="form-label">Kategori</label>
                            <select class="form-select" id="kategori" name="kategori">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($categories as $category) : ?>
                                    <option value="<?= $category['kategori_id']; ?>" 
                                            <?= ($kategori == $category['kategori_id']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($category['nama_kategori']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="merk" class="form-label">Merk</label>
                            <select class="form-select" id="merk" name="merk">
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
                </div>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Rentang Harga</label>
                            <div class="row">
                                <div class="col">
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" id="min_price" name="min_price" 
                                               placeholder="Min" value="<?= htmlspecialchars($min_price); ?>">
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" id="max_price" name="max_price" 
                                               placeholder="Max" value="<?= htmlspecialchars($max_price); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="mb-3 w-100">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">
                                    <i class="bi bi-search me-2"></i>Cari
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

        <!-- Search Results -->
        <?php if ($search_used) : ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">Hasil Pencarian</h4>
                <div class="text-muted">
                    Ditemukan <?= count($products); ?> produk
                </div>
            </div>

            <?php if (empty($products)) : ?>
                <div class="empty-results">
                    <i class="bi bi-search"></i>
                    <h4>Tidak ada hasil</h4>
                    <p>Tidak menemukan produk yang sesuai dengan pencarian Anda.</p>
                    <a href="search.php" class="btn btn-primary">
                        <i class="bi bi-arrow-counterclockwise me-2"></i>Reset Pencarian
                    </a>
                </div>
            <?php else : ?>
                <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
                    <?php foreach ($products as $product) : ?>
                        <div class="col">
                            <div class="card h-100 product-card" onclick="goToDetail(<?= $product['barang_id']; ?>)">
                                <div class="position-relative product-image-container">
                                    <img src="../assets/img/barang/<?= htmlspecialchars($product['gambar'] ?: 'no-image.jpg'); ?>" 
                                        class="product-image" 
                                        alt="<?= htmlspecialchars($product['nama_barang']); ?>">
                                    <?php if ($product['stok'] <= 5) : ?>
                                        <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-3">
                                            Stok Terbatas: <?= $product['stok']; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title text-truncate"><?= htmlspecialchars($product['nama_barang']); ?></h5>
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="bi bi-tag me-1"></i><?= htmlspecialchars($product['nama_merk']); ?> | 
                                            <i class="bi bi-laptop me-1"></i><?= htmlspecialchars($product['nama_kategori']); ?>
                                        </small>
                                    </div>
                                    <div class="description-container mb-2">
                                        <div class="description-text">
                                            <?= htmlspecialchars($product['jenis_barang']); ?>
                                        </div>
                                    </div>
                                    <div class="mt-auto">
                                        <h6 class="fw-bold text-primary mb-3">
                                            Rp <?= number_format($product['harga_jual'], 0, ',', '.'); ?>
                                        </h6>
                                        <div class="d-flex gap-2">
                                            <?php if ($product['stok'] > 0) : ?>
                                                <form action="cart.php" method="post" class="w-100" onclick="event.stopPropagation();">
                                                    <input type="hidden" name="barang_id" value="<?= $product['barang_id']; ?>">
                                                    <input type="hidden" name="action" value="add">
                                                    <input type="hidden" name="qty" value="1">
                                                    <button type="submit" class="btn btn-primary w-100">
                                                        <i class="bi bi-cart-plus me-2"></i>Tambah ke Keranjang
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-outline-primary add-to-wishlist" 
                                                        data-id="<?= $product['barang_id']; ?>" onclick="event.stopPropagation();">
                                                    <i class="bi bi-heart"></i>
                                                </button>
                                            <?php else : ?>
                                                <button class="btn btn-secondary w-100" disabled onclick="event.stopPropagation();">
                                                    <i class="bi bi-x-circle me-2"></i>Stok Habis
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Added "Lihat Detail" Button -->
                                        <a href="detail_product.php?id=<?= $product['barang_id']; ?>" class="btn-detail mt-2" onclick="event.stopPropagation();">
                                            <i class="bi bi-eye"></i> Lihat Detail
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <div class="text-center py-5">
                <i class="bi bi-search fs-1 text-muted mb-3"></i>
                <h4>Cari Laptop Impian Anda</h4>
                <p class="text-muted">Gunakan form pencarian di atas untuk menemukan laptop yang sesuai dengan kebutuhan Anda.</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form submit - show loading spinner
        const searchForm = document.getElementById('searchForm');
        const spinner = document.getElementById('spinner');
        
        if (searchForm) {
            searchForm.addEventListener('submit', function() {
                spinner.classList.add('show');
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
                window.location.href = 'search.php';
            });
        }
        
        // Add to wishlist functionality
        const wishlistButtons = document.querySelectorAll('.add-to-wishlist');
        
        wishlistButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                // Prevent card click event propagation
                e.stopPropagation();
                
                const productId = this.getAttribute('data-id');
                addToWishlist(productId, button);
            });
        });
        
        function addToWishlist(productId, button) {
            spinner.classList.add('show');
            
            fetch('wishlist_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add&barang_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                spinner.classList.remove('show');
                
                if (data.status === 'success') {
                    showToast(data.message, 'success');
                    button.innerHTML = '<i class="bi bi-heart-fill"></i>';
                    button.classList.add('active');
                } else {
                    showToast(data.message, 'danger');
                }
            })
            .catch(error => {
                spinner.classList.remove('show');
                showToast('Terjadi kesalahan. Silakan coba lagi.', 'danger');
                console.error('Error:', error);
            });
        }
        
        // Toast notification function
        function showToast(message, type) {
            // Create toast container if not exists
            let toastContainer = document.querySelector('.toast-container');
            
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container';
                document.body.appendChild(toastContainer);
            }
            
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            let icon = 'info-circle';
            if (type === 'success') icon = 'check-circle';
            if (type === 'danger') icon = 'exclamation-circle';
            
            toast.innerHTML = `
                <i class="bi bi-${icon}"></i>
                <div>${message}</div>
            `;
            
            toastContainer.appendChild(toast);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                toast.classList.add('hide');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
        }
        
        // Check if products exist in wishlist (optional enhancement)
        function checkWishlistStatus() {
            const wishlistButtons = document.querySelectorAll('.add-to-wishlist');
            
            if (wishlistButtons.length > 0) {
                const productIds = Array.from(wishlistButtons).map(btn => btn.getAttribute('data-id'));
                
                // For each product, check if it's in wishlist
                productIds.forEach(productId => {
                    fetch('wishlist_action.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=check&barang_id=${productId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success' && data.exists) {
                            // Update button appearance if product is in wishlist
                            const button = document.querySelector(`.add-to-wishlist[data-id="${productId}"]`);
                            if (button) {
                                button.innerHTML = '<i class="bi bi-heart-fill"></i>';
                                button.classList.add('active');
                            }
                        }
                    })
                    .catch(error => console.error('Error checking wishlist status:', error));
                });
            }
        }
        
        // Function to go to product detail page
        window.goToDetail = function(productId) {
            window.location.href = `detail_product.php?id=${productId}`;
        };
        
        // Run on page load
        checkWishlistStatus();
    });
    </script>
</body>
</html>