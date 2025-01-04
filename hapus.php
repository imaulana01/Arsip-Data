<?php
require_once 'koneksi.php';

// Memulai sesi
session_start();

// Fungsi untuk menghapus file dan folder dari server secara rekursif
function deleteFolderRecursively($folderPath) {
    if (!is_dir($folderPath)) {
        return false;
    }

    $items = array_diff(scandir($folderPath), ['.', '..']);
    foreach ($items as $item) {
        $itemPath = $folderPath . DIRECTORY_SEPARATOR . $item;
        if (is_dir($itemPath)) {
            deleteFolderRecursively($itemPath); // Hapus subfolder
        } else {
            unlink($itemPath); // Hapus file
        }
    }

    return rmdir($folderPath); // Hapus folder
}

// Fungsi untuk menghapus folder dan subfolder dari database
function deleteFolderFromDatabase($conn, $folderId) {
    // Cari semua subfolder dan file dalam folder ini
    $stmt = $conn->prepare("SELECT id, is_folder FROM arsip WHERE parent_id = ?");
    $stmt->bind_param("i", $folderId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        if ($row['is_folder'] == 1) {
            // Jika item adalah folder, hapus secara rekursif
            deleteFolderFromDatabase($conn, $row['id']);
        } else {
            // Jika item adalah file, hapus langsung dari database
            $deleteFileStmt = $conn->prepare("DELETE FROM arsip WHERE id = ?");
            $deleteFileStmt->bind_param("i", $row['id']);
            $deleteFileStmt->execute();
            $deleteFileStmt->close();
        }
    }

    $stmt->close();

    // Hapus folder itu sendiri dari database
    $deleteFolderStmt = $conn->prepare("DELETE FROM arsip WHERE id = ?");
    $deleteFolderStmt->bind_param("i", $folderId);
    $deleteFolderStmt->execute();
    $deleteFolderStmt->close();
}

// Cek apakah ada parameter id dan tipe yang diterima
if (isset($_GET['id']) && isset($_GET['tipe'])) {
    $id = intval($_GET['id']);
    $tipe = $_GET['tipe']; // Ambil nilai tipe (file/folder)

    if ($id > 0 && ($tipe === 'file' || $tipe === 'folder')) {
        // Query untuk mendapatkan informasi file/folder berdasarkan ID
        $stmt = $conn->prepare("SELECT nama, is_folder, parent_id FROM arsip WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        $stmt->close();

        if ($item) {
            $item_name = $item['nama'];
            $is_folder = $item['is_folder'];
            $parent_id = $item['parent_id'];

            // Tentukan path file atau folder di direktori uploads
            if ($is_folder) {
                $target_path = "uploads/" . preg_replace('/[^A-Za-z0-9_\-]/', '', $item_name);
            } else {
                $stmt = $conn->prepare("SELECT nama FROM arsip WHERE id = ?");
                $stmt->bind_param("i", $parent_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $parent_folder = $result->fetch_assoc();
                $stmt->close();

                if ($parent_folder) {
                    $parent_folder_name = preg_replace('/[^A-Za-z0-9_\-]/', '', $parent_folder['nama']);
                    $target_path = "uploads/" . $parent_folder_name . "/" . $item_name;
                } else {
                    $target_path = "uploads/" . $item_name;
                }
            }

            // Hapus file atau folder secara fisik
            if (file_exists($target_path)) {
                if ($is_folder) {
                    // Hapus folder beserta isinya secara rekursif
                    deleteFolderRecursively($target_path);
                } else {
                    // Hapus file
                    unlink($target_path);
                }
            }

            // Pastikan email valid di session
            if (isset($_SESSION['email'])) {
                $email = $_SESSION['email'];

                // Tentukan proses yang benar ('delete') sesuai dengan aksi
                $proses = ($tipe === 'file' || $tipe === 'folder') ? 'delete' : 'upload';

                // 1. Masukkan data history untuk mencatat aksi ini
                $stmt_history = $conn->prepare("INSERT INTO history (nama_file, extension, email, proses) VALUES (?, ?, ?, ?)");
                $stmt_history->bind_param("ssss", $item_name, pathnfo($item_name, PATHINFO_EXTENSION), $email, $proses);
                $stmt_history->execute();
                $stmt_history->close();

                // 2. Lanjutkan menghapus file atau folder dari tabel arsip setelah mencatat history
                if ($tipe === 'file') {
                    // Hapus file berdasarkan ID dari tabel arsip
                    $stmt = $conn->prepare("DELETE FROM arsip WHERE id = ? AND is_folder = 0");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $stmt->close();
                } elseif ($tipe === 'folder') {
                    // Hapus folder dan semua isinya dari database arsip
                    deleteFolderFromDatabase($conn, $id);
                }

                // Redirect ke lokasi asal setelah operasi selesai
                if ($parent_id > 0) {
                    header("Location: index.php?folder_id=" . $parent_id);
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                echo "Tidak ada email yang terdaftar dalam sesi.";
            }
        } else {
            echo "Item tidak ditemukan.";
        }
    } else {
        echo "ID atau tipe tidak valid.";
    }
} else {
    echo "ID atau tipe tidak valid.";
}
?>
