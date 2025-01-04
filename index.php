<?php
require_once 'koneksi.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Ambil folder_id dari URL (jika ada)
$folder_id = isset($_GET['folder_id']) ? intval($_GET['folder_id']) : null;
if ($folder_id <= 0) {
    $folder_id = null;
}

// Ambil kata kunci pencarian (jika ada)
$search = isset($_GET['search']) ? trim($_GET['search']) : null;

// Query untuk mendapatkan folder dan file berdasarkan kondisi pencarian atau parent_id
if ($search) {
    // Pencarian mencakup file dan folder di semua subfolder
    $sql = "SELECT * FROM arsip 
            WHERE nama LIKE ? 
            AND (parent_id = ? OR ? IS NULL)";
    $stmt = $conn->prepare($sql);

    $searchTerm = '%' . $search . '%';
    $stmt->bind_param("sii", $searchTerm, $folder_id, $folder_id);
} else {
    // Jika tidak ada pencarian
    $sql = "SELECT * FROM arsip WHERE parent_id " . ($folder_id === null ? "IS NULL" : "= ?");
    $stmt = $conn->prepare($sql);

    if ($folder_id !== null) {
        $stmt->bind_param("i", $folder_id);
    }
}

$stmt->execute();
$result = $stmt->get_result();

// Query untuk mendapatkan nama folder saat berada di dalam folder (breadcrumb)
$breadcrumb = null;
if ($folder_id !== null) {
    $breadcrumb_stmt = $conn->prepare("SELECT nama FROM arsip WHERE id = ?");
    $breadcrumb_stmt->bind_param("i", $folder_id);
    $breadcrumb_stmt->execute();
    $breadcrumb_result = $breadcrumb_stmt->get_result();
    $breadcrumb = $breadcrumb_result->fetch_assoc();
}

