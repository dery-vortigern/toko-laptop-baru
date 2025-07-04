<?php
session_start();

// Hapus semua data session
$_SESSION = [];

// Hapus cookie session
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Hancurkan session
session_destroy();

// Hapus cookie login jika ada
if (isset($_COOKIE['id']) && isset($_COOKIE['key'])) {
    setcookie('id', '', time() - 3600, '/');
    setcookie('key', '', time() - 3600, '/');
}

// Redirect ke halaman login
header("Location: login.php");
exit;
?>