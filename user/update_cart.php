<?php
session_start();
require_once '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barang_id = $_POST['barang_id'];
    $qty = (int)$_POST['qty'];
    
    // Cek stok
    $barang = query("SELECT stok FROM tb_barang WHERE barang_id = $barang_id")[0];
    
    if ($qty > $barang['stok']) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Stok tidak mencukupi! Stok tersedia: ' . $barang['stok']
        ]);
        exit;
    }
    
    if ($qty > 0) {
        $_SESSION['cart'][$barang_id] = $qty;
        echo json_encode([
            'status' => 'success',
            'message' => 'Keranjang berhasil diupdate'
        ]);
    } else {
        unset($_SESSION['cart'][$barang_id]);
        echo json_encode([
            'status' => 'success',
            'message' => 'Produk dihapus dari keranjang'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}
?>