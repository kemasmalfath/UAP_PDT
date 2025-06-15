<?php

class Laporan {
    protected $pdo;

    // Konstruktor menggunakan $pdo
    public function __construct(PDO $db_connection) {
        $this->pdo = $db_connection;
    }

    public function getReports($status = '', $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        $params = [];

        $sql = "SELECT l.*, f.nama_fasilitas, u.nama as nama_pelapor 
                FROM laporan l
                JOIN fasilitas f ON l.id_fasilitas = f.id_fasilitas
                JOIN users u ON l.id_pelapor = u.id_user";

        if ($status !== '') {
            $sql .= " WHERE l.status = ?";
            $params[] = $status;
        }

        $perPage = (int)$perPage;
        $offset = (int)$offset;

        $sql .= " ORDER BY l.tanggal_lapor DESC LIMIT $perPage OFFSET $offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalFilteredReports($status = '') {
        $sql = "SELECT COUNT(*) FROM laporan";
        $params = [];

        if ($status !== '') {
            $sql .= " WHERE status = ?";
            $params[] = $status;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn();
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

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buatLaporanBaru($idPelapor, $idFasilitas, $deskripsi) {
        try {
            $this->pdo->beginTransaction();

            $stmtLaporan = $this->pdo->prepare(
                "INSERT INTO laporan (id_pelapor, id_fasilitas, deskripsi_kerusakan, status, tanggal_lapor) 
                 VALUES (?, ?, ?, 'Baru', NOW())"
            );
            $stmtLaporan->execute([$idPelapor, $idFasilitas, $deskripsi]);
            $laporanId = $this->pdo->lastInsertId();

            $stmtNotifikasi = $this->pdo->prepare(
                "INSERT INTO notifikasi (id_laporan, pesan) 
                 VALUES (?, 'Laporan baru telah masuk!')"
            );
            $stmtNotifikasi->execute([$laporanId]);

            $this->pdo->commit(); 
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack(); 
            throw new Exception("Gagal membuat laporan: " . $e->getMessage());
        }
    }

    public function getLatestReports($limit = 5) {
        try {
            $limit = (int)$limit;
            $query = "SELECT l.*, f.nama_fasilitas, u.nama as nama_pelapor 
                      FROM laporan l 
                      JOIN fasilitas f ON l.id_fasilitas = f.id_fasilitas 
                      JOIN users u ON l.id_pelapor = u.id_user 
                      ORDER BY l.tanggal_lapor DESC 
                      LIMIT $limit";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error fetching reports: " . $e->getMessage());
        }
    }

    public function getTotalReports() {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM laporan"); 
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
            $stmt = $this->pdo->prepare($query); 
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
            $stmt = $this->pdo->prepare($query); 
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error fetching report: " . $e->getMessage());
        }
    }

    public function getRataRataPerbaikan($id_kategori) {
    $query = "SELECT AVG(TIMESTAMPDIFF(HOUR, l.tanggal_lapor, l.tanggal_selesai)) AS rata_rata_jam
              FROM laporan l
              JOIN fasilitas f ON l.id_fasilitas = f.id_fasilitas
              WHERE l.status = 'Selesai'
              AND f.id_kategori = :id_kategori
              AND l.tanggal_selesai IS NOT NULL";

    $stmt = $this->pdo->prepare($query);
    $stmt->bindParam(':id_kategori', $id_kategori);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row && $row['rata_rata_jam'] !== null ? floatval($row['rata_rata_jam']) : 0;
    }
}
?>