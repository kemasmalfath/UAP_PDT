-- Create database
CREATE DATABASE IF NOT EXISTS fixitnow_db;
USE fixitnow_db;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'teknisi', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create kategori_fasilitas table
CREATE TABLE IF NOT EXISTS kategori_fasilitas (
    id_kategori INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50) NOT NULL,
    deskripsi TEXT
);

-- Create fasilitas table
CREATE TABLE IF NOT EXISTS fasilitas (
    id_fasilitas INT AUTO_INCREMENT PRIMARY KEY,
    nama_fasilitas VARCHAR(100) NOT NULL,
    id_kategori INT NOT NULL,
    lokasi VARCHAR(100) NOT NULL,
    FOREIGN KEY (id_kategori) REFERENCES kategori_fasilitas(id_kategori)
);

-- Create laporan table
CREATE TABLE IF NOT EXISTS laporan (
    id_laporan INT AUTO_INCREMENT PRIMARY KEY,
    id_pelapor INT NOT NULL,
    id_fasilitas INT NOT NULL,
    deskripsi_kerusakan TEXT NOT NULL,
    status ENUM('Baru', 'Ditangani', 'Selesai') NOT NULL DEFAULT 'Baru',
    tanggal_lapor DATETIME NOT NULL,
    id_teknisi INT NULL,
    estimasi_selesai DATE NULL,
    tanggal_selesai DATETIME NULL,
    FOREIGN KEY (id_pelapor) REFERENCES users(id_user),
    FOREIGN KEY (id_fasilitas) REFERENCES fasilitas(id_fasilitas),
    FOREIGN KEY (id_teknisi) REFERENCES users(id_user)
);

-- Create notifikasi table
CREATE TABLE IF NOT EXISTS notifikasi (
    id_notifikasi INT AUTO_INCREMENT PRIMARY KEY,
    id_laporan INT NOT NULL,
    pesan TEXT NOT NULL,
    dibaca BOOLEAN NOT NULL DEFAULT FALSE,
    tanggal_notifikasi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_laporan) REFERENCES laporan(id_laporan)
);

-- Create trigger to update tanggal_selesai when status changes to 'Selesai'
DELIMITER //
CREATE TRIGGER update_tanggal_selesai
BEFORE UPDATE ON laporan
FOR EACH ROW
BEGIN
    -- Check if status is changing to 'Selesai' AND previous status was not 'Selesai'
    IF NEW.status = 'Selesai' AND OLD.status != 'Selesai' THEN
        -- Set tanggal_selesai to current timestamp
        SET NEW.tanggal_selesai = NOW();
    END IF;
END//
DELIMITER ;

-- Create stored procedure for processing reports
DELIMITER //
CREATE PROCEDURE proses_laporan(
    IN p_laporan_id INT,
    IN p_teknisi_id INT,
    IN p_status_baru VARCHAR(20),
    IN p_estimasi DATE
)
BEGIN
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
END//
DELIMITER ;

-- Create stored function to calculate average repair time by category
DELIMITER //
CREATE FUNCTION hitung_rata_rata_waktu_perbaikan(p_id_kategori INT)
RETURNS DECIMAL(10, 2)
DETERMINISTIC
BEGIN
    DECLARE avg_time DECIMAL(10, 2);

    SELECT AVG(TIMESTAMPDIFF(HOUR, tanggal_lapor, tanggal_selesai))
    INTO avg_time
    FROM laporan l
    JOIN fasilitas f ON l.id_fasilitas = f.id_fasilitas
    WHERE f.id_kategori = p_id_kategori AND l.status = 'Selesai';

    RETURN IFNULL(avg_time, 0);
END//
DELIMITER ;

-- Insert sample data
-- Insert users
INSERT INTO users (nama, email, password, role) VALUES
('Admin', 'admin@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'), -- password: password
('Teknisi 1', 'teknisi1@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teknisi'),
('Teknisi 2', 'teknisi2@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teknisi'),
('Mahasiswa 1', 'mahasiswa1@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Dosen 1', 'dosen1@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Insert categories
INSERT INTO kategori_fasilitas (nama_kategori, deskripsi) VALUES
('AC', 'Air Conditioner dan sistem pendingin ruangan'),
('Listrik', 'Instalasi listrik, stop kontak, dan lampu'),
('Plumbing', 'Sistem air, keran, dan saluran pembuangan'),
('Furniture', 'Meja, kursi, lemari, dan perabotan lainnya'),
('IT', 'Komputer, proyektor, dan perangkat elektronik lainnya');

-- Insert facilities
INSERT INTO fasilitas (nama_fasilitas, id_kategori, lokasi) VALUES
('AC Ruang 101', 1, 'Gedung A Lantai 1'),
('AC Ruang 201', 1, 'Gedung A Lantai 2'),
('Lampu Koridor', 2, 'Gedung B Lantai 1'),
('Stop Kontak Lab Komputer', 2, 'Gedung C Lantai 1'),
('Keran Toilet Pria', 3, 'Gedung A Lantai 1'),
('Kursi Auditorium', 4, 'Gedung D Lantai 2'),
('Proyektor Ruang 301', 5, 'Gedung B Lantai 3'),
('Komputer Lab 1', 5, 'Gedung C Lantai 1');

-- Insert sample reports
INSERT INTO laporan (id_pelapor, id_fasilitas, deskripsi_kerusakan, status, tanggal_lapor, id_teknisi, estimasi_selesai, tanggal_selesai) VALUES
(4, 1, 'AC tidak dingin dan mengeluarkan bunyi berisik', 'Selesai', DATE_SUB(NOW(), INTERVAL 10 DAY), 2, DATE_SUB(NOW(), INTERVAL 8 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY)),
(5, 3, 'Lampu koridor berkedip-kedip dan terkadang mati', 'Ditangani', DATE_SUB(NOW(), INTERVAL 5 DAY), 3, DATE_ADD(NOW(), INTERVAL 2 DAY), NULL),
(4, 7, 'Proyektor tidak menyala saat dihubungkan ke laptop', 'Baru', DATE_SUB(NOW(), INTERVAL 1 DAY), NULL, NULL, NULL),
(5, 5, 'Keran toilet bocor', 'Baru', NOW(), NULL, NULL, NULL);

-- Insert notifications
INSERT INTO notifikasi (id_laporan, pesan, dibaca) VALUES
(1, 'Laporan baru telah masuk!', TRUE),
(1, 'Laporan Anda sedang ditangani oleh Teknisi 1. Estimasi selesai: 2023-06-15', TRUE),
(1, 'Laporan Anda telah selesai ditangani.', FALSE),
(2, 'Laporan baru telah masuk!', TRUE),
(2, 'Laporan Anda sedang ditangani oleh Teknisi 2. Estimasi selesai: 2023-06-18', FALSE),
(3, 'Laporan baru telah masuk!', FALSE),
(4, 'Laporan baru telah masuk!', FALSE);
