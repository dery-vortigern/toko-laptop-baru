<?php
session_start();
require_once '../../config/koneksi.php';

// Cek login - FIXED: Allow both admin and superadmin roles
if (!isset($_SESSION['login']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header("Location: ../../auth/login.php");
    exit;
}

// Cek parameter id
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$admin_id = $_GET['id'];
$current_admin_id = $_SESSION['admin_id'];

// Mencegah admin menghapus dirinya sendiri
if ($admin_id == $current_admin_id) {
    $_SESSION['error'] = "Anda tidak dapat menghapus akun Anda sendiri!";
    header("Location: index.php");
    exit;
}

// Ambil data admin yang akan dihapus
$admin = query("SELECT * FROM tb_admin WHERE admin_id = $admin_id");

// Jika admin tidak ditemukan
if (!$admin) {
    $_SESSION['error'] = "Admin tidak ditemukan!";
    header("Location: index.php");
    exit;
}

// Cek jika yang dihapus adalah superadmin dan penghapus bukan superadmin
$admin_data = $admin[0];
if ($admin_data['role'] == 'superadmin' && $_SESSION['role'] != 'superadmin') {
    $_SESSION['error'] = "Anda tidak memiliki hak akses untuk menghapus akun Super Admin!";
    header("Location: index.php");
    exit;
}

// Proses hapus
if (hapus('tb_admin', "admin_id = $admin_id")) {
    $_SESSION['success'] = "Admin berhasil dihapus!";
} else {
    $_SESSION['error'] = "Gagal menghapus admin!";
}

header("Location: index.php");
exit;
?>