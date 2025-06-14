<?php
class Laporan {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function buatLaporanBaru($idPelapor, $idFasilitas, $deskripsi) {
        try {
            // Memulai transaksi untuk menjamin atomicity
            // Laporan dan notifikasi harus dibuat bersamaan atau tidak sama sekali.
            $this->conn->beginTransaction();

            // 1. Simpan data laporan kerusakan
            $stmtLaporan = $this->conn->prepare(
                "INSERT INTO laporan (id_pelapor, id_fasilitas, deskripsi_kerusakan, status, tanggal_lapor) 
                 VALUES (?, ?, ?, 'Baru', NOW())"
            );
            $stmtLaporan->execute([$idPelapor, $idFasilitas, $deskripsi]);
            $laporanId = $this->conn->lastInsertId();

            // 2. Buat notifikasi untuk tim teknisi
            $stmtNotifikasi = $this->conn->prepare(
                "INSERT INTO notifikasi (id_laporan, pesan) 
                 VALUES (?, 'Laporan baru telah masuk!')"
            );
            $stmtNotifikasi->execute([$laporanId]);

            // Jika semua berhasil, simpan perubahan secara permanen
            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            // Jika salah satu gagal, batalkan semua perubahan
            $this->conn->rollBack();
            throw new Exception("Gagal membuat laporan: " . $e->getMessage());
        }
    }
    
/*************  ✨ Windsurf Command ⭐  *************/
    /**
     * Retrieve the latest reports from the database.
     *
     * This function fetches a specified number of the most recently reported issues,
     * including details about the facility and the reporter.
     *
     * @param int $limit The maximum number of reports to retrieve. Defaults to 5.
     * @return array An associative array of the latest reports, including details
     *               such as the report, facility name, and reporter name.
     * @throws Exception If there is an error fetching the reports from the database.
     */

/*******  24432ab2-fae1-4cf7-91da-63cfb8472c03  *******/
    public function getLatestReports($limit = 5) {
        try {
            $query = "SELECT l.*, f.nama_fasilitas, u.nama as nama_pelapor 
                      FROM laporan l 
                      JOIN fasilitas f ON l.id_fasilitas = f.id_fasilitas 
                      JOIN users u ON l.id_pelapor = u.id_user 
                      ORDER BY l.tanggal_lapor DESC 
                      LIMIT ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error fetching reports: " . $e->getMessage());
        }
    }
    
    public function getTotalReports() {
        try {
            $query = "SELECT COUNT(*) as total FROM laporan";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
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
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$status]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error fetching reports by status: " . $e->getMessage());
        }
    }
    
    public function getReports($status = '', $page = 1, $perPage = 10) {
        try {
            $offset = ($page - 1) * $perPage;
            
            $query = "SELECT l.*, f.nama_fasilitas, u.nama as nama_pelapor 
                      FROM laporan l 
                      JOIN fasilitas f ON l.id_fasilitas = f.id_fasilitas 
                      JOIN users u ON l.id_pelapor = u.id_user";
            
            if (!empty($status)) {
                $query .= " WHERE l.status = :status";
            }
            
            $query .= " ORDER BY l.tanggal_lapor DESC LIMIT :offset, :perPage";
            
            $stmt = $this->conn->prepare($query);
            
            if (!empty($status)) {
                $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            }
            
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindParam(':perPage', $perPage, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error fetching reports: " . $e->getMessage());
        }
    }
    
    public function getTotalFilteredReports($status = '') {
        try {
            $query = "SELECT COUNT(*) as total FROM laporan";
            
            if (!empty($status)) {
                $query .= " WHERE status = :status";
            }
            
            $stmt = $this->conn->prepare($query);
            
            if (!empty($status)) {
                $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch (PDOException $e) {
            throw new Exception("Error counting filtered reports: " . $e->getMessage());
        }
    }
    
    public function getReportById($id) {
        try {
            $query = "SELECT l.*, f.nama_fasilitas, f.lokasi, u.nama as nama_pelapor 
                      FROM laporan l 
                      JOIN fasilitas f ON l.id_fasilitas = f.id_fasilitas 
                      JOIN users u ON l.id_pelapor = u.id_user 
                      WHERE l.id_laporan = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error fetching report: " . $e->getMessage());
        }
    }
    
    public function prosesLaporan($laporanId, $teknisiId, $status, $estimasiDate) {
        try {
            // Memanggil stored procedure untuk memproses laporan
            $stmt = $this->conn->prepare("CALL proses_laporan(?, ?, ?, ?)");
            $stmt->execute([
                $laporanId,
                $teknisiId,
                $status,
                $estimasiDate
            ]);
            return true;
        } catch (PDOException $e) {
            throw new Exception("Error processing report: " . $e->getMessage());
        }
    }
    
    public function getRataRataPerbaikan($kategoriId) {
        try {
            $stmt = $this->conn->prepare("SELECT hitung_rata_rata_waktu_perbaikan(?) AS avg_hours");
            $stmt->execute([$kategoriId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['avg_hours'];
        } catch (PDOException $e) {
            throw new Exception("Error calculating average repair time: " . $e->getMessage());
        }
    }
}
?>
