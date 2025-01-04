<?php
require_once 'koneksi.php';

// Ambil ID file dari URL
$file_id = isset($_GET['file']) ? intval($_GET['file']) : null;

if (!$file_id) {
    die("File ID tidak ditemukan.");
}

// Ambil informasi file dari database berdasarkan id
$stmt = $conn->prepare("SELECT * FROM arsip WHERE id = ?");
$stmt->bind_param("i", $file_id);
$stmt->execute();
$result = $stmt->get_result();
$file = $result->fetch_assoc();

if (!$file || strtolower(pathinfo($file['nama'], PATHINFO_EXTENSION)) !== 'pdf') {
    die("File tidak valid atau tidak ditemukan.");
}

// Fungsi untuk mencari file secara rekursif
function findFile($directory, $filename) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($iterator as $file) {
        if ($file->getFilename() === $filename) {
            return $file->getPathname();
        }
    }
    return false;
}

// Path ke folder uploads
$uploads_dir = 'uploads/';

// Cari file di semua subfolder
$file_path = findFile($uploads_dir, $file['nama']);

if (!$file_path || !file_exists($file_path)) {
    die("File tidak ditemukan di server.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lihat File PDF</title>
    <link rel="stylesheet" href="style.css">
    <style>
                .back-button {
            display: block;
            margin: 20px auto 0;
            padding: 10px;
            width: 10%;
            background-color: rgb(0, 128, 255);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-size: 16px;
            text-align: center;
            transition: background-color 0.3s ease;
            margin-top: -50px;  /* Menggeser tombol ke atas */
            margin-left: 1250px;  /* Menggeser tombol ke kiri */


        }

        .back-button:hover {
            background-color: rgb(86, 171, 255);
        }
    </style>
</head>
<body>
    <h1>Lihat File: <?= htmlspecialchars($file['nama']); ?></h1>
    <a href="index.php" class="back-button">Kembali ke home</a>
    <embed src="<?= htmlspecialchars($file_path); ?>" type="application/pdf" width="100%" height="600px">
</body>
</html>
