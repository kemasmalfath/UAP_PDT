<?php
// teknisi_dashboard.php (Versi Final yang Disesuaikan)

$page_title = 'Panel Teknisi'; 

// Panggil header. File ini sudah otomatis menjalankan session_start().
require_once 'includes/header.php';

// Memanggil file-file yang dibutuhkan
require_once 'config/database.php'; // Ini berisi class Database
require_once 'models/Laporan.php';   // Ini berisi class Laporan

// ======================= PERUBAHAN UTAMA ADA DI SINI =======================
// Mendapatkan koneksi database dengan cara yang BENAR sesuai file database.php Anda

// 1. Buat objek dari class Database
$database = new Database();
// 2. Panggil method getConnection() untuk mendapatkan koneksi PDO
$db_connection = $database->getConnection();
// =========================================================================

// --- KEAMANAN ---
// Blok ini sekarang menggunakan variabel sesi 'role' sesuai gambar database Anda
// Jika Anda ingin menonaktifkan sementara untuk development, beri komentar pada blok ini.
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['teknisi', 'admin'])) {
    header("Location: teknisi_login.php?pesan=tidak_diizinkan");
    exit();
}
// ----------------

// Inisialisasi variabel
$laporan_list = [];
$teknisi_list = [];

try {
    // 3. Berikan koneksi ($db_connection) ke model Laporan
    $laporanModel = new Laporan($db_connection);
    
    // Ambil semua data laporan dari database
    $laporan_list = $laporanModel->getAllLaporan();

    // 4. Gunakan koneksi ($db_connection) juga untuk query langsung
    $teknisi_stmt = $db_connection->query("SELECT id_teknisi, nama_teknisi FROM teknisi ORDER BY nama_teknisi ASC");
    $teknisi_list = $teknisi_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Tangani error jika koneksi atau query database gagal
    die("Error: Tidak dapat mengambil data dari database. " . $e->getMessage());
}
?>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h3>🛠️ Daftar Laporan Kerusakan Fasilitas</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Fasilitas & Lokasi</th>
                        <th>Deskripsi</th>
                        <th>Pelapor</th>
                        <th>Tanggal Lapor</th>
                        <th>Status</th>
                        <th>Teknisi Bertugas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($laporan_list)): ?>
                        <tr>
                            <td colspan="8" class="text-center p-4">Tidak ada laporan kerusakan untuk ditampilkan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($laporan_list as $laporan): ?>
                            <tr>
                                <td><strong>#<?= htmlspecialchars($laporan['id_laporan']) ?></strong></td>
                                <td>
                                    <?= htmlspecialchars($laporan['nama_fasilitas']) ?><br>
                                    <small class="text-muted"><?= htmlspecialchars($laporan['lokasi']) ?></small>
                                </td>
                                <td style="min-width: 250px;"><?= htmlspecialchars($laporan['deskripsi_kerusakan']) ?></td>
                                <td><?= htmlspecialchars($laporan['nama_pelapor']) ?></td>
                                <td><?= date('d M Y, H:i', strtotime($laporan['tanggal_lapor'])) ?></td>
                                <td>
                                    <?php
                                        $status = $laporan['status'];
                                        $badge_class = 'bg-secondary';
                                        if ($status == 'Baru') $badge_class = 'bg-danger';
                                        if ($status == 'Ditangani') $badge_class = 'bg-warning text-dark';
                                        if ($status == 'Selesai') $badge_class = 'bg-success';
                                    ?>
                                    <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($status) ?></span>
                                </td>
                                <td><?= htmlspecialchars($laporan['teknisi_bertugas'] ?? '<em>Belum Ada</em>') ?></td>
                                <td>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#prosesModal-<?= $laporan['id_laporan'] ?>">
                                        Proses
                                    </button>
                                </td>
                            </tr>

                            <div class="modal fade" id="prosesModal-<?= $laporan['id_laporan'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog"><div class="modal-content">
                                    <form action="proses_laporan.php" method="POST">
                                        <div class="modal-header"><h5 class="modal-title">Proses Laporan #<?= $laporan['id_laporan'] ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id_laporan" value="<?= $laporan['id_laporan'] ?>">
                                            <div class="mb-3"><p><strong>Fasilitas:</strong> <?= htmlspecialchars($laporan['nama_fasilitas']) ?></p></div>
                                            <div class="mb-3"><label class="form-label">Ubah Status</label><select name="status" class="form-select" required><option value="Ditangani">Ditangani</option><option value="Selesai">Selesai</option><option value="Ditolak">Ditolak</option></select></div>
                                            <div class="mb-3"><label class="form-label">Tugaskan Teknisi</label><select name="id_teknisi" class="form-select" required><option value="">Pilih Teknisi</option><?php foreach ($teknisi_list as $teknisi): ?><option value="<?= $teknisi['id_teknisi'] ?>" <?= ($laporan['id_teknisi'] == $teknisi['id_teknisi']) ? 'selected' : '' ?>><?= htmlspecialchars($teknisi['nama_teknisi']) ?></option><?php endforeach; ?></select></div>
                                            <div class="mb-3"><label class="form-label">Estimasi Selesai</label><input type="date" name="estimasi_selesai" class="form-control" value="<?= htmlspecialchars($laporan['estimasi_selesai'] ?? '') ?>"></div>
                                        </div>
                                        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-success">Simpan</button></div>
                                    </form>
                                </div></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
require_once 'includes/footer.php'; 
?>