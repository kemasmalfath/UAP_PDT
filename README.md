**//FixItNow — Sistem Laporan & Penanganan Kerusakan Fasilitas Kampus//**


**Fitur Utama**
- Otentikasi Berbasis Peran: Sistem membedakan hak akses antara user biasa (mahasiswa/dosen) dan teknisi/admin.
- Pelaporan Kerusakan: User dapat dengan mudah membuat laporan baru mengenai fasilitas yang rusak.
- Dashboard Terpusat:
- Bagi User: Melihat riwayat laporan yang pernah dibuat.
- Bagi Teknisi: Melihat semua laporan yang masuk, memproses laporan, dan menugaskan teknisi.
- Pemisahan Halaman Login: Alur login untuk user biasa dan teknisi dipisahkan untuk keamanan dan kemudahan penggunaan.
- Manajemen Laporan: Teknisi dapat mengubah status laporan (Baru, Ditangani, Selesai, Ditolak), memberikan estimasi penyelesaian, dan menugaskan teknisi spesifik.


**Teknologi yang digunakan**
- Backend: PHP
- Database: MySQL / MariaDB
- Frontend: HTML, CSS, JavaScript
- Framework CSS: Bootstrap 5
- Web Server: Apache (via Laragon/XAMPP)


**Stored Procedure: proses_laporan()**
Stored procedure ini bertindak sebagai "SOP Digital" atau satu-satunya gerbang untuk memproses sebuah laporan. Daripada membiarkan PHP melakukan beberapa query UPDATE secara terpisah, semua logika dirangkum dalam satu prosedur yang aman.

Peran & Fungsi:
- Mengubah status sebuah laporan (misalnya dari 'Baru' menjadi 'Ditangani').
- Menugaskan seorang teknisi (id_teknisi) sebagai penanggung jawab.
- Menetapkan tanggal estimasi penyelesaian.
- Memastikan semua pembaruan data terkait laporan dilakukan secara konsisten melalui satu panggilan.

Implementasi di Database (SQL):
Prosedur ini dibuat di MySQL menggunakan query berikut:

    DELIMITER $$
    CREATE PROCEDURE proses_laporan(
        IN p_id_laporan INT,
        IN p_id_teknisi INT,
        IN p_status_baru VARCHAR(50),
        IN p_estimasi_selesai DATE
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

Implementasi di Aplikasi (PHP):
Aplikasi memanggil prosedur ini dari file proses_laporan.php setelah teknisi mengirimkan form dari modal di dashboard.

PHP

    try {
        // Menyiapkan panggilan ke Stored Procedure
        $stmt = $db_connection->prepare("CALL proses_laporan(?, ?, ?, ?)");
    
        // Menjalankan procedure dengan data yang aman
        $stmt->execute([
            $laporanId,
            $teknisiId,
            $status,
            $estimasiDate
        ]);
    }

**Trigger: update_tanggal_selesai (Contoh Implementasi)**
Trigger adalah aksi otomatis yang dijalankan oleh database ketika sebuah event (seperti UPDATE atau INSERT) terjadi pada sebuah tabel.

Peran & Fungsi:

Untuk secara otomatis mengisi kolom tanggal_selesai dengan waktu saat ini, tepat ketika seorang teknisi mengubah status laporan menjadi 'Selesai'.
Ini menghilangkan kebutuhan aplikasi untuk mengirim data tanggal secara manual dan menjamin akurasi waktu penyelesaian.
Implementasi di Database (SQL):
Trigger ini akan "mendengarkan" setiap UPDATE pada tabel laporan.

SQL

    DELIMITER $$
    CREATE TRIGGER update_tanggal_selesai 
    BEFORE UPDATE ON laporan 
    FOR EACH ROW 
    BEGIN
        -- Cek jika status baru adalah 'Selesai' dan status lama BUKAN 'Selesai'
        IF NEW.status = 'Selesai' AND OLD.status != 'Selesai' THEN
            -- Atur tanggal_selesai ke waktu saat ini
            SET NEW.tanggal_selesai = NOW();
        END IF;
    END$$
    DELIMITER ;

Dengan adanya trigger ini, proses pengisian tanggal_selesai terjadi sepenuhnya di sisi database tanpa perlu campur tangan dari kode PHP.

**Transaction: buatLaporanBaru()**
Transaction memastikan bahwa serangkaian operasi database diperlakukan sebagai satu unit kerja tunggal. Entah semua berhasil, atau semua gagal (dibatalkan).

Peran & Fungsi:

Digunakan dalam method buatLaporanBaru() di models/Laporan.php.
Menjamin bahwa saat sebuah laporan baru dibuat, data harus berhasil dimasukkan ke tabel laporan DAN tabel notifikasi secara bersamaan.
Jika salah satu gagal (misalnya, INSERT ke notifikasi error), maka INSERT ke tabel laporan juga akan dibatalkan (ROLLBACK). Ini mencegah adanya "data hantu" (laporan ada tapi notifikasinya tidak terkirim).
Implementasi di Aplikasi (PHP):

PHP

    public function buatLaporanBaru($idPelapor, $idFasilitas, $deskripsi) {
        try {
            // Memulai sebuah transaksi
            $this->pdo->beginTransaction();
    
            // 1. Simpan data ke tabel 'laporan'
            $stmtLaporan = $this->pdo->prepare(...);
            $stmtLaporan->execute(...);
            $laporanId = $this->pdo->lastInsertId();
    
            // 2. Simpan data ke tabel 'notifikasi'
            $stmtNotifikasi = $this->pdo->prepare(...);
            $stmtNotifikasi->execute(...);
    
            // Jika semua query di atas berhasil, konfirmasi transaksi
            $this->pdo->commit();
            return true;
    
        } catch (PDOException $e) {
            // Jika ada satu saja yang gagal, batalkan semua perubahan
            $this->pdo->rollBack();
            throw new Exception("Gagal membuat laporan: " . $e->getMessage());
        }
    }
