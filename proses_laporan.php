<?php
// proses_laporan.php

// Panggil file konfigurasi database secara langsung
require_once 'config/database.php';

// Pastikan skrip ini hanya diakses melalui metode POST dari form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Jika diakses secara langsung, kembalikan ke halaman utama
    header('Location: index.php');
    exit();
}

// Ambil data yang dikirim dari form modal
$id_laporan = $_POST['id_laporan'] ?? null;
$status = $_POST['status'] ?? null;
$id_teknisi = $_POST['id_teknisi'] ?? null;
// Handle input tanggal: jika kosong, kirim NULL ke database
$estimasi_selesai = !empty($_POST['estimasi_selesai']) ? $_POST['estimasi_selesai'] : null;

// Validasi dasar: pastikan data penting tidak kosong
if (empty($id_laporan) || empty($status) || empty($id_teknisi)) {
    // Jika ada data yang kosong, hentikan proses dan berikan pesan error
    die("Error: Data tidak lengkap. Pastikan semua field yang wajib diisi.");
}

try {
    // Panggil Stored Procedure `proses_laporan` yang sudah ada di database Anda
    $sql = "CALL proses_laporan(:id_laporan, :id_teknisi, :status, :estimasi_selesai)";
    $stmt = $pdo->prepare($sql);

    // Bind parameter ke stored procedure untuk keamanan
    $stmt->bindParam(':id_laporan', $id_laporan, PDO::PARAM_INT);
    $stmt->bindParam(':id_teknisi', $id_teknisi, PDO::PARAM_INT);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':estimasi_selesai', $estimasi_selesai, PDO::PARAM_STR);

    // Eksekusi stored procedure
    $stmt->execute();

    // Jika berhasil, redirect kembali ke dashboard teknisi dengan pesan sukses
    header("Location: teknisi_dashboard.php?update=sukses");
    exit();

} catch (PDOException $e) {
    // Jika ada error dari sisi database, tampilkan pesan error yang jelas
    die("DATABASE ERROR: Gagal memproses laporan. " . $e->getMessage());
}

/*
--- CATATAN PENTING ---
Pastikan Stored Procedure `proses_laporan` sudah ada di database MySQL Anda.
Contoh SQL untuk membuatnya:

DELIMITER $$
CREATE PROCEDURE `proses_laporan`(
    IN `p_id_laporan` INT,
    IN `p_id_teknisi` INT,
    IN `p_status_baru` VARCHAR(50),
    IN `p_estimasi_selesai` DATE
)
BEGIN
    UPDATE laporan
    SET 
        status = p_status_baru,
        id_teknisi = p_id_teknisi,
        estimasi_selesai = p_estimasi_selesai
    WHERE id_laporan = p_id_laporan;
END$$
DELIMITER ;

*/
?>