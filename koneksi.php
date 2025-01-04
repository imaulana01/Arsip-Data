<?php
// File koneksi.php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "arsip_surat";

$conn = new mysqli($host, $user, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
?>
