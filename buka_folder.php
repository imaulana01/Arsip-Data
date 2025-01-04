<?php
require_once 'koneksi.php';

$id_folder = $_GET['folder'] ?? 0;

$sql = "SELECT * FROM folder WHERE parent_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_folder);
$stmt->execute();
$result_folder = $stmt->get_result();

$sql = "SELECT * FROM file WHERE id_folder = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_folder);
$stmt->execute();
$result_file = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Isi Folder</title>
</head>
<body>
    <h2>Isi Folder</h2>

    <h3>Subfolder</h3>
    <ul>
        <?php while ($folder = $result_folder->fetch_assoc()): ?>
            <li><a href="buka_folder.php?folder=<?= $folder['id_folder'] ?>"><?= htmlspecialchars($folder['nama_folder']) ?></a></li>
        <?php endwhile; ?>
    </ul>

    <h3>Files</h3>
    <ul>
        <?php while ($file = $result_file->fetch_assoc()): ?>
            <li><?= htmlspecialchars($file['nama_file']) ?> - <a href="download.php?file=<?= $file['id_file'] ?>">Download</a></li>
        <?php endwhile; ?>
    </ul>
</body>
</html>
