<?php
require_once 'koneksi.php';
session_start();

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Ambil parent_id dari URL (jika ada)
$parent_id = isset($_GET['parent_id']) ? intval($_GET['parent_id']) : NULL;

// Fungsi untuk mencatat aktivitas ke dalam history
function logActivity($nama_file, $extension, $proses, $email) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO history (nama_file, extension, proses, email) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nama_file, $extension, $proses, $email);
    $stmt->execute();
    $stmt->close();
}

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_folder = trim($_POST['nama_folder']);

    if (!empty($nama_folder)) {
        // Query untuk membuat folder baru di database
        $stmt = $conn->prepare("INSERT INTO arsip (nama, tipe, parent_id, is_folder) VALUES (?, 'folder', ?, 1)");
        $stmt->bind_param("si", $nama_folder, $parent_id);

        if ($stmt->execute()) {
            echo "<script>alert('Folder berhasil dibuat!');</script>";

            // Cek apakah email ada di session
            if (isset($_SESSION['email'])) {
                $email = $_SESSION['email'];  // Ambil email dari session

                // Pencatatan aktivitas folder
                logActivity($nama_folder, '', 'upload', $email);  // 'upload' digunakan untuk menandakan pembuatan folder
            }

            // Redirect ke folder induk (jika ada)
            if ($parent_id !== NULL) {
                header("Location: index.php?folder_id=" . $parent_id);
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            echo "Terjadi kesalahan: " . $stmt->error;
        }
    } else {
        echo "<script>alert('Nama folder tidak boleh kosong!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Folder Baru</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #dedede;
            margin: 0;
            padding: 0;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            text-align: center;
            margin: auto;
            width: 30%;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        input[type="text"] {
            width: 90%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            padding: 10px;
            background-color: #999999;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #575757;
        }

        .back-button {
            display: block;
            margin: 20px auto 0;
            padding: 10px;
            width: 40%;
            background-color: rgb(0, 128, 255);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            text-align: center;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: rgb(86, 171, 255);
        }

        @media (max-width: 600px) {
            form {
                padding: 15px;
            }

            button {
                font-size: 14px;
                padding: 8px 16px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Buat Folder Baru</h1>
    <p>Masukkan Nama Folder Baru</p>
    <form method="POST">
        <input type="text" id="nama_folder" name="nama_folder" placeholder="Nama Folder" required><br>
        <button type="submit">Buat Folder</button>
    </form>
    <a href="index.php<?= $parent_id !== NULL ? '?folder_id=' . $parent_id : ''; ?>" class="back-button">Kembali</a>
</div>
</body>
</html>
