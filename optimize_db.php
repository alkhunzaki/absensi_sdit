<?php
include 'config.php';

echo "<h1>Optimasi Database (Indexing)...</h1><hr>";

$queries = [
    // Index untuk tabel absensi agar filter tanggal dan status cepat
    "CREATE INDEX idx_tanggal ON absensi(tanggal)",
    "CREATE INDEX idx_status ON absensi(status)",
    "CREATE INDEX idx_siswa_absensi ON absensi(id_siswa)",
    
    // Index untuk tabel siswa
    "CREATE INDEX idx_kelas ON siswa(kelas)",
    
    // Index untuk penilaian akhlak
    "CREATE INDEX idx_tanggal_akhlak ON penilaian_akhlak(tanggal)",
    "CREATE INDEX idx_siswa_akhlak ON penilaian_akhlak(id_siswa)"
];

foreach ($queries as $q) {
    echo "Executing: $q ... ";
    try {
        if (mysqli_query($koneksi, $q)) {
            echo "<span style='color:green'>[OK]</span><br>";
        } else {
            echo "<span style='color:orange'>[ALREADY EXISTS / ERROR] " . mysqli_error($koneksi) . "</span><br>";
        }
    } catch (Exception $e) {
        echo "<span style='color:orange'>[SKIP] " . $e->getMessage() . "</span><br>";
    }
}

echo "<hr><a href='index.php'>Kembali ke Dashboard</a>";
?>
