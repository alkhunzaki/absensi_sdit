<?php
// export_siswa.php
include 'config.php';
check_login();

// Nama file yang akan diunduh
$nama_file = "data-siswa-kelas-3a-" . date('Y-m-d') . ".xls";

// Atur header HTTP untuk memberitahu browser agar mengunduh file sebagai Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$nama_file\"");

// Query untuk mengambil semua data siswa
$result_siswa = mysqli_query($koneksi, "SELECT * FROM siswa ORDER BY nama_lengkap ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Data Siswa</title>
    <style>
        body { font-family: sans-serif; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #dddddd; text-align: left; padding: 8px; }
        th { background-color: #f2f2f2; font-weight: bold; }
    </style>
</head>
<body>
    <h3>Data Siswa Kelas 3A - SDIT Akhyar International Islamic School</h3>
    <table>
        <thead>
            <tr>
                <th>NO</th>
                <th>NAMA LENGKAP</th>
                <th>NIS</th>
                <th>NISN</th>
                <th>JENIS KELAMIN</th>
                <th>KELAS</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result_siswa) > 0): $no = 1; ?>
                <?php while($siswa = mysqli_fetch_assoc($result_siswa)): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($siswa['nama_lengkap']) ?></td>
                    <td>'<?= htmlspecialchars($siswa['nis']) ?></td>
                    <td>'<?= htmlspecialchars($siswa['nisn']) ?></td>
                    <td><?= htmlspecialchars($siswa['jenis_kelamin']) ?></td>
                    <td><?= htmlspecialchars($siswa['kelas']) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Tidak ada data siswa.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
