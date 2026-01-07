<?php
include 'config.php';

echo "<h1>Updating Security Structure...</h1><hr>";

// 1. Create Users Table
$query_users = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($koneksi, $query_users)) {
    echo "[OK] Users table ready.<br>";
} else {
    echo "[ERROR] " . mysqli_error($koneksi) . "<br>";
}

// 2. Insert Default Admin (admin/admin123)
$username = 'admin';
$password = password_hash('admin123', PASSWORD_DEFAULT);

$stmt = mysqli_prepare($koneksi, "INSERT IGNORE INTO users (username, password) VALUES (?, ?)");
mysqli_stmt_bind_param($stmt, "ss", $username, $password);

if (mysqli_stmt_execute($stmt)) {
    echo "[OK] Default admin user created (admin / admin123).<br>";
    echo "<b>PENTING: Segera hapus file ini setelah dijalankan!</b>";
} else {
    echo "[ERROR] " . mysqli_error($koneksi) . "<br>";
}

echo "<hr><a href='login.php'>Ke Halaman Login</a>";
?>
