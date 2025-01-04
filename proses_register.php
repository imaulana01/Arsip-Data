<?php
require_once 'koneksi.php'; // Pastikan koneksi database ada

// Ambil data dari form
$email = $_POST['email'];
$username = $_POST['username'];
$password = $_POST['password'];

// Periksa apakah username atau email sudah ada
$stmt = $conn->prepare('SELECT * FROM user WHERE username = ? OR email = ?');
$stmt->bind_param('ss', $username, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Jika username atau email sudah digunakan
    echo 'Username atau email sudah terdaftar!';
    exit();
}

// Hash password sebelum disimpan
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Masukkan data ke database
$stmt = $conn->prepare('INSERT INTO user (username, email, password) VALUES (?, ?, ?)');
$stmt->bind_param('sss', $username, $email, $hashed_password);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo 'User berhasil ditambahkan!';
} else {
    echo 'Gagal menambahkan user.';
}

// Redirect ke halaman login
header('Location: login.php');
exit();
?>
