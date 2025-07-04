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

$barang_id = $_GET['id'];

// Cek apakah barang masih terkait dengan tabel lain
$cek_detail_pembelian = query("SELECT * FROM tb_detail_pembelian WHERE barang_id = $barang_id");
$cek_detail_penjualan = query("SELECT * FROM tb_detail_penjualan WHERE barang_id = $barang_id");
$cek_supplier = query("SELECT * FROM tb_supplier WHERE barang_id = $barang_id");

if ($cek_detail_pembelian || $cek_detail_penjualan || $cek_supplier) {
    $_SESSION['error'] = "Barang tidak dapat dihapus karena masih terkait dengan data lain!";
    header("Location: index.php");
    exit;
}

// Proses hapus
if (hapus('tb_barang', "barang_id = $barang_id")) {
    $_SESSION['success'] = "Data barang berhasil dihapus!";
} else {
    $_SESSION['error'] = "Gagal menghapus data barang!";
}

header("Location: index.php");
exit;
?>