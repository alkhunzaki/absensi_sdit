<?php
// test_db.php
// Script ini digunakan untuk mengetes koneksi database tanpa melakukan redirect.

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: 'db_absensi_sdit';
$db_port = getenv('DB_PORT') ?: 3306;

echo "<h1>Tes Koneksi Database</h1>";
echo "Host: $db_host<br>";
echo "User: $db_user<br>";
echo "DB Name: $db_name<br>";
echo "Port: $db_port<br><hr>";

$koneksi = mysqli_init();

if (getenv('VERCEL') && strpos($db_host, 'tidbcloud.com') !== false) {
    echo "Menyiapkan SSL untuk TiDB Cloud...<br>";
    $ca_path = NULL;
    if (file_exists('/etc/ssl/certs/ca-certificates.crt')) {
        $ca_path = '/etc/ssl/certs/ca-certificates.crt';
        echo "CA Cert ditemukan di /etc/ssl/certs/ca-certificates.crt<br>";
    } else {
        echo "Warning: CA Cert tidak ditemukan di /etc/ssl/certs/ca-certificates.crt<br>";
    }
    mysqli_ssl_set($koneksi, NULL, NULL, $ca_path, NULL, NULL);
    $flags = MYSQLI_CLIENT_SSL;
} else {
    $flags = 0;
}

echo "Mencoba menghubungkan...<br>";
$start = microtime(true);
if (mysqli_real_connect($koneksi, $db_host, $db_user, $db_pass, $db_name, (int)$db_port, NULL, $flags)) {
    $end = microtime(true);
    echo "<h2 style='color:green'>Koneksi BERHASIL!</h2>";
    echo "Waktu koneksi: " . round(($end - $start) * 1000, 2) . " ms<br>";
    
    $res = mysqli_query($koneksi, "SHOW TABLES");
    echo "Daftar Tabel:<br>";
    while ($row = mysqli_fetch_array($res)) {
        echo "- " . $row[0] . "<br>";
    }
} else {
    echo "<h2 style='color:red'>Koneksi GAGAL!</h2>";
    echo "Error: " . mysqli_connect_error() . "<br>";
}
?>
