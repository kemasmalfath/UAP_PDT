-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: fixitnow_db
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `fasilitas`
--

DROP TABLE IF EXISTS `fasilitas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fasilitas` (
  `id_fasilitas` int NOT NULL AUTO_INCREMENT,
  `nama_fasilitas` varchar(100) NOT NULL,
  `id_kategori` int NOT NULL,
  `lokasi` varchar(100) NOT NULL,
  PRIMARY KEY (`id_fasilitas`),
  KEY `id_kategori` (`id_kategori`),
  CONSTRAINT `fasilitas_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori_fasilitas` (`id_kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fasilitas`
--

LOCK TABLES `fasilitas` WRITE;
/*!40000 ALTER TABLE `fasilitas` DISABLE KEYS */;
INSERT INTO `fasilitas` VALUES (1,'AC Ruang 101',1,'Gedung A Lantai 1'),(2,'AC Ruang 201',1,'Gedung A Lantai 2'),(3,'Lampu Koridor',2,'Gedung B Lantai 1'),(4,'Stop Kontak Lab Komputer',2,'Gedung C Lantai 1'),(5,'Keran Toilet Pria',3,'Gedung A Lantai 1'),(6,'Kursi Auditorium',4,'Gedung D Lantai 2'),(7,'Proyektor Ruang 301',5,'Gedung B Lantai 3'),(8,'Komputer Lab 1',5,'Gedung C Lantai 1');
/*!40000 ALTER TABLE `fasilitas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kategori_fasilitas`
--

DROP TABLE IF EXISTS `kategori_fasilitas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kategori_fasilitas` (
  `id_kategori` int NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(50) NOT NULL,
  `deskripsi` text,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kategori_fasilitas`
--

LOCK TABLES `kategori_fasilitas` WRITE;
/*!40000 ALTER TABLE `kategori_fasilitas` DISABLE KEYS */;
INSERT INTO `kategori_fasilitas` VALUES (1,'AC','Air Conditioner dan sistem pendingin ruangan'),(2,'Listrik','Instalasi listrik, stop kontak, dan lampu'),(3,'Plumbing','Sistem air, keran, dan saluran pembuangan'),(4,'Furniture','Meja, kursi, lemari, dan perabotan lainnya'),(5,'IT','Komputer, proyektor, dan perangkat elektronik lainnya');
/*!40000 ALTER TABLE `kategori_fasilitas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `laporan`
--

DROP TABLE IF EXISTS `laporan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `laporan` (
  `id_laporan` int NOT NULL AUTO_INCREMENT,
  `id_pelapor` int NOT NULL,
  `id_fasilitas` int NOT NULL,
  `deskripsi_kerusakan` text NOT NULL,
  `status` enum('Baru','Ditangani','Selesai') NOT NULL DEFAULT 'Baru',
  `tanggal_lapor` datetime NOT NULL,
  `id_teknisi` int DEFAULT NULL,
  `estimasi_selesai` date DEFAULT NULL,
  `tanggal_selesai` datetime DEFAULT NULL,
  PRIMARY KEY (`id_laporan`),
  KEY `id_pelapor` (`id_pelapor`),
  KEY `id_fasilitas` (`id_fasilitas`),
  KEY `id_teknisi` (`id_teknisi`),
  CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`id_pelapor`) REFERENCES `users` (`id_user`),
  CONSTRAINT `laporan_ibfk_2` FOREIGN KEY (`id_fasilitas`) REFERENCES `fasilitas` (`id_fasilitas`),
  CONSTRAINT `laporan_ibfk_3` FOREIGN KEY (`id_teknisi`) REFERENCES `users` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `laporan`
--

LOCK TABLES `laporan` WRITE;
/*!40000 ALTER TABLE `laporan` DISABLE KEYS */;
INSERT INTO `laporan` VALUES (1,4,1,'AC tidak dingin dan mengeluarkan bunyi berisik','Selesai','2025-06-04 12:30:29',2,'2025-06-06','2025-06-07 12:30:29'),(2,5,3,'Lampu koridor berkedip-kedip dan terkadang mati','Ditangani','2025-06-09 12:30:29',3,'2025-06-16',NULL),(3,4,7,'Proyektor tidak menyala saat dihubungkan ke laptop','Baru','2025-06-13 12:30:29',NULL,NULL,NULL),(4,5,5,'Keran toilet bocor','Baru','2025-06-14 12:30:29',NULL,NULL,NULL),(5,6,4,'hujan','Baru','2025-06-14 20:22:18',NULL,NULL,NULL);
/*!40000 ALTER TABLE `laporan` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `update_tanggal_selesai` BEFORE UPDATE ON `laporan` FOR EACH ROW BEGIN
    -- Check if status is changing to 'Selesai' AND previous status was not 'Selesai'
    IF NEW.status = 'Selesai' AND OLD.status != 'Selesai' THEN
        -- Set tanggal_selesai to current timestamp
        SET NEW.tanggal_selesai = NOW();
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `notifikasi`
--

DROP TABLE IF EXISTS `notifikasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifikasi` (
  `id_notifikasi` int NOT NULL AUTO_INCREMENT,
  `id_laporan` int NOT NULL,
  `pesan` text NOT NULL,
  `dibaca` tinyint(1) NOT NULL DEFAULT '0',
  `tanggal_notifikasi` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_notifikasi`),
  KEY `id_laporan` (`id_laporan`),
  CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`id_laporan`) REFERENCES `laporan` (`id_laporan`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifikasi`
--

LOCK TABLES `notifikasi` WRITE;
/*!40000 ALTER TABLE `notifikasi` DISABLE KEYS */;
INSERT INTO `notifikasi` VALUES (1,1,'Laporan baru telah masuk!',1,'2025-06-14 05:30:29'),(2,1,'Laporan Anda sedang ditangani oleh Teknisi 1. Estimasi selesai: 2023-06-15',1,'2025-06-14 05:30:29'),(3,1,'Laporan Anda telah selesai ditangani.',0,'2025-06-14 05:30:29'),(4,2,'Laporan baru telah masuk!',1,'2025-06-14 05:30:29'),(5,2,'Laporan Anda sedang ditangani oleh Teknisi 2. Estimasi selesai: 2023-06-18',0,'2025-06-14 05:30:29'),(6,3,'Laporan baru telah masuk!',0,'2025-06-14 05:30:29'),(7,4,'Laporan baru telah masuk!',0,'2025-06-14 05:30:29'),(8,5,'Laporan baru telah masuk!',0,'2025-06-14 13:22:18');
/*!40000 ALTER TABLE `notifikasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teknisi`
--

DROP TABLE IF EXISTS `teknisi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teknisi` (
  `id_teknisi` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `nama_teknisi` varchar(100) NOT NULL,
  `spesialisasi` varchar(50) DEFAULT NULL,
  `nomor_telepon` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_teknisi`),
  UNIQUE KEY `id_user_unique` (`id_user`),
  CONSTRAINT `fk_teknisi_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teknisi`
--

LOCK TABLES `teknisi` WRITE;
/*!40000 ALTER TABLE `teknisi` DISABLE KEYS */;
INSERT INTO `teknisi` VALUES (1,12,'Teknisi 3','Jaringan & IT','081255556666','2025-06-14 05:58:53');
/*!40000 ALTER TABLE `teknisi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','teknisi','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin','admin@kampus.ac.id','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin','2025-06-14 05:30:29'),(2,'Teknisi 1','teknisi1@kampus.ac.id','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','teknisi','2025-06-14 05:30:29'),(3,'Teknisi 2','teknisi2@kampus.ac.id','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','teknisi','2025-06-14 05:30:29'),(4,'Mahasiswa 1','mahasiswa1@kampus.ac.id','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','user','2025-06-14 05:30:29'),(5,'Dosen 1','dosen1@kampus.ac.id','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','user','2025-06-14 05:30:29'),(6,'y','usera@user.com','$2y$10$.flP8D6MUMUOXcD2HJgmSO8f66tXp9eO41UoEiVd1.aZB.LeXtIOe','user','2025-06-14 05:32:07'),(12,'Teknisi 3','teknisi3@gmail.com','$2y$10$8oHRNw22puupi3nf8/hJKOjGzVYwCak2H4hWdrebKCqRxS2voJlQO','teknisi','2025-06-14 05:58:53'),(13,'user123','user123@gmail.com','$2y$10$M83.TAnKLQ7A7OJ6Why51eKPNPoZTwpaEFbR/3XxcYuS9aWpwMaF.','user','2025-06-15 12:31:19'),(14,'coba','coba@gmail.com','$2y$10$8J4U5HJUFl0dNrBIYDyECe9KhVX3hee7qd9kORvCy6ZzNWcSBMp/C','teknisi','2025-06-15 13:28:53');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-06-15 20:39:49
