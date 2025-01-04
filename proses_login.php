<?php
// Koneksi ke database
require_once 'koneksi.php';

session_start();

// Cek jika sudah login, arahkan ke halaman utama
if (isset($_SESSION['user_id'])) {
    header('Location: index.php'); // Jika sudah login, arahkan ke halaman utama
    exit();
}

// Proses login jika data dikirim melalui POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Validasi input
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Email atau password tidak boleh kosong.';
        header('Location: login.php');
        exit();
    }

    try {
        // Query untuk mencari pengguna berdasarkan email
        $stmt = $conn->prepare('SELECT id_user, username, email, password, role FROM user WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Cek apakah pengguna ditemukan
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                // Login berhasil, simpan informasi user dalam session
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Redirect ke halaman utama (index.php) berdasarkan role
                header('Location: index.php');
                exit();
            } else {
                // Password salah
                $_SESSION['error'] = 'Password salah.';
                header('Location: login.php');
                exit();
            }
        } else {
            // Email tidak ditemukan
            $_SESSION['error'] = 'Email tidak ditemukan.';
            header('Location: login.php');
            exit();
        }
    } catch (Exception $e) {
        // Tangani error
        $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
        header('Location: login.php');
        exit();
    }
} else {
    // Jika akses langsung tanpa POST, redirect ke halaman login
    header('Location: login.php');
    exit();
}
?>
