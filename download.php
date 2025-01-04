<?php
include 'koneksi.php'; // Koneksi ke database

// Fungsi untuk mencari file secara rekursif di dalam folder
function findFile($directory, $filename) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($iterator as $file) {
        if ($file->getFilename() === $filename) {
            return $file->getPathname();
        }
    }
    return false;
}

// Ambil parameter file ID dari URL
$fileId = isset($_GET['file']) ? intval($_GET['file']) : null;

if (!$fileId) {
    die("File ID tidak ditemukan.");
}

// Query database untuk mendapatkan informasi file berdasarkan ID
$query = "SELECT nama FROM arsip WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $fileId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $fileName = $row['nama'];

    // Tentukan folder utama tempat file berada
    $baseFolder = 'uploads';

    // Cari file secara rekursif
    $filePath = findFile($baseFolder, $fileName);

    // Periksa apakah file ditemukan
    if ($filePath && file_exists($filePath)) {
        // Header untuk memulai proses download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        echo "File tidak ditemukan di server.";
    }
} else {
    echo "File tidak ditemukan di database.";
}
?>
