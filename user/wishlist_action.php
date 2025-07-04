<?php
session_start();
require_once '../config/koneksi.php';

// Set header JSON
header('Content-Type: application/json');

// Log debugging
error_log("wishlist_action.php dipanggil");

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'user') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Anda harus login terlebih dahulu!'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Debug log
error_log("User ID: $user_id");

// Cek jika tabel wishlist sudah ada
$checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'tb_wishlist'");
if (mysqli_num_rows($checkTable) == 0) {
    // Buat tabel wishlist jika belum ada
    $createTable = "CREATE TABLE tb_wishlist (
        wishlist_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        barang_id INT(11) NOT NULL,
        tanggal DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_wishlist (user_id, barang_id)
    )";
    
    if (!mysqli_query($conn, $createTable)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal membuat tabel wishlist: ' . mysqli_error($conn)
        ]);
        exit;
    }
    
    error_log("Tabel wishlist dibuat");
}

// Inisialisasi respons default
$response = [
    'status' => 'error',
    'message' => 'Permintaan tidak valid'
];

// Cek request method dan data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log semua data POST
    error_log("POST data: " . print_r($_POST, true));
    
    // Ambil action dan barang_id
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $barang_id = isset($_POST['barang_id']) ? (int)$_POST['barang_id'] : 0;
    
    error_log("Action: $action, Barang ID: $barang_id");
    
    // Validasi barang_id
    if (!$barang_id) {
        echo json_encode([
            'status' => 'error',
            'message' => 'ID produk tidak valid!'
        ]);
        exit;
    }
    
    // Cek apakah produk ada
    $check_barang_query = "SELECT barang_id FROM tb_barang WHERE barang_id = $barang_id";
    $check_barang_result = mysqli_query($conn, $check_barang_query);
    
    if (!$check_barang_result || mysqli_num_rows($check_barang_result) == 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Produk tidak ditemukan!'
        ]);
        exit;
    }
    
    // Proses berdasarkan action
    switch ($action) {
        case 'add':
            // Cek apakah sudah ada di wishlist
            $check_query = "SELECT wishlist_id FROM tb_wishlist WHERE user_id = $user_id AND barang_id = $barang_id";
            $check_result = mysqli_query($conn, $check_query);
            
            if ($check_result && mysqli_num_rows($check_result) > 0) {
                // Sudah ada di wishlist
                $response = [
                    'status' => 'success',
                    'message' => 'Produk sudah ada di wishlist Anda!'
                ];
            } else {
                // Tambahkan ke wishlist
                $tanggal = date('Y-m-d H:i:s');
                $insert_query = "INSERT INTO tb_wishlist (user_id, barang_id, tanggal) VALUES ($user_id, $barang_id, '$tanggal')";
                
                try {
                    if (mysqli_query($conn, $insert_query)) {
                        $response = [
                            'status' => 'success',
                            'message' => 'Produk berhasil ditambahkan ke wishlist!'
                        ];
                        error_log("Produk berhasil ditambahkan ke wishlist");
                    } else {
                        $error = mysqli_error($conn);
                        error_log("Error saat menambahkan ke wishlist: $error");
                        
                        // Cek jika error karena duplikasi
                        if (strpos($error, 'Duplicate entry') !== false) {
                            $response = [
                                'status' => 'success',
                                'message' => 'Produk sudah ada di wishlist Anda!'
                            ];
                        } else {
                            $response = [
                                'status' => 'error',
                                'message' => 'Gagal menambahkan produk ke wishlist'
                            ];
                        }
                    }
                } catch (Exception $e) {
                    error_log("Exception: " . $e->getMessage());
                    $response = [
                        'status' => 'error',
                        'message' => 'Terjadi kesalahan saat memproses permintaan'
                    ];
                }
            }
            break;
            
        case 'remove':
            // Hapus dari wishlist
            $delete_query = "DELETE FROM tb_wishlist WHERE user_id = $user_id AND barang_id = $barang_id";
            
            if (mysqli_query($conn, $delete_query)) {
                if (mysqli_affected_rows($conn) > 0) {
                    $response = [
                        'status' => 'success',
                        'message' => 'Produk berhasil dihapus dari wishlist!'
                    ];
                } else {
                    $response = [
                        'status' => 'error',
                        'message' => 'Produk tidak ada di wishlist Anda.'
                    ];
                }
            } else {
                $response = [
                    'status' => 'error',
                    'message' => 'Gagal menghapus produk dari wishlist'
                ];
            }
            break;
            
        case 'check':
            // Cek apakah produk ada di wishlist
            $check_query = "SELECT wishlist_id FROM tb_wishlist WHERE user_id = $user_id AND barang_id = $barang_id";
            $check_result = mysqli_query($conn, $check_query);
            
            $response = [
                'status' => 'success',
                'exists' => ($check_result && mysqli_num_rows($check_result) > 0)
            ];
            break;
            
        default:
            $response = [
                'status' => 'error',
                'message' => 'Action tidak valid!'
            ];
            break;
    }
} else {
    $response = [
        'status' => 'error',
        'message' => 'Method tidak valid! Harap gunakan POST.'
    ];
}

// Log response untuk debugging
error_log("Response: " . json_encode($response));

// Mengembalikan respon dalam format JSON
echo json_encode($response);
exit;