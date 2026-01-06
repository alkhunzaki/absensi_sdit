<?php
// setup_db_new.php
include 'config.php';

echo "<h1>Updating Database...</h1>";
echo "<hr>";

$queries = [
    "CREATE TABLE IF NOT EXISTS pengumuman (
        id_pengumuman INT AUTO_INCREMENT PRIMARY KEY,
        judul VARCHAR(255) NOT NULL,
        isi TEXT NOT NULL,
        tanggal DATE NOT NULL,
        kategori VARCHAR(50) NOT NULL
    )",
    "INSERT IGNORE INTO pengumuman (judul, isi, tanggal, kategori) VALUES 
    ('Selamat Datang di Portal Walimurid', 'Selamat datang di sistem informasi baru untuk orang tua siswa SDIT Akhyar.', CURDATE(), 'Info'),
    ('Ujian Tengah Semester', 'Pelaksanaan UTS akan dimulai pada hari Senin depan. Mohon bimbingan orang tua di rumah.', DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'Agenda')"
];

foreach ($queries as $q) {
    echo "Processing: " . htmlspecialchars(substr($q, 0, 60)) . "... ";
    if (mysqli_query($koneksi, $q)) {
        echo "<span style='color:green'> [OK]</span><br>";
    } else {
        echo "<span style='color:red'> [ERROR] " . mysqli_error($koneksi) . "</span><br>";
    }
}

echo "<hr>";
echo "<h2>Database Update Finished!</h2>";
echo "<p><a href='portal_siswa.php'>Buka Portal Walimurid</a></p>";
?>
