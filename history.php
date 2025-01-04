<?php
// Koneksi ke database
require_once 'koneksi.php';

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Query untuk mengambil data history
$sql = "SELECT nama_file, extension, proses, email, waktu FROM history ORDER BY waktu DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Aktivitas</title>
    <style>
/* Mengatur tampilan secara umum */
body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f4f9; /* Warna latar belakang yang lebih terang */
    margin: 0;
    padding: 20px;
    color: #333; /* Warna teks lebih gelap agar mudah dibaca */
}

/* Judul halaman */
h1 {
    text-align: center;
    color: #333;
    margin-bottom: 30px;
    font-size: 2.5em;
    font-weight: bold;
    letter-spacing: 1px;
}

/* Kontainer untuk membatasi lebar konten */
.container {
    max-width: 1200px;
    margin: 0 auto;
    background-color: white; /* Memberikan latar belakang putih pada kontainer */
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); /* Memberikan bayangan pada kontainer */
}

/* Tabel yang lebih elegan */
table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 30px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Bayangan untuk tabel */
}

/* Gaya untuk header tabel */
th {
    background-color: #727272;
    color: white;
    padding: 12px;
    text-align: left;
    font-weight: bold;
}

/* Gaya untuk sel tabel */
td {
    padding: 12px;
    text-align: left;
    background-color: #ffffff;
    font-size: 1.1em;
}

/* Memberikan warna bergantian untuk baris tabel */
tr:nth-child(even) {
    background-color: #f9f9f9;
}

tr:hover {
    background-color: #f1f1f1; /* Warna saat baris tabel di-hover */
}

/* Tombol kembali yang menarik */
.back-button {
    display: inline-block;
    padding: 12px 25px;
    background-color: #007BFF;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 1.1em;
    margin-top: 20px;
    transition: background-color 0.3s ease, transform 0.2s ease; /* Efek transisi */
}

.back-button:hover {
    background-color: #0056b3;
    transform: translateY(-2px); /* Menambahkan efek mengangkat tombol saat hover */
}

.back-button:active {
    transform: translateY(2px); /* Efek ketika tombol ditekan */
}

/* Responsif untuk perangkat mobile */
@media (max-width: 768px) {
    table, th, td {
        font-size: 0.9em; /* Menurunkan ukuran font pada perangkat mobile */
    }

    .back-button {
        width: 100%; /* Tombol akan lebar penuh pada layar kecil */
        text-align: center;
    }

    h1 {
        font-size: 2em;
    }
}


    </style>
</head>
<body>

<div class="container">
    <h1>Riwayat Aktivitas</h1>
    
    <table>
        <thead>
            <tr>
                <th>Nama File</th>
                <th>Ekstensi</th>
                <th>Proses</th>
                <th>Email</th>
                <th>Waktu</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Menampilkan status proses dalam bahasa Indonesia
                    $proses = ($row['proses'] == 'upload') ? 'Unggah' : 'Hapus';
                    echo "<tr>
                        <td>{$row['nama_file']}</td>
                        <td>{$row['extension']}</td>
                        <td>{$proses}</td>
                        <td>{$row['email']}</td>
                        <td>{$row['waktu']}</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='5' style='text-align: center;'>Tidak ada data</td></tr>";
            }
            ?>
        </tbody>
    </table>
    
    <a href="index.php" class="back-button">Kembali ke Halaman Utama</a>
</div>

</body>
</html>

<?php
$conn->close();
?>
