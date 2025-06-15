-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 15, 2025 at 02:03 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fixitnow_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `proses_laporan` (IN `p_laporan_id` INT, IN `p_teknisi_id` INT, IN `p_status_baru` VARCHAR(20), IN `p_estimasi` DATE)   BEGIN
    -- Start transaction
    START TRANSACTION;
    
    -- Update the report status, assign technician, and set estimated completion date
    UPDATE laporan
    SET status = p_status_baru,
        id_teknisi = p_teknisi_id,
        estimasi_selesai = p_estimasi
    WHERE id_laporan = p_laporan_id;
    
    -- Create notification for the reporter
    INSERT INTO notifikasi (id_laporan, pesan)
    SELECT p_laporan_id, CONCAT('Laporan Anda sedang ditangani oleh ', u.nama, '. Estimasi selesai: ', p_estimasi)
    FROM users u
    WHERE u.id_user = p_teknisi_id;
    
    -- Commit transaction
    COMMIT;
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `hitung_rata_rata_waktu_perbaikan` (`p_id_kategori` INT) RETURNS DECIMAL(10,2) DETERMINISTIC BEGIN
    DECLARE avg_time DECIMAL(10, 2);

    SELECT AVG(TIMESTAMPDIFF(HOUR, tanggal_lapor, tanggal_selesai))
    INTO avg_time
    FROM laporan l
    JOIN fasilitas f ON l.id_fasilitas = f.id_fasilitas
    WHERE f.id_kategori = p_id_kategori AND l.status = 'Selesai';

    RETURN IFNULL(avg_time, 0);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `fasilitas`
--

