<?php
// Start session for user authentication
session_start();
require_once 'config/database.php';
require_once 'models/Laporan.php';
require_once 'models/User.php';

// Check if user is logged in and is a technician
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teknisi') {
    header('Location: login.php');
    exit;
}

// Initialize models
$db = new Database();
$conn = $db->getConnection();
$laporanModel = new Laporan($conn);
$userModel = new User($conn);

// Get report ID from URL
$reportId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get report details
try {
    $report = $laporanModel->getReportById($reportId);
    
    // Check if report exists and is in 'Baru' status
    if (!$report || $report['status'] !== 'Baru') {
        header('Location: laporan.php');
        exit;
    }
} catch (Exception $e) {
    header('Location: laporan.php');
    exit;
}

// Get all technicians for assignment
$teknisi = $userModel->getAllTeknisi();

// Process form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $teknisiId = filter_input(INPUT_POST, 'teknisi_id', FILTER_VALIDATE_INT);
    $estimasiDate = $_POST['estimasi_date'] ?? '';
    
    if (!$teknisiId) {
        $errors[] = "Pilih teknisi yang valid";
    }
    
    if (empty($estimasiDate)) {
        $errors[] = "Estimasi tanggal selesai tidak boleh kosong";
    } else {
        // Validate date format
        $date = DateTime::createFromFormat('Y-m-d', $estimasiDate);
        if (!$date || $date->format('Y-m-d') !== $estimasiDate) {
            $errors[] = "Format tanggal tidak valid";
        }
    }
    
    // If no errors, process the report
    if (empty($errors)) {
        try {
            $laporanModel->prosesLaporan($reportId, $teknisiId, 'Ditangani', $estimasiDate);
            $success = true;
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Laporan - FixItNow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#0F2A3D',
                            light: '#1C3E56'
                        },
                        secondary: {
                            DEFAULT: '#1CD6B1',
                            light: '#3EEAC5'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <a href="index.php" class="text-primary font-bold text-xl">FixItNow</a>
            </div>
            <div class="hidden md:flex space-x-6">
                <a href="index.php" class="text-gray-600 hover:text-secondary">Beranda</a>
                <a href="laporan.php" class="text-secondary font-medium">Laporan</a>
                <a href="fasilitas.php" class="text-gray-600 hover:text-secondary">Fasilitas</a>
                <a href="statistik.php" class="text-gray-600 hover:text-secondary">Statistik</a>
            </div>
            <div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600"><?= $_SESSION['user_name'] ?></span>
                    <a href="logout.php" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-light">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold text-primary mb-8">Proses Laporan Kerusakan</h1>
            
            <?php if ($success): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p>Laporan berhasil diproses! Status laporan telah diubah menjadi "Ditangani".</p>
                    <p class="mt-2">
                        <a href="laporan.php" class="text-green-700 font-medium underline">Kembali ke daftar laporan</a>
                    </p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p class="font-bold">Terjadi kesalahan:</p>
                    <ul class="list-disc ml-5">
                        <?php foreach ($errors as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if (!$success): ?>
                <div class="bg-white shadow-md rounded-md p-6 mb-8">
                    <h2 class="text-xl font-bold text-primary mb-4">Detail Laporan</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <p class="text-sm text-gray-500">ID Laporan</p>
                            <p class="font-medium"><?= $report['id_laporan'] ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Tanggal Lapor</p>
                            <p class="font-medium"><?= date('d M Y H:i', strtotime($report['tanggal_lapor'])) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Pelapor</p>
                            <p class="font-medium"><?= $report['nama_pelapor'] ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Fasilitas</p>
                            <p class="font-medium"><?= $report['nama_fasilitas'] ?></p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-500">Lokasi</p>
                            <p class="font-medium"><?= $report['lokasi'] ?></p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-500">Deskripsi Kerusakan</p>
                            <p class="font-medium"><?= $report['deskripsi_kerusakan'] ?></p>
                        </div>
                    </div>
                </div>
                
                <form action="proses-laporan.php?id=<?= $reportId ?>" method="post" class="bg-white shadow-md rounded-md p-6">
                    <h2 class="text-xl font-bold text-primary mb-4">Penugasan Teknisi</h2>
                    
                    <div class="mb-6">
                        <label for="teknisi_id" class="block text-gray-700 font-medium mb-2">Teknisi yang Ditugaskan</label>
                        <select id="teknisi_id" name="teknisi_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-secondary" required>
                            <option value="">-- Pilih Teknisi --</option>
                            <?php foreach ($teknisi as $t): ?>
                                <option value="<?= $t['id_user'] ?>"><?= $t['nama'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-6">
                        <label for="estimasi_date" class="block text-gray-700 font-medium mb-2">Estimasi Tanggal Selesai</label>
                        <input type="date" id="estimasi_date" name="estimasi_date" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-secondary" required>
                    </div>
                    
                    <div class="flex justify-end">
                        <a href="laporan.php" class="bg-gray-300 text-gray-700 px-6 py-3 rounded-md hover:bg-gray-400 mr-4">Batal</a>
                        <button type="submit" class="bg-secondary text-white px-6 py-3 rounded-md hover:bg-secondary-light">Proses Laporan</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-primary text-white py-8 mt-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">FixItNow</h3>
                    <p class="text-gray-300 mb-4">Sistem laporan dan penanganan kerusakan fasilitas kampus yang efisien dan transparan.</p>
                </div>
                <div class="md:text-right">
                    <h3 class="text-xl font-bold mb-4">Kontak</h3>
                    <p class="text-gray-300">Email: support@fixitnow.kampus.ac.id</p>
                    <p class="text-gray-300">Telepon: (021) 1234-5678</p>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-4 text-center text-gray-400">
                <p>&copy; <?= date('Y') ?> FixItNow. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>
</body>
</html>
