<?php
// setup_db.php
include 'config.php';

echo "<h1>Inisialisasi Database...</h1>";
echo "<hr>";

// Array Query
$queries = [
    "SET FOREIGN_KEY_CHECKS = 0", // Matikan cek foreign key
    "DROP TABLE IF EXISTS penilaian_akhlak", // Drop table anak dulu (Foreign Key)
    "DROP TABLE IF EXISTS absensi",
    "DROP TABLE IF EXISTS siswa",
    "DROP TABLE IF EXISTS master_aspek",
    
    // 1. Tabel Siswa
    "CREATE TABLE IF NOT EXISTS siswa (
        id_siswa INT AUTO_INCREMENT PRIMARY KEY,
        nama_lengkap VARCHAR(255) NOT NULL,
        nis VARCHAR(50) NOT NULL,
        nisn VARCHAR(50) NOT NULL,
        jenis_kelamin VARCHAR(20) NOT NULL,
        kelas VARCHAR(20) NOT NULL
    )",
    
    // 2. Tabel Absensi
    "CREATE TABLE IF NOT EXISTS absensi (
        id_absensi INT AUTO_INCREMENT PRIMARY KEY,
        id_siswa INT NOT NULL,
        tanggal DATE NOT NULL,
        status VARCHAR(20) NOT NULL,
        catatan TEXT,
        FOREIGN KEY (id_siswa) REFERENCES siswa(id_siswa) ON DELETE CASCADE
    )",
    
    // 3. Tabel Master Aspek Akhlak
    "CREATE TABLE IF NOT EXISTS master_aspek (
        id_aspek INT AUTO_INCREMENT PRIMARY KEY,
        nama_aspek VARCHAR(255) NOT NULL UNIQUE
    )",

    // 4. Tabel Penilaian Akhlak
    "CREATE TABLE IF NOT EXISTS penilaian_akhlak (
        id_penilaian INT AUTO_INCREMENT PRIMARY KEY,
        id_siswa INT NOT NULL,
        tanggal DATE NOT NULL,
        aspek_penilaian VARCHAR(255) NOT NULL,
        nilai VARCHAR(50) NOT NULL,
        catatan TEXT,
        FOREIGN KEY (id_siswa) REFERENCES siswa(id_siswa) ON DELETE CASCADE
    )",

    // 5. Insert Data Dummy Siswa
    "INSERT INTO siswa (nama_lengkap, nis, nisn, jenis_kelamin, kelas) VALUES 
    ('Ahmad Zaky', '123456', '0012345678', 'Laki-laki', '3A'),
    ('Siti Aminah', '123457', '0012345679', 'Perempuan', '3A'),
    ('Budi Santoso', '123458', '0012345680', 'Laki-laki', '3A')",
    
    // 6. Insert Data Dummy Aspek Akhlak
    "INSERT IGNORE INTO master_aspek (nama_aspek) VALUES 
    ('Kedisiplinan'),
    ('Kejujuran'),
    ('Tanggung Jawab'),
    ('Kesopanan')"
];

$errors = false;

foreach ($queries as $q) {
    echo "Processing: " . htmlspecialchars(substr($q, 0, 60)) . "... <br>";
    try {
        if (mysqli_query($koneksi, $q)) {
            echo "<span style='color:green'> [OK]</span><br>";
        } else {
            echo "<span style='color:red'> [ERROR] " . mysqli_error($koneksi) . "</span><br>";
            $errors = true;
        }
    } catch (Exception $e) {
         echo "<span style='color:red'> [EXCEPTION] " . $e->getMessage() . "</span><br>";
         $errors = true;
    }
    echo "<br>";
}

echo "<hr>";
if (!$errors) {
    echo "<h2>DATABASE BERHASIL DIBUAT! ✅</h2>";
    echo "<p>Silakan buka <a href='index.php'>Dashboard</a> sekarang.</p>";
} else {
    echo "<h2>Ada Error. Silakan cek pesan di atas.</h2>";
}
?>
