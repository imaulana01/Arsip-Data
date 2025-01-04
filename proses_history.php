<?php
// Koneksi ke database
require_once 'koneksi.php';

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Fungsi untuk mencatat aktivitas
function logActivity($nama_file, $extension, $proses, $email) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO history (nama_file, extension, proses, email) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nama_file, $extension, $proses, $email);
    $stmt->execute();
    $stmt->close();
}

// Contoh penggunaan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_file = $_POST['nama_file'] ?? '';
    $extension = $_POST['extension'] ?? '';
    $proses = $_POST['proses'] ?? '';
    $email = $_POST['email'] ?? '';

    if ($nama_file && $proses && $email) {
        logActivity($nama_file, $extension, $proses, $email);
        echo "Aktivitas berhasil dicatat.";
    } else {
        echo "Data tidak lengkap.";
    }
}

$conn->close();
?>
