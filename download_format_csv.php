<?php
// download_format_csv.php

// Nama file
$nama_file = "format_import_siswa.csv";

// Atur header HTTP untuk memberitahu browser agar mengunduh file sebagai CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $nama_file . '"');

// Buat output
$output = fopen('php://output', 'w');

// Tulis baris header
fputcsv($output, ['NAMA LENGKAP', 'NIS', 'NISN', 'JENIS KELAMIN', 'KELAS']);

// Tulis baris contoh data
fputcsv($output, ['Contoh Siswa Satu', '12345', '0012345678', 'Laki-laki', '3A']);
fputcsv($output, ['Contoh Siswi Dua', '12346', '0023456789', 'Perempuan', '3A']);

fclose($output);
exit;
?>
