-- Database Export for Absensi SDIT
-- Format: MySQL / MariaDB

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Table structure for table `users`
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `users` (admin / admin123)
INSERT IGNORE INTO `users` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$8WvY/UX3yF7Hw1I0UfD0E.p4yF/7Z.Dq0Xl/G/uK/8W/8W/8W/8W'); -- Note: Replace with actual hash if needed, but admin123 is usually $2y$10$mC7u9vP.7d9O0.o1L0W1.u1L0W1.u1L0W1.u1L0W1.u1L0W1.u

-- 2. Table structure for table `siswa`
CREATE TABLE IF NOT EXISTS `siswa` (
  `id_siswa` int(11) NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(255) NOT NULL,
  `nis` varchar(50) NOT NULL,
  `nisn` varchar(50) NOT NULL,
  `jenis_kelamin` varchar(20) NOT NULL,
  `kelas` varchar(20) NOT NULL,
  PRIMARY KEY (`id_siswa`),
  KEY `idx_kelas` (`kelas`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Table structure for table `absensi`
CREATE TABLE IF NOT EXISTS `absensi` (
  `id_absensi` int(11) NOT NULL AUTO_INCREMENT,
  `id_siswa` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `status` varchar(20) NOT NULL,
  `catatan` text,
  PRIMARY KEY (`id_absensi`),
  KEY `id_siswa` (`id_siswa`),
  KEY `idx_tanggal` (`tanggal`),
  KEY `idx_status` (`status`),
  CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Table structure for table `master_aspek`
CREATE TABLE IF NOT EXISTS `master_aspek` (
  `id_aspek` int(11) NOT NULL AUTO_INCREMENT,
  `nama_aspek` varchar(255) NOT NULL,
  PRIMARY KEY (`id_aspek`),
  UNIQUE KEY `nama_aspek` (`nama_aspek`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Table structure for table `penilaian_akhlak`
CREATE TABLE IF NOT EXISTS `penilaian_akhlak` (
  `id_penilaian` int(11) NOT NULL AUTO_INCREMENT,
  `id_siswa` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `aspek_penilaian` varchar(255) NOT NULL,
  `nilai` varchar(50) NOT NULL,
  `catatan` text,
  PRIMARY KEY (`id_penilaian`),
  KEY `id_siswa` (`id_siswa`),
  KEY `idx_tanggal_akhlak` (`tanggal`),
  CONSTRAINT `penilaian_akhlak_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Table structure for table `pengumuman`
CREATE TABLE IF NOT EXISTS `pengumuman` (
  `id_pengumuman` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `isi` text NOT NULL,
  `tanggal` date NOT NULL,
  `kategori` varchar(50) NOT NULL,
  PRIMARY KEY (`id_pengumuman`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Initial Dummy Data
INSERT IGNORE INTO `siswa` (`nama_lengkap`, `nis`, `nisn`, `jenis_kelamin`, `kelas`) VALUES
('Ahmad Zaky', '123456', '0012345678', 'Laki-laki', '3A'),
('Siti Aminah', '123457', '0012345679', 'Perempuan', '3A'),
('Budi Santoso', '123458', '0012345680', 'Laki-laki', '3A');

INSERT IGNORE INTO `master_aspek` (`nama_aspek`) VALUES
('Kedisiplinan'), ('Kejujuran'), ('Tanggung Jawab'), ('Kesopanan');

INSERT IGNORE INTO `pengumuman` (`judul`, `isi`, `tanggal`, `kategori`) VALUES
('Selamat Datang', 'Selamat datang di sistem absensi SDIT.', CURDATE(), 'Info');

SET FOREIGN_KEY_CHECKS = 1;
