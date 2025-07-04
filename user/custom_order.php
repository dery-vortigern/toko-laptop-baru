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

// Cek apakah tabel custom_orders sudah ada
$checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'tb_custom_orders'");
if (mysqli_num_rows($checkTable) == 0) {
    // Buat tabel custom_orders jika belum ada
    $createTable = "CREATE TABLE tb_custom_orders (
        order_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        ram VARCHAR(50) NOT NULL,
        storage VARCHAR(100) NOT NULL,
        processor VARCHAR(100) NOT NULL,
        vga VARCHAR(100) NOT NULL,
        screen_size VARCHAR(50) NOT NULL,
        screen_type VARCHAR(100) NOT NULL,
        operating_system VARCHAR(100) NOT NULL,
        additional_specs TEXT,
        budget INT(11) NOT NULL,
        down_payment INT(11) NOT NULL DEFAULT 0,
        status ENUM('pending', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
        admin_notes TEXT,
        admin_id INT(11),
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP
    )";
    
    mysqli_query($conn, $createTable);
}

// Ambil data RAM untuk dropdown
$ram_options = ['4GB', '8GB', '16GB', '32GB', '64GB'];

// Ambil data storage untuk dropdown
$storage_types = ['SSD', 'HDD', 'SSD + HDD', 'NVMe SSD'];
$storage_capacities = ['128GB', '256GB', '512GB', '1TB', '2TB', '4TB'];

// Ambil data processor untuk dropdown
$processor_brands = ['Intel', 'AMD'];
$intel_processors = [
    'Intel Core i3', 
    'Intel Core i5', 
    'Intel Core i7',
    'Intel Core i9',
    'Intel Celeron',
    'Intel Pentium'
];
$amd_processors = [
    'AMD Ryzen 3',
    'AMD Ryzen 5',
    'AMD Ryzen 7',
    'AMD Ryzen 9',
    'AMD Athlon'
];

// Ambil data VGA untuk dropdown
$vga_options = [
    'Integrated/Onboard',
    'NVIDIA GeForce GTX 1650',
    'NVIDIA GeForce GTX 1660',
    'NVIDIA GeForce RTX 3050',
    'NVIDIA GeForce RTX 3060',
    'NVIDIA GeForce RTX 3070',
    'NVIDIA GeForce RTX 3080',
    'NVIDIA GeForce RTX 4050',
    'NVIDIA GeForce RTX 4060',
    'NVIDIA GeForce RTX 4070',
    'NVIDIA GeForce RTX 4080',
    'NVIDIA GeForce RTX 4090',
    'AMD Radeon RX 5500M',
    'AMD Radeon RX 6600',
    'AMD Radeon RX 6700',
    'AMD Radeon RX 6800',
    'AMD Radeon RX 7600',
    'AMD Radeon RX 7700',
    'AMD Radeon RX 7800',
    'Intel Arc A370M',
    'Other'
];

// Ambil data screen sizes untuk dropdown
$screen_sizes = ['13.3"', '14"', '15.6"', '16"', '17.3"'];

// Ambil data screen types untuk dropdown
$screen_types = [
    'IPS Panel',
    'OLED',
    'LCD',
    'TN Panel',
    'Mini-LED',
    'Standard (Non-Touch)',
    'Touchscreen'
];

// Ambil data OS untuk dropdown
$os_options = [
    'Windows 11 Home',
    'Windows 11 Pro',
    'Windows 10 Home',
    'Windows 10 Pro',
    'ChromeOS',
    'Linux',
    'FreeDOS',
    'Without OS'
];

