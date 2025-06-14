<?php
// models/Laporan.php (Versi Final dengan Perbaikan Konsistensi)

class Laporan {
    protected $pdo;

    // Konstruktor sudah benar, menggunakan $pdo
    public function __construct(PDO $db_connection) {
        $this->pdo = $db_connection;
    }

    public function getAllLaporan() {
        $sql = "SELECT 
                    l.id_laporan, l.deskripsi_kerusakan, l.status, 
                    l.tanggal_lapor, l.id_teknisi, l.estimasi_selesai,
                    f.nama_fasilitas, f.lokasi,
                    u.nama AS nama_pelapor,
                    t.nama_teknisi AS teknisi_bertugas
                FROM laporan l
                JOIN fasilitas f ON l.id_fasilitas = f.id_fasilitas
                JOIN users u ON l.id_pelapor = u.id_user
                LEFT JOIN teknisi t ON l.id_teknisi = t.id_teknisi
                ORDER BY 
                    CASE l.status
                        WHEN 'Baru' THEN 1
                        WHEN 'Ditangani' THEN 2
                        WHEN 'Selesai' THEN 3
                        ELSE 4
                    END, l.tanggal_lapor DESC";
        
        $stmt = $this->pdo->query($sql); // Sudah benar menggunakan $this->pdo
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ... (Fungsi-fungsi di bawah ini sekarang sudah diperbaiki semua) ...

    public function buatLaporanBaru($idPelapor, $idFasilitas, $deskripsi) {
        try {
            $this->pdo->beginTransaction(); // DIUBAH dari $this->conn

            $stmtLaporan = $this->pdo->prepare( // DIUBAH dari $this->conn
                "INSERT INTO laporan (id_pelapor, id_fasilitas, deskripsi_kerusakan, status, tanggal_lapor) 
                 VALUES (?, ?, ?, 'Baru', NOW())"
            );
            $stmtLaporan->execute([$idPelapor, $idFasilitas, $deskripsi]);
            $laporanId = $this->pdo->lastInsertId(); // DIUBAH dari $this->conn

            $stmtNotifikasi = $this->pdo->prepare( // DIUBAH dari $this->conn
                "INSERT INTO notifikasi (id_laporan, pesan) 
                 VALUES (?, 'Laporan baru telah masuk!')"
            );
            $stmtNotifikasi->execute([$laporanId]);

            $this->pdo->commit(); // DIUBAH dari $this->conn
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack(); // DIUBAH dari $this->conn
            throw new Exception("Gagal membuat laporan: " . $e->getMessage());
        }
    }

    public function getLatestReports($limit = 5) {
        try {
            $query = "SELECT l.*, f.nama_fasilitas, u.nama as nama_pelapor 
                      FROM laporan l 
                      JOIN fasilitas f ON l.id_fasilitas = f.id_fasilitas 
                      JOIN users u ON l.id_pelapor = u.id_user 
                      ORDER BY l.tanggal_lapor DESC 
                      LIMIT ?";
            $stmt = $this->pdo->prepare($query); // DIUBAH dari $this->conn
            $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error fetching reports: " . $e->getMessage());
        }
    }

    public function getTotalReports() {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM laporan"); // DIUBAH
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new Exception("Error counting reports: " . $e->getMessage());
        }
    }

    public function getReportsByStatus($status) {
        try {
            $query = "SELECT l.*, f.nama_fasilitas, u.nama as nama_pelapor 
                      FROM laporan l 
                      JOIN fasilitas f ON l.id_fasilitas = f.id_fasilitas 
                      JOIN users u ON l.id_pelapor = u.id_user 
                      WHERE l.status = ?";
            $stmt = $this->pdo->prepare($query); // DIUBAH
            $stmt->execute([$status]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error fetching reports by status: " . $e->getMessage());
        }
    }
    
    public function getReportById($id) {
        try {
            $query = "SELECT l.*, f.nama_fasilitas, f.lokasi, u.nama as nama_pelapor 
                      FROM laporan l 
                      JOIN fasilitas f ON l.id_fasilitas = f.id_fasilitas 
                      JOIN users u ON l.id_pelapor = u.id_user 
                      WHERE l.id_laporan = ?";
            $stmt = $this->pdo->prepare($query); // DIUBAH
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error fetching report: " . $e->getMessage());
        }
    }
}
?>