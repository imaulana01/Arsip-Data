<?php
// Sertakan koneksi.php untuk menggunakan koneksi database
require_once 'koneksi.php';

// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fungsi untuk mencatat aktivitas
function logActivity($nama_file, $extension, $proses, $email) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO history (nama_file, extension, proses, email) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nama_file, $extension, $proses, $email);
    $stmt->execute();
    $stmt->close();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload File</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* CSS Anda tetap dipertahankan */
        /* Reset dasar */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #dedede;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 400px;
            text-align: center;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333333;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-size: 16px;
            color: #555555;
            font-weight: bold;
            text-align: left;
        }

        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #cccccc;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 20px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #999999;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #575757;
        }

        .back-button {
            display: block;
            margin: 20px auto 0;
            padding: 10px;
            width: 50%;
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

        @media (max-width: 480px) {
            h1 {
                font-size: 20px;
            }

            button {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Upload File</h1>
        
        <!-- Form Upload -->
        <form action="upload_file.php" method="post" enctype="multipart/form-data">
            <label for="file">Pilih File:</label>
            <input type="file" name="file" id="file" required>
            <input type="hidden" name="folder_id" value="<?php echo isset($_GET['folder_id']) ? intval($_GET['folder_id']) : 'NULL'; ?>">
            <button type="submit" name="upload">Upload</button>
            <a href="index.php" class="back-button">Kembali ke home</a>
        </form>
    </div>

    <?php
    // Cek apakah tombol upload diklik
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
        // Ambil data dari form
        $file_name = $_FILES['file']['name'];
        $file_tmp = $_FILES['file']['tmp_name'];
        $folder_id = isset($_POST['folder_id']) && $_POST['folder_id'] !== 'NULL' ? intval($_POST['folder_id']) : null;

        // Validasi folder_id
        if ($folder_id) {
            // Ambil nama folder dari database berdasarkan folder_id
            $stmt = $conn->prepare("SELECT nama FROM arsip WHERE id = ? AND is_folder = 1");
            $stmt->bind_param("i", $folder_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $folder = $result->fetch_assoc();
            $stmt->close();

            if (!$folder) {
                echo "Folder dengan ID $folder_id tidak ditemukan.";
                exit();
            }

            // Tentukan direktori berdasarkan nama folder
            $folder_name = preg_replace('/[^A-Za-z0-9_\-]/', '', $folder['nama']);
            $target_dir = "uploads/" . $folder_name . "/";
        } else {
            // Jika folder_id NULL, simpan di root uploads/
            $target_dir = "uploads/";
        }

        // Pastikan direktori ada
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $target_file = $target_dir . basename($file_name);
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);

        // Pindahkan file ke folder tujuan
        if (move_uploaded_file($file_tmp, $target_file)) {
            // Simpan informasi file ke database
            $stmt = $conn->prepare("INSERT INTO arsip (nama, is_folder, parent_id) VALUES (?, 0, ?)");
            $stmt->bind_param("si", $file_name, $folder_id);

            if ($stmt->execute()) {
                echo "File berhasil diupload!";

                // Ambil email dari sesi pengguna (misalnya)
                session_start();
                $email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

                // Jika email tersedia, catat aktivitas upload
                if ($email) {
                    logActivity($file_name, $file_extension, 'upload', $email);
                }
            } else {
                echo "Gagal menyimpan ke database: " . $stmt->error;
            }

            $stmt->close();

            // Redirect ke lokasi sebelumnya
            header("Location: index.php" . ($folder_id ? "?folder_id=$folder_id" : ""));
            exit();
        } else {
            echo "Gagal mengupload file.";
        }
    }
    ?>
</body>
</html>