// Proses form submit
if (isset($_POST['submit_order'])) {
    $ram = $_POST['ram'];
    $storage_type = $_POST['storage_type'];
    $storage_capacity = $_POST['storage_capacity'];
    $storage = $storage_type . ' ' . $storage_capacity;
    $processor_brand = $_POST['processor_brand'];
    $processor_type = $_POST['processor_type'];
    $processor = $processor_brand . ' ' . $processor_type;
    $vga = $_POST['vga'];
    
    if ($vga === 'Other') {
        $vga = $_POST['other_vga'];
    }
    
    $screen_size = $_POST['screen_size'];
    $screen_type = $_POST['screen_type'];
    $os = $_POST['os'];
    $additional_specs = $_POST['additional_specs'];
    $budget = (int)str_replace(['Rp', '.', ','], '', $_POST['budget']);
    $down_payment = (int)str_replace(['Rp', '.', ','], '', $_POST['down_payment']);
    
    // Validasi data
    $errors = [];
    
    if (empty($ram)) {
        $errors[] = 'RAM tidak boleh kosong!';
    }
    
    if (empty($storage)) {
        $errors[] = 'Storage tidak boleh kosong!';
    }
    
    if (empty($processor)) {
        $errors[] = 'Processor tidak boleh kosong!';
    }
    
    if (empty($vga)) {
        $errors[] = 'VGA tidak boleh kosong!';
    }
    
    if (empty($screen_size)) {
        $errors[] = 'Ukuran layar tidak boleh kosong!';
    }
    
    if (empty($screen_type)) {
        $errors[] = 'Tipe layar tidak boleh kosong!';
    }
    
    if (empty($os)) {
        $errors[] = 'Sistem operasi tidak boleh kosong!';
    }
    
    if ($budget <= 0) {
        $errors[] = 'Budget tidak valid!';
    }
    
    if ($down_payment <= 0) {
        $errors[] = 'Down Payment tidak valid!';
    }
    
    if ($down_payment >= $budget) {
        $errors[] = 'Down Payment tidak boleh lebih besar atau sama dengan budget!';
    }
    
    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        $query = "INSERT INTO tb_custom_orders 
                 (user_id, ram, storage, processor, vga, screen_size, screen_type, operating_system, additional_specs, budget, down_payment) 
                 VALUES 
                 (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "issssssssii", $user_id, $ram, $storage, $processor, $vga, $screen_size, $screen_type, $os, $additional_specs, $budget, $down_payment);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Pesanan kustom berhasil dikirim! Admin kami akan menghubungi Anda segera.";
            header("Location: custom_order_status.php");
            exit;
        } else {
            $error = "Terjadi kesalahan saat mengirim pesanan: " . mysqli_error($conn);
        }
    } else {
        $error = implode('<br>', $errors);
    }
}

