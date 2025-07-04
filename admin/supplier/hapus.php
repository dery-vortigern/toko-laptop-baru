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

$supplier_id = $_GET['id'];

// Cek apakah supplier masih memiliki relasi dengan barang
$cek = query("SELECT * FROM tb_barang WHERE barang_id IN (SELECT barang_id FROM tb_supplier WHERE supplier_id = $supplier_id)");

if ($cek) {
    $_SESSION['error'] = "Supplier tidak dapat dihapus karena masih memiliki produk terkait!";
    header("Location: index.php");
    exit;
}

// Proses hapus
if (hapus('tb_supplier', "supplier_id = $supplier_id")) {
    $_SESSION['success'] = "Data supplier berhasil dihapus!";
} else {
    $_SESSION['error'] = "Gagal menghapus data supplier!";
}

header("Location: index.php");
exit;
?>