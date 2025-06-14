<?php
// teknisi_dashboard.php

// Atur judul halaman dinamis yang akan digunakan di header
$page_title = 'Panel Teknisi'; 

// Panggil header. File ini sudah berisi session_start(), auth check, koneksi db, dan navbar
require_once 'includes/header.php';

// --- KEAMANAN ---
// Lindungi halaman ini. Hanya peran 'teknisi' atau 'admin' yang boleh mengakses.
if (!isset($_SESSION['peran']) || ($_SESSION['peran'] !== 'teknisi' && $_SESSION['peran'] !== 'admin')) {
    // Jika peran tidak sesuai, tendang ke halaman utama dengan pesan error.
    header("Location: index.php?pesan=tidak_diizinkan");
    exit();
}

// Panggil model yang diperlukan untuk mengambil data dari database
require_once 'models/Laporan.php';

// Inisialisasi variabel
$laporan_list = [];
$teknisi_list = [];

try {
    // Buat instance dari model Laporan
    $laporanModel = new Laporan($pdo);
    
    // Ambil semua data laporan dari database
    $laporan_list = $laporanModel->getAllLaporan(); // Asumsi method ini ada di Laporan.php

    // Ambil daftar semua teknisi untuk dropdown penugasan
    $teknisi_stmt = $pdo->query("SELECT id_teknisi, nama_teknisi FROM teknisi ORDER BY nama_teknisi ASC");
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
                            <td colspan="8" class="text-center">Tidak ada laporan kerusakan untuk ditampilkan.</td>
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
                                        // Logika untuk warna badge status
                                        $status = $laporan['status'];
                                        $badge_class = 'bg-secondary'; // Default
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

                            <div class="modal fade" id="prosesModal-<?= $laporan['id_laporan'] ?>" tabindex="-1" aria-labelledby="modalLabel-<?= $laporan['id_laporan'] ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="proses_laporan.php" method="POST">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalLabel-<?= $laporan['id_laporan'] ?>">Proses Laporan #<?= htmlspecialchars($laporan['id_laporan']) ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="id_laporan" value="<?= $laporan['id_laporan'] ?>">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label"><strong>Fasilitas:</strong></label>
                                                    <p class="form-control-plaintext"><?= htmlspecialchars($laporan['nama_fasilitas']) ?> di <?= htmlspecialchars($laporan['lokasi']) ?></p>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="status-<?= $laporan['id_laporan'] ?>" class="form-label">Ubah Status Laporan</label>
                                                    <select name="status" id="status-<?= $laporan['id_laporan'] ?>" class="form-select" required>
                                                        <option value="Ditangani" <?= $laporan['status'] == 'Ditangani' ? 'selected' : '' ?>>Ditangani</option>
                                                        <option value="Selesai" <?= $laporan['status'] == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                                        <option value="Ditolak" <?= $laporan['status'] == 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="id_teknisi-<?= $laporan['id_laporan'] ?>" class="form-label">Tugaskan Teknisi</label>
                                                    <select name="id_teknisi" id="id_teknisi-<?= $laporan['id_laporan'] ?>" class="form-select" required>
                                                        <option value="">-- Pilih Teknisi --</option>
                                                        <?php foreach ($teknisi_list as $teknisi): ?>
                                                            <option value="<?= $teknisi['id_teknisi'] ?>" <?= $laporan['id_teknisi'] == $teknisi['id_teknisi'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($teknisi['nama_teknisi']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                 <div class="mb-3">
                                                    <label for="estimasi_selesai-<?= $laporan['id_laporan'] ?>" class="form-label">Estimasi Selesai (Opsional)</label>
                                                    <input type="date" name="estimasi_selesai" id="estimasi_selesai-<?= $laporan['id_laporan'] ?>" class="form-control" value="<?= htmlspecialchars($laporan['estimasi_selesai']) ?>">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
// Panggil footer. File ini sudah berisi tag penutup html, body, dan link JS
require_once 'includes/footer.php'; 
?>