// Fungsi untuk menentukan ikon berdasarkan ekstensi file
function getFileIcon($fileName) {
    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
    switch (strtolower($ext)) {
        case 'pdf':
            return 'images/pdf.png'; // Path ke ikon PDF
        case 'doc':
        case 'docx':
        case 'dotx':
        case 'rtf':
        case 'odt':
            return 'images/word.png'; // Path ke ikon Word
        case 'xls':
        case 'xlsx':
        case 'xlsm':
        case 'csv':
        case 'ods':
            return 'images/excel.png'; // Path ke ikon Excel
        case 'ppt':
        case 'pptx':
        case 'ppsx':
        case 'odp':
            return 'images/ppt.png'; // Path ke ikon PowerPoint
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            return 'images/img.png'; // Path ke ikon Gambar
        default:
            return 'images/default.png'; // Path ke ikon default
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Utama - Arsip Surat</title>
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #f4f4f9;
            --accent-color: #FF5722;
            --text-color: #333;
            --light-text-color: #fff;
            --card-bg-color: #ffffff;
            --border-color: #ddd;
        }

        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--secondary-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        header {
            background-color: #727272;
            color: var(--light-text-color);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        header .logo {
            display: flex;
            align-items: center;
        }

        header .logo img {
            height: 100px;
            margin-right: 50px;
        }

        header nav button {
            background-color: #a7a7a7;
            color: var(--light-text-color);
            border: none;
            border-radius: 20px;
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        header nav button:hover {
            background-color: #e64a19;
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            background-color: var(--card-bg-color);
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .breadcrumb {
            margin-bottom: 15px;
            font-size: 14px;
            color: black;
        }

        .breadcrumb a {
            text-decoration: none;
            color: #00c9ff;
            font-weight: bold;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .search {
            margin-bottom: 20px;
        }

        .search form {
            display: flex;
            justify-content: space-between;
        }

        .search input[type="text"] {
            width: 80%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
        }

        .search button {
            padding: 10px 20px;
            background-color: black;
            color: var(--light-text-color);
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .search button:hover {
            background-color: var(--accent-color);
        }

        .item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            margin-bottom: 15px;
            background-color: var(--secondary-color);
            transition: box-shadow 0.3s ease;
        }

        .item:hover {
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .item img {
            height: 40px;
            margin-right: 15px;
        }

        .item .details {
            flex: 1;
        }

        .item .details strong {
            font-size: 16px;
            color: black;
        }

        .item button {
            background-color: #555555;
            color: var(--light-text-color);
            border: none;
            border-radius: 20px;
            padding: 8px 12px;
            font-size: 14px;
            cursor: pointer;
            margin-left: 10px;
            transition: background-color 0.3s ease;
        }

        .item button:hover {
            background-color: #000000;
        }

        footer {
            text-align: center;
            padding: 15px;
            background-color: var(--primary-color);
            color: var(--light-text-color);
            margin-top: 30px;
            border-top: 4px solid var(--accent-color);
        }

        @media (max-width: 768px) {
            header,
            .item {
                flex-direction: column;
                text-align: center;
            }

            header nav button,
            .item button {
                margin: 10px 0;
                width: 90%;
            }
        }
        </style>
</head>
<body>

<header>
    <div class="logo">
        <img src="images/logokpu.png" alt="Logo KPU">
        <h1>Arsip Surat</h1>
    </div>
    <nav>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <!-- Admin dapat membuat folder dan mengelola arsip -->
            <button onclick="location.href='buat_folder.php<?php echo $folder_id !== null ? '?parent_id=' . $folder_id : ''; ?>'">Buat Folder</button>
            <button onclick="location.href='history.php'">History</button>
        <?php endif; ?>
        
        <!-- Semua pengguna dapat upload file -->
        <button onclick="location.href='upload_file.php<?php echo $folder_id !== null ? '?folder_id=' . $folder_id : ''; ?>'">Upload File</button>
        
        <button onclick="location.href='logout.php'">Keluar</button>
    </nav>
</header>

<div class="container">
    <h2>Daftar Arsip</h2>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <?php if ($search): ?>
            <a href="index.php">Menu Utama</a> &gt; 
            <span>Pencarian: "<?= htmlspecialchars($search); ?>"</span>
        <?php elseif ($folder_id !== null): ?>
            <a href="index.php">Menu Utama</a> &gt; 
            <span><?= htmlspecialchars($breadcrumb['nama'] ?? 'Tidak Diketahui'); ?></span>
        <?php else: ?>
            Menu Utama
        <?php endif; ?>
    </div>

    <!-- Search -->
    <div class="search">
        <form method="GET" action="index.php">
            <input type="text" name="search" placeholder="Cari nama file atau folder..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button type="submit">Cari</button>
        </form>
    </div>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="item">
                <?php if (isset($row['is_folder']) && $row['is_folder'] == 1): ?>
                    <img src="images/folder.png" alt="Ikon Folder">
                    <div class="details">
                        <strong><?= htmlspecialchars($row['nama']); ?></strong>
                    </div>
                    <button onclick="location.href='index.php?folder_id=<?= urlencode($row['id']); ?>'">Buka</button>
                    
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <!-- Hanya admin yang dapat menghapus folder -->
                        <button onclick="hapusItem(<?= $row['id']; ?>, 'folder')">Hapus</button>
                    <?php endif; ?>
                <?php else: ?>
                    <img src="<?= getFileIcon($row['nama']); ?>" alt="Ikon File">
                    <div class="details">
                        <strong><?= htmlspecialchars($row['nama']); ?></strong>
                    </div>
                    <button onclick="location.href='download.php?file=<?= $row['id'] ?>'">Download</button>
                    
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <!-- Admin dapat menghapus file -->
                        <button onclick="hapusItem(<?= $row['id']; ?>, 'file')">Hapus</button>
                    <?php endif; ?>
                    
                    <!-- Tombol Lihat untuk file PDF -->
                    <?php if (strtolower(pathinfo($row['nama'], PATHINFO_EXTENSION)) === 'pdf'): ?>
                        <button onclick="location.href='lihat_file.php?file=<?= urlencode($row['id']); ?>'">Lihat</button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Tidak ada arsip untuk ditampilkan.</p>
    <?php endif; ?>
</div>

<script>
    // Fungsi untuk menghapus item
    function hapusItem(id, tipe) {
        if (confirm(`Apakah Anda yakin ingin menghapus ${tipe} ini?`)) {
            // Redirect ke halaman hapus.php dengan ID yang sesuai
            window.location.href = `hapus.php?id=${id}&tipe=${tipe}`;
        }
    }
</script>

</body>
</html>