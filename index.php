<?php
// Start session for user authentication
session_start();
require_once 'config/database.php';
require_once 'models/Laporan.php';
require_once 'models/Fasilitas.php';
require_once 'models/User.php';

// Initialize models
$db = new Database();
$conn = $db->getConnection();
$laporanModel = new Laporan($conn);
$fasilitasModel = new Fasilitas($conn);
$userModel = new User($conn);

// Get latest reports for dashboard
$latestReports = $laporanModel->getLatestReports(5);
$totalReports = $laporanModel->getTotalReports();
$pendingReports = $laporanModel->getReportsByStatus('Baru');
$inProgressReports = $laporanModel->getReportsByStatus('Ditangani');
$completedReports = $laporanModel->getReportsByStatus('Selesai');

// Get facility categories
$categories = $fasilitasModel->getAllCategories();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FixItNow - Sistem Laporan & Penanganan Kerusakan Fasilitas Kampus</title>
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
                <a href="index.php" class="text-secondary font-medium">Beranda</a>
                <a href="laporan.php" class="text-gray-600 hover:text-secondary">Laporan</a>
                <a href="fasilitas.php" class="text-gray-600 hover:text-secondary">Fasilitas</a>
                <a href="statistik.php" class="text-gray-600 hover:text-secondary">Statistik</a>
            </div>
            <div>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="flex items-center space-x-4">
                        <a href="logout.php" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-light">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="bg-secondary text-white px-4 py-2 rounded-md hover:bg-secondary-light">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-primary text-white">
        <div class="container mx-auto px-4 py-16">
            <div class="max-w-2xl">
                <h1 class="text-3xl md:text-4xl font-bold mb-4">Laporkan Kerusakan Fasilitas Kampus dengan Mudah</h1>
                <p class="text-gray-300 mb-8">Sistem pelaporan dan penanganan kerusakan fasilitas kampus yang cepat, transparan, dan efisien.</p>
                <a href="buat-laporan.php" class="bg-secondary text-white px-6 py-3 rounded-md hover:bg-secondary-light inline-block">Buat Laporan</a>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-12">
        <div class="container mx-auto px-4">
            <h2 class="text-2xl font-bold text-primary mb-8 text-center">Statistik Laporan</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-secondary p-6 rounded-md text-white text-center">
                    <h3 class="text-xl font-bold mb-2">Total Laporan</h3>
                    <p class="text-3xl font-bold"><?= $totalReports ?></p>
                </div>
                <div class="bg-secondary p-6 rounded-md text-white text-center">
                    <h3 class="text-xl font-bold mb-2">Sedang Ditangani</h3>
                    <p class="text-3xl font-bold"><?= count($inProgressReports) ?></p>
                </div>
                <div class="bg-secondary p-6 rounded-md text-white text-center">
                    <h3 class="text-xl font-bold mb-2">Selesai Diperbaiki</h3>
                    <p class="text-3xl font-bold"><?= count($completedReports) ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Reports Section -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-bold text-primary">Laporan Terbaru</h2>
                <a href="laporan.php" class="text-secondary hover:underline">Lihat Semua</a>
            </div>
            
            <div class="grid grid-cols-1 gap-6">
                <?php foreach($latestReports as $report): ?>
                <div class="border border-gray-200 rounded-md overflow-hidden flex flex-col md:flex-row">
                    <div class="bg-primary w-full md:w-48 p-4 flex items-center justify-center">
                        <span class="text-white text-lg font-medium"><?= date('d M Y', strtotime($report['tanggal_lapor'])) ?></span>
                    </div>
                    <div class="p-6 flex-1">
                        <div class="flex flex-wrap justify-between mb-4">
                            <h3 class="text-lg font-bold text-primary"><?= $report['nama_fasilitas'] ?></h3>
                            <span class="<?= getStatusClass($report['status']) ?> px-3 py-1 rounded-full text-sm">
                                <?= $report['status'] ?>
                            </span>
                        </div>
                        <p class="text-gray-600 mb-4"><?= $report['deskripsi_kerusakan'] ?></p>
                        <div class="flex items-center text-sm text-gray-500">
                            <span>Dilaporkan oleh: <?= $report['nama_pelapor'] ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <div class="flex justify-center mt-8">
                <nav class="flex items-center space-x-1">
                    <a href="#" class="px-3 py-1 bg-primary text-white rounded-md">1</a>
                    <a href="#" class="px-3 py-1 text-gray-600 hover:bg-gray-100 rounded-md">2</a>
                    <a href="#" class="px-3 py-1 text-gray-600 hover:bg-gray-100 rounded-md">3</a>
                    <a href="#" class="px-3 py-1 text-gray-600 hover:bg-gray-100 rounded-md">4</a>
                    <a href="#" class="px-3 py-1 text-gray-600 hover:bg-gray-100 rounded-md">5</a>
                    <span class="px-3 py-1">...</span>
                    <a href="#" class="px-3 py-1 text-gray-600 hover:bg-gray-100 rounded-md">10</a>
                </nav>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="py-12">
        <div class="container mx-auto px-4">
            <h2 class="text-2xl font-bold text-primary mb-8 text-center">Kategori Fasilitas</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach($categories as $category): ?>
                <div class="bg-white p-6 rounded-md shadow-md text-center">
                    <div class="w-20 h-20 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-secondary text-2xl"><?= getCategoryIcon($category['nama_kategori']) ?></span>
                    </div>
                    <h3 class="text-xl font-bold text-primary mb-2"><?= $category['nama_kategori'] ?></h3>
                    <p class="text-gray-600"><?= $category['deskripsi'] ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Process Section -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-2xl font-bold text-primary mb-8 text-center">Proses Penanganan Laporan</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-primary p-6 rounded-md">
                    <div class="text-secondary mb-4">✓</div>
                    <h3 class="text-white text-xl font-bold mb-2">1. Pelaporan</h3>
                    <ul class="text-gray-300 space-y-2">
                        <li class="flex items-start">
                            <span class="text-secondary mr-2">•</span>
                            <span>Identifikasi kerusakan</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-secondary mr-2">•</span>
                            <span>Isi formulir laporan</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-secondary mr-2">•</span>
                            <span>Kirim laporan</span>
                        </li>
                    </ul>
                </div>
                <div class="bg-secondary p-6 rounded-md">
                    <div class="text-white mb-4">✓</div>
                    <h3 class="text-white text-xl font-bold mb-2">2. Penugasan</h3>
                    <ul class="text-white space-y-2">
                        <li class="flex items-start">
                            <span class="text-primary mr-2">•</span>
                            <span>Verifikasi laporan</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-primary mr-2">•</span>
                            <span>Penugasan teknisi</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-primary mr-2">•</span>
                            <span>Estimasi waktu</span>
                        </li>
                    </ul>
                </div>
                <div class="bg-primary p-6 rounded-md">
                    <div class="text-secondary mb-4">✓</div>
                    <h3 class="text-white text-xl font-bold mb-2">3. Penyelesaian</h3>
                    <ul class="text-gray-300 space-y-2">
                        <li class="flex items-start">
                            <span class="text-secondary mr-2">•</span>
                            <span>Perbaikan fasilitas</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-secondary mr-2">•</span>
                            <span>Verifikasi hasil</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-secondary mr-2">•</span>
                            <span>Penutupan laporan</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-primary text-white py-8">
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

    <?php
    // Helper function to get status class
    function getStatusClass($status) {
        switch($status) {
            case 'Baru':
                return 'bg-yellow-100 text-yellow-800';
            case 'Ditangani':
                return 'bg-blue-100 text-blue-800';
            case 'Selesai':
                return 'bg-green-100 text-green-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }

    // Helper function to get category icon
    function getCategoryIcon($category) {
        switch($category) {
            case 'AC':
                return '❄';
            case 'Listrik':
                return '⚡';
            case 'Plumbing':
                return '🚿';
            case 'Furniture':
                return '🪑';
            case 'IT':
                return '💻';
            default:
                return '🔧';
        }
    }
    ?>
</body>
</html>