// Ambil custom orders yang sudah ada
$history_query = "SELECT * FROM tb_custom_orders WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5";
$recent_orders = query($history_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Order - WARINGIN-IT</title>
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
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            --light-color: #f8fafc;
            --card-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
            --card-hover-shadow: 0 15px 30px rgba(67, 97, 238, 0.15);
            --border-radius: 16px;
            --transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        body {
            background-color: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding-bottom: 30px;
            color: #334155;
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

        /* Title Section */
        h2.mb-4.fw-bold {
            color: #1e293b;
            font-size: 1.75rem;
            letter-spacing: -0.5px;
            margin-bottom: 1.5rem !important;
            padding-bottom: 0.75rem;
            position: relative;
        }

        h2.mb-4.fw-bold::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--primary-gradient);
            border-radius: 2px;
        }

        h2.mb-4.fw-bold i {
            color: var(--primary-color);
            margin-right: 0.5rem;
        }

        /* Card Enhanced */
        .card {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            overflow: hidden;
            position: relative;
            z-index: 1;
            background: white;
            margin-bottom: 2rem;
        }

        .card::before {
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

        .card:hover {
            transform: translateY(-10px);
            box-shadow: var(--card-hover-shadow);
        }

        .card:hover::before {
            opacity: 0.3;
            transform: scale(1);
        }

        .card-body {
            padding: 1.75rem;
        }

        /* Form Styles */
        .form-label {
            color: #475569;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-select, 
        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: var(--transition);
            color: #1e293b;
        }

        .form-select:focus, 
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
        }

        .form-select option:checked {
            background-color: var(--primary-color);
            color: white;
        }

        /* Alert Styling */
        .alert {
            border-radius: 16px;
            border: none;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
            animation: fadeIn 0.5s ease;
            display: flex;
            align-items: center;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            color: #b91c1c;
        }

        .alert i {
            font-size: 1.2rem;
            margin-right: 0.75rem;
        }

        .btn-close {
            margin-left: auto;
            opacity: 0.8;
            transition: var(--transition);
        }

        .btn-close:hover {
            opacity: 1;
            transform: rotate(90deg);
        }

        /* Section Styling */
        .form-section {
            position: relative;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px dashed #e2e8f0;
        }

        .form-section-title {
            display: flex;
            align-items: center;
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .form-section-title i {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }

        /* Stepper Style */
        .stepper-wrapper {
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            position: relative;
        }

        .stepper-line {
            position: absolute;
            top: 1.25rem;
            left: 0;
            right: 0;
            height: 2px;
            background: #e2e8f0;
            z-index: 1;
        }

        .stepper-line-progress {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            background: var(--primary-gradient);
            transition: var(--transition);
            width: 0%;
        }

        .stepper-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            user-select: none;
            cursor: pointer;
            transition: var(--transition);
            flex: 1;
            max-width: 33.33%;
        }

        .stepper-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            border: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #64748b;
            transition: var(--transition);
            position: relative;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .stepper-text {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 500;
            text-align: center;
            transition: var(--transition);
        }

        .stepper-item.active .stepper-circle {
            background: var(--primary-gradient);
            border-color: var(--primary-color);
            color: white;
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.25);
        }

        .stepper-item.active .stepper-text {
            color: var(--primary-color);
            font-weight: 600;
        }

        .stepper-item.completed .stepper-circle {
            background: var(--success-color);
            border-color: var(--success-color);
            color: white;
        }

        .stepper-item.completed .stepper-circle::after {
            content: '\F26A';
            font-family: "bootstrap-icons";
            font-size: 1.25rem;
        }

        /* Button Styles */
        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            padding: 0.9rem 1.5rem;
            font-weight: 600;
            transition: var(--transition);
            color: white;
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
            color: white;
        }

        .btn-primary:hover::before {
            opacity: 1;
        }

        .btn-secondary {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            color: #475569;
            border-radius: 12px;
            padding: 0.75rem 1.25rem;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-secondary:hover {
            background: #f1f5f9;
            color: #334155;
            transform: translateY(-3px);
        }

        .btn i {
            transition: var(--transition);
        }

        .btn-primary:hover i.bi-arrow-right {
            transform: translateX(3px);
        }

        .btn-secondary:hover i.bi-arrow-left {
            transform: translateX(-3px);
        }

        /* Input Group Style */
        .input-group-text {
            border: 2px solid #e2e8f0;
            border-right: none;
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            border-radius: 12px 0 0 12px;
            padding: 0.75rem 1rem;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
        }

        /* Custom Tooltip */
        .tooltip-icon {
            color: #64748b;
            cursor: pointer;
            font-size: 0.9rem;
            margin-left: 0.5rem;
            transition: var(--transition);
        }

        .tooltip-icon:hover {
            color: var(--primary-color);
        }

        /* Animation */
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

        /* History Card Styles */
        .history-item {
            border-left: 3px solid var(--primary-color);
            padding: 1rem;
            margin-bottom: 1rem;
            background: #f8fafc;
            border-radius: 0 12px 12px 0;
            transition: var(--transition);
        }

        .history-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .history-item h6 {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .history-item p {
            margin-bottom: 0.25rem;
            color: #475569;
            font-size: 0.9rem;
        }

        .history-item p:last-child {
            margin-bottom: 0;
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
            border-radius: 6px;
        }

        .status-pending {
            background-color: #fff7ed;
            color: #9a3412;
        }

        .status-processing {
            background-color: #eff6ff;
            color: #1e40af;
        }

        .status-completed {
            background-color: #ecfdf5;
            color: #065f46;
        }

        .status-cancelled {
            background-color: #fef2f2;
            color: #b91c1c;
        }

        /* Budget Slider */
        .range-slider {
            position: relative;
            margin-bottom: 1rem;
        }

        .range-slider-value {
            display: flex;
            justify-content: space-between;
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: #64748b;
        }

        /* Help Info Box */
        .info-box {
            background: #eff6ff;
            border-left: 3px solid var(--info-color);
            padding: 1rem;
            border-radius: 0 12px 12px 0;
            margin-bottom: 1rem;
        }

        .info-box h6 {
            color: #1e40af;
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .info-box h6 i {
            margin-right: 0.5rem;
        }

        .info-box p {
            color: #334155;
            margin-bottom: 0;
            font-size: 0.9rem;
        }

        /* Bank Account Info Box */
        .bank-info-box {
            background: #f0fdf4;
            border-left: 3px solid var(--success-color);
            padding: 1rem;
            border-radius: 0 12px 12px 0;
            margin-top: 0.5rem;
        }

        .bank-info-box h6 {
            color: #065f46;
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .bank-info-box h6 i {
            margin-right: 0.5rem;
        }

        .bank-info-box p {
            color: #166534;
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }

        .bank-info-box p:last-child {
            margin-bottom: 0;
        }

        .bank-account {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #065f46;
        }

        /* Step Content Animation */
        .step-content {
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .step-content.active {
            display: block;
        }

        /* Responsive Adjustments */
        @media (max-width: 767.98px) {
            .stepper-text {
                font-size: 0.75rem;
            }
            
            .card-body {
                padding: 1.25rem;
            }
            
            .form-select, 
            .form-control,
            .input-group-text {
                padding: 0.6rem 0.75rem;
                font-size: 0.9rem;
            }
            
            .btn {
                padding: 0.6rem 1rem;
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
                        <a class="nav-link" href="search.php">Pencarian</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">Pesanan Saya</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="custom_order.php">Custom Order</a>
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

    <div class="container my-4">
        <h2 class="mb-4 fw-bold">
            <i class="bi bi-tools me-2"></i>Custom Order Laptop
        </h2>

        <?php if (isset($error)) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i><?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="info-box mb-4">
                            <h6><i class="bi bi-info-circle"></i>Tentang Custom Order</h6>
                            <p>Dengan fitur ini, Anda dapat memesan laptop sesuai dengan kebutuhan spesifik Anda. Cukup isi detail spesifikasi yang diinginkan, dan tim kami akan mencarikan laptop yang sesuai dengan kebutuhan Anda.</p>
                        </div>

                        <!-- Stepper Navigation -->
                        <div class="stepper-wrapper">
                            <div class="stepper-line">
                                <div class="stepper-line-progress" id="stepperProgress"></div>
                            </div>
                            <div class="stepper-item active" data-step="1">
                                <div class="stepper-circle">1</div>
                                <div class="stepper-text">Spesifikasi Dasar</div>
                            </div>
                            <div class="stepper-item" data-step="2">
                                <div class="stepper-circle">2</div>
                                <div class="stepper-text">Spesifikasi Lanjutan</div>
                            </div>
                            <div class="stepper-item" data-step="3">
                                <div class="stepper-circle">3</div>
                                <div class="stepper-text">Konfirmasi</div>
                            </div>
                        </div>

                        <form action="" method="post" id="customOrderForm">
                            <!-- Step 1: Basic Specifications -->
                            <div class="step-content active" id="step1">
                                <div class="form-section">
                                    <div class="form-section-title">
                                        <i class="bi bi-cpu"></i>Spesifikasi Utama
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="processor_brand" class="form-label">Merk Processor</label>
                                        <select class="form-select" id="processor_brand" name="processor_brand" required>
                                            <option value="">Pilih Merk</option>
                                            <?php foreach ($processor_brands as $brand): ?>
                                                <option value="<?= $brand ?>"><?= $brand ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="processor_type" class="form-label">Tipe Processor</label>
                                        <select class="form-select" id="processor_type" name="processor_type" required>
                                            <option value="">Pilih Processor</option>
                                            <!-- Akan diisi dengan JavaScript berdasarkan pilihan merk -->
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="ram" class="form-label">RAM</label>
                                        <select class="form-select" id="ram" name="ram" required>
                                            <option value="">Pilih RAM</option>
                                            <?php foreach ($ram_options as $ram): ?>
                                                <option value="<?= $ram ?>"><?= $ram ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="storage_type" class="form-label">Tipe Penyimpanan</label>
                                        <select class="form-select" id="storage_type" name="storage_type" required>
                                            <option value="">Pilih Tipe Penyimpanan</option>
                                            <?php foreach ($storage_types as $type): ?>
                                                <option value="<?= $type ?>"><?= $type ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="storage_capacity" class="form-label">Kapasitas Penyimpanan</label>
                                        <select class="form-select" id="storage_capacity" name="storage_capacity" required>
                                            <option value="">Pilih Kapasitas</option>
                                            <?php foreach ($storage_capacities as $capacity): ?>
                                                <option value="<?= $capacity ?>"><?= $capacity ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-2"></i>Kembali
                                    </a>
                                    <button type="button" class="btn btn-primary next-step" data-step="1">
                                        Selanjutnya<i class="bi bi-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Step 2: Advanced Specifications -->
                            <div class="step-content" id="step2">
                                <div class="form-section">
                                    <div class="form-section-title">
                                        <i class="bi bi-gpu-card"></i>Spesifikasi VGA/GPU
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="vga" class="form-label">VGA / Kartu Grafis</label>
                                        <select class="form-select" id="vga" name="vga" required>
                                            <option value="">Pilih VGA</option>
                                            <?php foreach ($vga_options as $vga): ?>
                                                <option value="<?= $vga ?>"><?= $vga ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3" id="otherVgaContainer" style="display: none;">
                                        <label for="other_vga" class="form-label">Spesifikasi VGA/GPU Lainnya</label>
                                        <input type="text" class="form-control" id="other_vga" name="other_vga" placeholder="Misal: NVIDIA RTX 4050 Ti">
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <div class="form-section-title">
                                        <i class="bi bi-display"></i>Spesifikasi Layar
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="screen_size" class="form-label">Ukuran Layar</label>
                                        <select class="form-select" id="screen_size" name="screen_size" required>
                                            <option value="">Pilih Ukuran Layar</option>
                                            <?php foreach ($screen_sizes as $size): ?>
                                                <option value="<?= $size ?>"><?= $size ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="screen_type" class="form-label">Tipe Layar</label>
                                        <select class="form-select" id="screen_type" name="screen_type" required>
                                            <option value="">Pilih Tipe Layar</option>
                                            <?php foreach ($screen_types as $type): ?>
                                                <option value="<?= $type ?>"><?= $type ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <div class="form-section-title">
                                        <i class="bi bi-windows"></i>Sistem Operasi
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="os" class="form-label">Sistem Operasi</label>
                                        <select class="form-select" id="os" name="os" required>
                                            <option value="">Pilih Sistem Operasi</option>
                                            <?php foreach ($os_options as $os_option): ?>
                                                <option value="<?= $os_option ?>"><?= $os_option ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary prev-step" data-step="2">
                                        <i class="bi bi-arrow-left me-2"></i>Sebelumnya
                                    </button>
                                    <button type="button" class="btn btn-primary next-step" data-step="2">
                                        Selanjutnya<i class="bi bi-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Step 3: Confirmation and Additional Information -->
                            <div class="step-content" id="step3">
                                <div class="form-section">
                                    <div class="form-section-title">
                                        <i class="bi bi-info-circle"></i>Informasi Tambahan
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="budget" class="form-label">Budget Total</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control" id="budget" name="budget" placeholder="Masukkan budget total Anda" required>
                                        </div>
                                        <small class="text-muted">Masukkan budget maksimal yang Anda siapkan untuk laptop ini.</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="down_payment" class="form-label">Down Payment (Uang Muka)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control" id="down_payment" name="down_payment" placeholder="Masukkan jumlah uang muka" required>
                                        </div>
                                        <small class="text-muted">Masukkan jumlah uang muka yang akan Anda bayarkan terlebih dahulu. Minimal 30% dari budget total.</small>
                                        
                                        <!-- Bank Account Information -->
                                        <div class="bank-info-box">
                                            <h6><i class="bi bi-bank"></i>Informasi Rekening Bank</h6>
                                            <p><strong>Bank BCA:</strong></p>
                                            <p>No. Rekening: <span class="bank-account">1234567890</span></p>
                                            <p>Atas Nama: <span class="bank-account">WARINGIN IT STORE</span></p>
                                            <hr style="margin: 0.5rem 0; border-color: #10b981;">
                                            <p><strong>Bank Mandiri:</strong></p>
                                            <p>No. Rekening: <span class="bank-account">0987654321</span></p>
                                            <p>Atas Nama: <span class="bank-account">WARINGIN IT STORE</span></p>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="additional_specs" class="form-label">Kebutuhan atau Spesifikasi Tambahan</label>
                                        <textarea class="form-control" id="additional_specs" name="additional_specs" rows="4" placeholder="Jelaskan kebutuhan atau spesifikasi tambahan yang Anda inginkan, misalnya: laptop untuk gaming, desain grafis, aktivitas tertentu, dll."></textarea>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <div class="form-section-title">
                                        <i class="bi bi-check-circle"></i>Konfirmasi Pesanan
                                    </div>
                                    
                                    <div class="info-box">
                                        <h6><i class="bi bi-info-circle"></i>Informasi Penting</h6>
                                        <p>Setelah menekan tombol "Kirim Pesanan", tim kami akan mencarikan laptop yang sesuai dengan spesifikasi yang Anda inginkan. Silakan transfer uang muka ke salah satu rekening yang tertera di atas, kemudian kami akan menghubungi Anda segera setelah menemukan laptop yang cocok.</p>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary prev-step" data-step="3">
                                        <i class="bi bi-arrow-left me-2"></i>Sebelumnya
                                    </button>
                                    <button type="submit" name="submit_order" class="btn btn-primary">
                                        <i class="bi bi-send me-2"></i>Kirim Pesanan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-clock-history me-2"></i>Riwayat Custom Order
                        </h5>
                        
                        <?php if (empty($recent_orders)) : ?>
                            <p class="text-muted text-center py-3">
                                Belum ada riwayat custom order.
                            </p>
                        <?php else : ?>
                            <?php foreach ($recent_orders as $order) : ?>
                                <div class="history-item">
                                    <h6>
                                        Order #<?= $order['order_id']; ?>
                                        <span class="status-badge status-<?= $order['status']; ?>"><?= ucfirst($order['status']); ?></span>
                                    </h6>
                                    <p><strong>Processor:</strong> <?= $order['processor']; ?></p>
                                    <p><strong>RAM:</strong> <?= $order['ram']; ?></p>
                                    <p><strong>Storage:</strong> <?= $order['storage']; ?></p>
                                    <p><strong>Budget:</strong> Rp <?= number_format($order['budget'], 0, ',', '.'); ?></p>
                                    <?php if (isset($order['down_payment']) && $order['down_payment'] > 0) : ?>
                                        <p><strong>Down Payment:</strong> Rp <?= number_format($order['down_payment'], 0, ',', '.'); ?></p>
                                    <?php endif; ?>
                                    <p><strong>Tanggal:</strong> <?= date('d M Y', strtotime($order['created_at'])); ?></p>
                                    <a href="custom_order_detail.php?id=<?= $order['order_id']; ?>" class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="bi bi-eye me-1"></i>Lihat Detail
                                    </a>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="text-center mt-3">
                                <a href="custom_order_status.php" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-list-ul me-1"></i>Lihat Semua Custom Order
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize form elements
            const form = document.getElementById('customOrderForm');
            const stepperItems = document.querySelectorAll('.stepper-item');
            const stepContents = document.querySelectorAll('.step-content');
            const stepperProgress = document.getElementById('stepperProgress');
            const nextButtons = document.querySelectorAll('.next-step');
            const prevButtons = document.querySelectorAll('.prev-step');
            
            // Processor selection logic
            const processorBrandSelect = document.getElementById('processor_brand');
            const processorTypeSelect = document.getElementById('processor_type');
            
            // Define processor options
            const processors = {
                'Intel': <?= json_encode($intel_processors) ?>,
                'AMD': <?= json_encode($amd_processors) ?>
            };
            
            // Update processor types based on selected brand
            processorBrandSelect.addEventListener('change', function() {
                const brand = this.value;
                processorTypeSelect.innerHTML = '<option value="">Pilih Processor</option>';
                
                if (brand && processors[brand]) {
                    processors[brand].forEach(processor => {
                        const option = document.createElement('option');
                        option.value = processor;
                        option.textContent = processor;
                        processorTypeSelect.appendChild(option);
                    });
                }
            });
            
            // VGA "Other" option logic
            const vgaSelect = document.getElementById('vga');
            const otherVgaContainer = document.getElementById('otherVgaContainer');
            const otherVgaInput = document.getElementById('other_vga');
            
            vgaSelect.addEventListener('change', function() {
                if (this.value === 'Other') {
                    otherVgaContainer.style.display = 'block';
                    otherVgaInput.setAttribute('required', 'required');
                } else {
                    otherVgaContainer.style.display = 'none';
                    otherVgaInput.removeAttribute('required');
                }
            });
            
            // Budget and Down Payment formatting
            const budgetInput = document.getElementById('budget');
            const downPaymentInput = document.getElementById('down_payment');
            
            function formatCurrency(input) {
                input.addEventListener('input', function(e) {
                    // Remove all non-digit characters
                    let value = this.value.replace(/\D/g, '');
                    
                    // Format with thousand separators
                    if (value) {
                        value = parseInt(value).toLocaleString('id-ID');
                    }
                    
                    // Update input value
                    this.value = value;
                });
            }
            
            formatCurrency(budgetInput);
            formatCurrency(downPaymentInput);
            
            // Down Payment validation
            function validateDownPayment() {
                const budgetValue = parseInt(budgetInput.value.replace(/\D/g, '')) || 0;
                const downPaymentValue = parseInt(downPaymentInput.value.replace(/\D/g, '')) || 0;
                
                if (budgetValue > 0 && downPaymentValue > 0) {
                    const minDownPayment = budgetValue * 0.3; // 30% minimum
                    
                    if (downPaymentValue < minDownPayment) {
                        downPaymentInput.setCustomValidity(`Down payment minimal 30% dari budget total (Rp ${minDownPayment.toLocaleString('id-ID')})`);
                        return false;
                    } else if (downPaymentValue >= budgetValue) {
                        downPaymentInput.setCustomValidity('Down payment tidak boleh lebih besar atau sama dengan budget total');
                        return false;
                    } else {
                        downPaymentInput.setCustomValidity('');
                        return true;
                    }
                }
                return true;
            }
            
            downPaymentInput.addEventListener('blur', validateDownPayment);
            budgetInput.addEventListener('blur', validateDownPayment);
            
            // Stepper functionality
            function goToStep(step) {
                // Update stepper UI
                stepperItems.forEach(item => {
                    const itemStep = parseInt(item.dataset.step);
                    
                    item.classList.remove('active', 'completed');
                    
                    if (itemStep === step) {
                        item.classList.add('active');
                    } else if (itemStep < step) {
                        item.classList.add('completed');
                    }
                });
                
                // Update content visibility
                stepContents.forEach(content => {
                    content.classList.remove('active');
                });
                
                document.getElementById(`step${step}`).classList.add('active');
                
                // Update progress bar
                const progressPercentage = ((step - 1) / (stepperItems.length - 1)) * 100;
                stepperProgress.style.width = `${progressPercentage}%`;
                
                // Scroll to top of form
                form.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            
            // Next button click
            nextButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const currentStep = parseInt(this.dataset.step);
                    const nextStep = currentStep + 1;
                    
                    // Validate current step fields
                    const currentStepContent = document.getElementById(`step${currentStep}`);
                    const requiredFields = currentStepContent.querySelectorAll('[required]');
                    let isValid = true;
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            isValid = false;
                            field.classList.add('is-invalid');
                            
                            // Add validation message if not exists
                            let feedback = field.nextElementSibling;
                            if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                                feedback = document.createElement('div');
                                feedback.className = 'invalid-feedback';
                                feedback.textContent = 'Field ini wajib diisi';
                                field.parentNode.insertBefore(feedback, field.nextSibling);
                            }
                        } else {
                            field.classList.remove('is-invalid');
                        }
                    });
                    
                    if (isValid) {
                        goToStep(nextStep);
                    }
                });
            });
            
            // Previous button click
            prevButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const currentStep = parseInt(this.dataset.step);
                    const prevStep = currentStep - 1;
                    
                    goToStep(prevStep);
                });
            });
            
            // Stepper item click
            stepperItems.forEach(item => {
                item.addEventListener('click', function() {
                    const clickedStep = parseInt(this.dataset.step);
                    const currentActive = document.querySelector('.stepper-item.active');
                    const currentStep = parseInt(currentActive.dataset.step);
                    
                    // Only allow going back to previous steps
                    if (clickedStep < currentStep) {
                        goToStep(clickedStep);
                    }
                });
            });
            
            // Form validation on submit
            form.addEventListener('submit', function(e) {
                // Validate down payment before submit
                if (!validateDownPayment()) {
                    e.preventDefault();
                    e.stopPropagation();
                    goToStep(3); // Go to step 3 where down payment is
                    return;
                }
                
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Find which step has invalid fields
                    let stepWithError = 1;
                    stepContents.forEach((content, index) => {
                        const invalidFields = content.querySelectorAll(':invalid');
                        if (invalidFields.length > 0) {
                            stepWithError = index + 1;
                        }
                    });
                    
                    goToStep(stepWithError);
                    
                    // Show validation messages
                    this.classList.add('was-validated');
                }
            });
        });
    </script>
</body>
</html>