CREATE TABLE `fasilitas` (
  `id_fasilitas` int NOT NULL,
  `nama_fasilitas` varchar(100) NOT NULL,
  `id_kategori` int NOT NULL,
  `lokasi` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `fasilitas`
--

INSERT INTO `fasilitas` (`id_fasilitas`, `nama_fasilitas`, `id_kategori`, `lokasi`) VALUES
(1, 'AC Ruang 101', 1, 'Gedung A Lantai 1'),
(2, 'AC Ruang 201', 1, 'Gedung A Lantai 2'),
(3, 'Lampu Koridor', 2, 'Gedung B Lantai 1'),
(4, 'Stop Kontak Lab Komputer', 2, 'Gedung C Lantai 1'),
(5, 'Keran Toilet Pria', 3, 'Gedung A Lantai 1'),
(6, 'Kursi Auditorium', 4, 'Gedung D Lantai 2'),
(7, 'Proyektor Ruang 301', 5, 'Gedung B Lantai 3'),
(8, 'Komputer Lab 1', 5, 'Gedung C Lantai 1');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_fasilitas`
--

CREATE TABLE `kategori_fasilitas` (
  `id_kategori` int NOT NULL,
  `nama_kategori` varchar(50) NOT NULL,
  `deskripsi` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori_fasilitas`
--

INSERT INTO `kategori_fasilitas` (`id_kategori`, `nama_kategori`, `deskripsi`) VALUES
(1, 'AC', 'Air Conditioner dan sistem pendingin ruangan'),
(2, 'Listrik', 'Instalasi listrik, stop kontak, dan lampu'),
(3, 'Plumbing', 'Sistem air, keran, dan saluran pembuangan'),
(4, 'Furniture', 'Meja, kursi, lemari, dan perabotan lainnya'),
(5, 'IT', 'Komputer, proyektor, dan perangkat elektronik lainnya');

-- --------------------------------------------------------

--
-- Table structure for table `laporan`
--

CREATE TABLE `laporan` (
  `id_laporan` int NOT NULL,
  `id_pelapor` int NOT NULL,
  `id_fasilitas` int NOT NULL,
  `deskripsi_kerusakan` text NOT NULL,
  `status` enum('Baru','Ditangani','Selesai') NOT NULL DEFAULT 'Baru',
  `tanggal_lapor` datetime NOT NULL,
  `id_teknisi` int DEFAULT NULL,
  `estimasi_selesai` date DEFAULT NULL,
  `tanggal_selesai` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `laporan`
--

INSERT INTO `laporan` (`id_laporan`, `id_pelapor`, `id_fasilitas`, `deskripsi_kerusakan`, `status`, `tanggal_lapor`, `id_teknisi`, `estimasi_selesai`, `tanggal_selesai`) VALUES
(1, 4, 1, 'AC tidak dingin dan mengeluarkan bunyi berisik', 'Selesai', '2025-06-04 12:30:29', 2, '2025-06-06', '2025-06-07 12:30:29'),
(2, 5, 3, 'Lampu koridor berkedip-kedip dan terkadang mati', 'Ditangani', '2025-06-09 12:30:29', 3, '2025-06-16', NULL),
(3, 4, 7, 'Proyektor tidak menyala saat dihubungkan ke laptop', 'Baru', '2025-06-13 12:30:29', NULL, NULL, NULL),
(4, 5, 5, 'Keran toilet bocor', 'Baru', '2025-06-14 12:30:29', NULL, NULL, NULL),
(5, 6, 4, 'hujan', 'Selesai', '2025-06-14 20:22:18', 1, '2025-06-15', '2025-06-15 20:44:55'),
(6, 14, 1, 'coba', 'Ditangani', '2025-06-15 20:40:21', 1, '2025-06-15', NULL),
(7, 14, 8, 'kldka', 'Ditangani', '2025-06-15 20:41:48', 1, '2025-06-15', NULL);

--
-- Triggers `laporan`
--
DELIMITER $$
CREATE TRIGGER `update_tanggal_selesai` BEFORE UPDATE ON `laporan` FOR EACH ROW BEGIN
    -- Check if status is changing to 'Selesai' AND previous status was not 'Selesai'
    IF NEW.status = 'Selesai' AND OLD.status != 'Selesai' THEN
        -- Set tanggal_selesai to current timestamp
        SET NEW.tanggal_selesai = NOW();
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id_notifikasi` int NOT NULL,
  `id_laporan` int NOT NULL,
  `pesan` text NOT NULL,
  `dibaca` tinyint(1) NOT NULL DEFAULT '0',
  `tanggal_notifikasi` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notifikasi`
--

INSERT INTO `notifikasi` (`id_notifikasi`, `id_laporan`, `pesan`, `dibaca`, `tanggal_notifikasi`) VALUES
(1, 1, 'Laporan baru telah masuk!', 1, '2025-06-14 05:30:29'),
(2, 1, 'Laporan Anda sedang ditangani oleh Teknisi 1. Estimasi selesai: 2023-06-15', 1, '2025-06-14 05:30:29'),
(3, 1, 'Laporan Anda telah selesai ditangani.', 0, '2025-06-14 05:30:29'),
(4, 2, 'Laporan baru telah masuk!', 1, '2025-06-14 05:30:29'),
(5, 2, 'Laporan Anda sedang ditangani oleh Teknisi 2. Estimasi selesai: 2023-06-18', 0, '2025-06-14 05:30:29'),
(6, 3, 'Laporan baru telah masuk!', 0, '2025-06-14 05:30:29'),
(7, 4, 'Laporan baru telah masuk!', 0, '2025-06-14 05:30:29'),
(8, 5, 'Laporan baru telah masuk!', 0, '2025-06-14 13:22:18'),
(9, 6, 'Laporan baru telah masuk!', 0, '2025-06-15 13:40:21'),
(10, 7, 'Laporan baru telah masuk!', 0, '2025-06-15 13:41:48'),
(11, 7, 'Laporan Anda sedang ditangani oleh Admin. Estimasi selesai: 2025-06-15', 0, '2025-06-15 13:44:29'),
(12, 6, 'Laporan Anda sedang ditangani oleh Admin. Estimasi selesai: 2025-06-15', 0, '2025-06-15 13:44:39'),
(13, 5, 'Laporan Anda sedang ditangani oleh Admin. Estimasi selesai: 2025-06-15', 0, '2025-06-15 13:44:55');

-- --------------------------------------------------------

--
-- Table structure for table `teknisi`
--

CREATE TABLE `teknisi` (
  `id_teknisi` int NOT NULL,
  `id_user` int NOT NULL,
  `nama_teknisi` varchar(100) NOT NULL,
  `spesialisasi` varchar(50) DEFAULT NULL,
  `nomor_telepon` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `teknisi`
--

INSERT INTO `teknisi` (`id_teknisi`, `id_user`, `nama_teknisi`, `spesialisasi`, `nomor_telepon`, `created_at`) VALUES
(1, 12, 'Teknisi 3', 'Jaringan & IT', '081255556666', '2025-06-14 05:58:53');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','teknisi','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Admin', 'admin@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2025-06-14 05:30:29'),
(2, 'Teknisi 1', 'teknisi1@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teknisi', '2025-06-14 05:30:29'),
(3, 'Teknisi 2', 'teknisi2@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teknisi', '2025-06-14 05:30:29'),
(4, 'Mahasiswa 1', 'mahasiswa1@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '2025-06-14 05:30:29'),
(5, 'Dosen 1', 'dosen1@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '2025-06-14 05:30:29'),
(6, 'y', 'usera@user.com', '$2y$10$.flP8D6MUMUOXcD2HJgmSO8f66tXp9eO41UoEiVd1.aZB.LeXtIOe', 'user', '2025-06-14 05:32:07'),
(12, 'Teknisi 3', 'teknisi3@gmail.com', '$2y$10$8oHRNw22puupi3nf8/hJKOjGzVYwCak2H4hWdrebKCqRxS2voJlQO', 'teknisi', '2025-06-14 05:58:53'),
(13, 'user123', 'user123@gmail.com', '$2y$10$M83.TAnKLQ7A7OJ6Why51eKPNPoZTwpaEFbR/3XxcYuS9aWpwMaF.', 'user', '2025-06-15 12:31:19'),
(14, 'coba', 'coba@gmail.com', '$2y$10$8J4U5HJUFl0dNrBIYDyECe9KhVX3hee7qd9kORvCy6ZzNWcSBMp/C', 'teknisi', '2025-06-15 13:28:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fasilitas`
--
ALTER TABLE `fasilitas`
  ADD PRIMARY KEY (`id_fasilitas`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `kategori_fasilitas`
--
ALTER TABLE `kategori_fasilitas`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id_laporan`),
  ADD KEY `id_pelapor` (`id_pelapor`),
  ADD KEY `id_fasilitas` (`id_fasilitas`),
  ADD KEY `id_teknisi` (`id_teknisi`);

--
-- Indexes for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id_notifikasi`),
  ADD KEY `id_laporan` (`id_laporan`);

--
-- Indexes for table `teknisi`
--
ALTER TABLE `teknisi`
  ADD PRIMARY KEY (`id_teknisi`),
  ADD UNIQUE KEY `id_user_unique` (`id_user`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fasilitas`
--
ALTER TABLE `fasilitas`
  MODIFY `id_fasilitas` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `kategori_fasilitas`
--
ALTER TABLE `kategori_fasilitas`
  MODIFY `id_kategori` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id_laporan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id_notifikasi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `teknisi`
--
ALTER TABLE `teknisi`
  MODIFY `id_teknisi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `fasilitas`
--
ALTER TABLE `fasilitas`
  ADD CONSTRAINT `fasilitas_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori_fasilitas` (`id_kategori`);

--
-- Constraints for table `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`id_pelapor`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `laporan_ibfk_2` FOREIGN KEY (`id_fasilitas`) REFERENCES `fasilitas` (`id_fasilitas`),
  ADD CONSTRAINT `laporan_ibfk_3` FOREIGN KEY (`id_teknisi`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`id_laporan`) REFERENCES `laporan` (`id_laporan`);

--
-- Constraints for table `teknisi`
--
ALTER TABLE `teknisi`
  ADD CONSTRAINT `fk_teknisi_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
