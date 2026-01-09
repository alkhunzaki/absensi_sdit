<?php
// import_csv.php
include 'config.php';
check_login();

if (isset($_POST['impor'])) {
    $file_name = $_FILES['file_csv']['name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Pastikan file adalah CSV
    if ($file_ext == 'csv') {
        $file_path = $_FILES['file_csv']['tmp_name'];
        $file = fopen($file_path, "r");

        $jumlah_sukses = 0;
        $is_header = true; // Flag untuk melewati baris pertama (header)

        // Mulai transaksi database
        mysqli_begin_transaction($koneksi);

        try {
            while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {
                // Lewati baris header
                if ($is_header) {
                    $is_header = false;
                    continue;
                }

                // Ambil data (tidak perlu escape manual jika menggunakan prepared statement)
                $nama_lengkap = $data[0] ?? '';
                $nis = $data[1] ?? '';
                $nisn = $data[2] ?? '';
                $jenis_kelamin = $data[3] ?? '';
                $kelas = $data[4] ?? '';

                // Hanya proses jika nama tidak kosong
                if (!empty($nama_lengkap)) {
                    // Gunakan Prepared Statement untuk keamanan dan performa
                    if (!isset($stmt_import)) {
                        $query = "
                            INSERT INTO siswa (nama_lengkap, nis, nisn, jenis_kelamin, kelas) 
                            VALUES (?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE 
                            nama_lengkap = VALUES(nama_lengkap), 
                            jenis_kelamin = VALUES(jenis_kelamin), 
                            kelas = VALUES(kelas)
                        ";
                        $stmt_import = mysqli_prepare($koneksi, $query);
                    }
                    
                    mysqli_stmt_bind_param($stmt_import, "sssss", $nama_lengkap, $nis, $nisn, $jenis_kelamin, $kelas);
                    
                    if (mysqli_stmt_execute($stmt_import)) {
                        $jumlah_sukses++;
                    }
                }
            }
            // Jika semua query berhasil, commit transaksi
            mysqli_commit($koneksi);
            header('Location: master_siswa.php?status=sukses_impor&jumlah=' . $jumlah_sukses);

        } catch (Exception $e) {
            // Jika ada satu saja query yang gagal, batalkan semua
            mysqli_rollback($koneksi);
            header('Location: master_siswa.php?status=gagal_impor');
        }

        fclose($file);

    } else {
        header('Location: master_siswa.php?status=file_salah');
    }
} else {
    header('Location: master_siswa.php');
}
exit;
?>
