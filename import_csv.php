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

                // Ambil dan bersihkan data
                $nama_lengkap = mysqli_real_escape_string($koneksi, $data[0]);
                $nis = mysqli_real_escape_string($koneksi, $data[1]);
                $nisn = mysqli_real_escape_string($koneksi, $data[2]);
                $jenis_kelamin = mysqli_real_escape_string($koneksi, $data[3]);
                $kelas = mysqli_real_escape_string($koneksi, $data[4]);

                // Hanya proses jika nama tidak kosong
                if (!empty($nama_lengkap)) {
                    // Query untuk memasukkan atau memperbarui data
                    $query = "
                        INSERT INTO siswa (nama_lengkap, nis, nisn, jenis_kelamin, kelas) 
                        VALUES ('$nama_lengkap', '$nis', '$nisn', '$jenis_kelamin', '$kelas')
                        ON DUPLICATE KEY UPDATE 
                        nama_lengkap = VALUES(nama_lengkap), 
                        jenis_kelamin = VALUES(jenis_kelamin), 
                        kelas = VALUES(kelas)
                    ";
                    
                    if (mysqli_query($koneksi, $query)) {
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
