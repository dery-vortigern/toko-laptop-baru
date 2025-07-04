<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'toko_db';

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

function query($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    $rows = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    } else {
        // Mencatat error jika query gagal
        error_log("Query error: " . mysqli_error($conn));
    }
    
    return $rows;
}
function tambah($table, $data) {
    global $conn;
    $columns = implode(", ", array_keys($data));
    $values = "'" . implode("', '", array_values($data)) . "'";
    $query = "INSERT INTO $table ($columns) VALUES ($values)";
    
    return mysqli_query($conn, $query);
}

function ubah($table, $data, $where) {
    global $conn;
    $set = [];
    foreach ($data as $key => $value) {
        $set[] = "$key = '$value'";
    }
    $set = implode(", ", $set);
    $query = "UPDATE $table SET $set WHERE $where";
    
    return mysqli_query($conn, $query);
}

function hapus($table, $where) {
    global $conn;
    $query = "DELETE FROM $table WHERE $where";
    return mysqli_query($conn, $query);
}
?>