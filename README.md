# FixItNow - Sistem Laporan dan Penanganan Kerusakan Fasilitas Kampus

Proyek ini adalah sistem berbasis web yang dibangun menggunakan PHP dan MySQL untuk mengelola laporan kerusakan fasilitas kampus secara efisien dan transparan. Sistem ini mengimplementasikan fitur penting seperti stored procedure, transaction, backup database, serta sistem login yang aman.

---

## 📌 Implementasi Fitur Utama

### 1. Backup Database
- **File:** `backup.php`
- **Deskripsi:** Script PHP yang menjalankan perintah `mysqldump` untuk membuat backup database secara otomatis. File backup disimpan di folder `backups/` dengan nama berformat timestamp.
- **Akses:** Terdapat tombol/link untuk memicu backup di navbar (`includes/header.php`) dan halaman dashboard teknisi (`teknisi_dashboard.php`).
- **Fungsi:** Memenuhi ketentuan backup database.

### 2. Stored Procedure
- **File:** `proses_laporan.php`
- **Deskripsi:** Memanggil stored procedure `proses_laporan` di database MySQL untuk memproses update laporan kerusakan (ubah status, menugaskan teknisi, dan estimasi selesai).
- **Fungsi:** Memenuhi ketentuan penggunaan procedure.

### 3. Transaction dan Trigger
- **Deskripsi:** Implementasi transaction dan trigger dilakukan di level database (MySQL). Stored procedure `proses_laporan` kemungkinan sudah mengandung transaksi dan trigger terkait untuk menjaga konsistensi data.
- **Catatan:** Jika diperlukan, dapat dibuat contoh function atau trigger tambahan.

### 4. Sistem Login
- **File:** `login.php`
- **Deskripsi:** Sistem login dengan pengecekan role user (admin, teknisi, user biasa) dan redirect ke halaman yang sesuai:
  - Admin diarahkan ke `index.php` (dashboard admin).
  - Teknisi diarahkan ke `teknisi_dashboard.php`.
- **Perbaikan:** Menghapus file `teknisi_login.php` dan menggabungkan login ke satu file `login.php`.
- **Session:** Konsistensi penggunaan variabel session `$_SESSION['role']` dan `$_SESSION['user_name']`.

### 5. Sanitasi Input dan Validasi
- **File:** `buat-laporan.php`
- **Deskripsi:** Validasi dan sanitasi input menggunakan `filter_input` dengan `FILTER_SANITIZE_SPECIAL_CHARS` untuk menghindari injeksi dan XSS.

### 6. Perbaikan Kode Lainnya
- **File:** `proses_laporan.php`
- **Deskripsi:** Memperbaiki inisialisasi koneksi PDO agar variabel `$pdo` tersedia dan dapat digunakan untuk eksekusi stored procedure.

---

## 📋 Cara Menjalankan dan Menguji

1. Pastikan MySQL dan PHP sudah terinstall dan `mysqldump` tersedia di PATH sistem.
2. Akses halaman login (`login.php`) dan masuk dengan akun admin atau teknisi.
3. Gunakan tombol/link "Backup Database" di navbar atau dashboard teknisi untuk membuat backup.
4. Buat laporan kerusakan melalui `buat-laporan.php`.
5. Proses laporan melalui dashboard teknisi yang memanggil stored procedure.

---

## ⚠️ Catatan Penting

- Pastikan stored procedure `proses_laporan` sudah dibuat di database MySQL.
- Backup database akan disimpan di folder `backups/` di root proyek.
- Sistem sudah menggunakan session yang konsisten untuk role dan nama user.

---

Dokumentasi ini dibuat untuk memenuhi ketentuan implementasi fitur pada proyek FixItNow.
