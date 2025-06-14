<?php
// Script untuk mengirim notifikasi email tentang laporan baru
// Dijalankan oleh task scheduler setiap jam

require_once '../config/database.php';

// Initialize database connection
$db = new Database();
$conn = $db->getConnection();

// Cari laporan yang masuk dalam 1 jam terakhir
$stmt = $conn->query(
    "SELECT l.id_laporan, l.deskripsi_kerusakan, f.nama_fasilitas, f.lokasi, u.nama as nama_pelapor 
     FROM laporan l 
     JOIN fasilitas f ON l.id_fasilitas = f.id_fasilitas 
     JOIN users u ON l.id_pelapor = u.id_user 
     WHERE l.status = 'Baru' AND l.tanggal_lapor >= NOW() - INTERVAL 1 HOUR"
);
$laporanBaru = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($laporanBaru) > 0) {
    // Buat body email
    $bodyEmail = "Ditemukan " . count($laporanBaru) . " laporan kerusakan baru yang perlu ditangani:\n\n";
    
    foreach ($laporanBaru as $laporan) {
        $bodyEmail .= "ID: " . $laporan['id_laporan'] . "\n";
        $bodyEmail .= "Fasilitas: " . $laporan['nama_fasilitas'] . "\n";
        $bodyEmail .= "Lokasi: " . $laporan['lokasi'] . "\n";
        $bodyEmail .= "Pelapor: " . $laporan['nama_pelapor'] . "\n";
        $bodyEmail .= "Deskripsi: " . $laporan['deskripsi_kerusakan'] . "\n\n";
    }
    
    $bodyEmail .= "Silakan login ke sistem FixItNow untuk menindaklanjuti laporan tersebut.\n";
    $bodyEmail .= "http://fixitnow.kampus.ac.id/laporan.php";
    
    // Kirim email ke kepala teknisi
    mail("teknisi@kampus.ac.id", "Notifikasi Laporan Kerusakan Baru", $bodyEmail);
    
    echo "Email notifikasi berhasil dikirim untuk " . count($laporanBaru) . " laporan baru.";
} else {
    echo "Tidak ada laporan baru dalam 1 jam terakhir.";
}
?>
