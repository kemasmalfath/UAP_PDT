****FixItNow — Sistem Laporan & Penanganan Kerusakan Fasilitas Kampus****

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

- Peran & Fungsi:
    - Mengubah status sebuah laporan (misalnya dari 'Baru' menjadi 'Ditangani').
    - Menugaskan seorang teknisi (id_teknisi) sebagai penanggung jawab.
    - Menetapkan tanggal estimasi penyelesaian.
    - Memastikan semua pembaruan data terkait laporan dilakukan secara konsisten melalui satu panggilan.

- Implementasi di Database (SQL):
    Prosedur ini dibuat di MySQL menggunakan query berikut:

        SQL
    
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

- Implementasi di Aplikasi (PHP):
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

- Peran & Fungsi:
    - Untuk secara otomatis mengisi kolom tanggal_selesai dengan waktu saat ini, tepat ketika seorang teknisi mengubah status laporan menjadi 'Selesai'.
    - Ini menghilangkan kebutuhan aplikasi untuk mengirim data tanggal secara manual dan menjamin akurasi waktu penyelesaian.

- Implementasi di Database (SQL):
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

- Peran & Fungsi:
    - Digunakan dalam method buatLaporanBaru() di models/Laporan.php.
    - Menjamin bahwa saat sebuah laporan baru dibuat, data harus berhasil dimasukkan ke tabel laporan DAN tabel notifikasi secara bersamaan.
    - Jika salah satu gagal (misalnya, INSERT ke notifikasi error), maka INSERT ke tabel laporan juga akan dibatalkan (ROLLBACK). Ini mencegah adanya "data hantu" (laporan ada tapi notifikasinya tidak terkirim).

- Implementasi di Aplikasi (PHP):

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

**Backup Otomatis & Penjadwalan Tugas (Scheduler)**

Untuk menjamin keamanan data dari kerusakan atau kehilangan yang tidak terduga, serta untuk mengotomatisasi tugas-tugas rutin, sistem FixItNow dirancang untuk memiliki mekanisme backup dan penjadwalan.

1. Backup Database Mingguan
- Peran & Fungsi:
  Membuat salinan (dump) dari seluruh database fixitnow_db secara berkala (misalnya, setiap hari Minggu pukul 02:00 pagi). File backup ini adalah jaring pengaman utama jika terjadi kegagalan server atau kerusakan data.

- Implementasi di Aplikasi (PHP):
  Kita akan membuat sebuah skrip PHP sederhana yang menggunakan utilitas command-line mysqldump. Skrip ini bisa diletakkan di dalam folder scripts/.

  File: scripts/backup.php

        PHP
    
        <?php
        $dbHost = "localhost";
        $dbUser = "root";
        $dbPass = "";
        $dbName = "fixitnow_db";
        
        $backupPath = _DIR_ . "/../storage/backups/";
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0777, true);
        }
    
        $tanggal = date("Y-m-d_H-i-s");
        $namaFile = "fixitnow_backup_{$tanggal}.sql";
        $lokasiFileLengkap = $backupPath . $namaFile;
        
        $command = "\"C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe\" --user={$dbUser} --password={$dbPass} --host={$dbHost} {$dbName} > \"{$lokasiFileLengkap}\"";
        
        @exec($command, $output, $return_var);
        
        if ($return_var === 0) {
            echo "Backup database berhasil dibuat: {$namaFile}\n";
        } else {
            echo "Gagal membuat backup database.\n";
        }
        ?>

2. Penjadwalan Tugas (Task Scheduler)
- Peran & Fungsi:
  Menjalankan skrip (backup.php atau skrip notifikasi email) secara otomatis pada waktu yang telah ditentukan tanpa perlu intervensi manual.

- Implementasi (tergantung sistem operasi Anda):
    - Untuk Pengguna Windows (menggunakan Task Scheduler):
      - Buka Task Scheduler dari Start Menu.
      - Di panel kanan, klik "Create Basic Task...".
      - Beri nama tugas, misalnya "Backup Mingguan FixItNow".
      - Pilih Trigger, misalnya "Weekly", lalu atur hari dan jam (contoh: setiap hari Sunday, jam 2:00 AM).
      - Pilih Action, pilih "Start a program".
      - Di bagian "Program/script", cari lokasi php.exe Anda. Contoh: C:\laragon\bin\php\php-8.1.10\php.exe
      - Di bagian "Add arguments (optional)", masukkan path lengkap ke skrip backup Anda. Contoh: C:\laragon\www\fixitnow\scripts\backup.php
      - Selesaikan wizard. Task scheduler akan otomatis menjalankan skrip backup Anda setiap minggu.
    
    - Untuk Pengguna Linux/macOS (menggunakan Cron Job):
      - Buka terminal dan ketik crontab -e.
      - Tambahkan baris baru di bagian bawah file untuk menjalankan skrip.
      - Contoh untuk menjalankan backup setiap hari Minggu jam 2 pagi:
    
                Bash
                # Menit Jam Hari(bulan) Bulan Hari(minggu) Perintah
                0 2 * * 0 /usr/bin/php /var/www/fixitnow/scripts/backup.php
      
      - Simpan dan tutup file. Cron akan menjalankan tugas ini secara otomatis.
