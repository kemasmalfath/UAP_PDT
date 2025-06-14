<?php
// Start session for user authentication
session_start();
require_once 'config/database.php';
require_once 'models/Laporan.php';

// Initialize models
$db = new Database();
$conn = $db->getConnection();
$laporanModel = new Laporan($conn);

// Get filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;

// Get reports based on filters
$reports = $laporanModel->getReports($status, $page, $perPage);
$totalReports = $laporanModel->getTotalFilteredReports($status);
$totalPages = ceil($totalReports / $perPage);

// Get status counts for filter badges
$newCount = count($laporanModel->getReportsByStatus('Baru'));
$inProgressCount = count($laporanModel->getReportsByStatus('Ditangani'));
$completedCount = count($laporanModel->getReportsByStatus('Selesai'));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Laporan - FixItNow</title>
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
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600"><?= $_SESSION['user_name'] ?></span>
                        <a href="logout.php" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-light">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="bg-secondary text-white px-4 py-2 rounded-md hover:bg-secondary-light">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-12">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-primary">Daftar Laporan Kerusakan</h1>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="buat-laporan.php" class="bg-secondary text-white px-4 py-2 rounded-md hover:bg-secondary-light">Buat Laporan Baru</a>
            <?php endif; ?>
        </div>
        
        <!-- Filters -->
        <div class="bg-white p-4 rounded-md shadow-sm mb-6">
            <div class="flex flex-wrap gap-2">
                <a href="laporan.php" class="<?= $status === '' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?> px-4 py-2 rounded-md">
                    Semua
                </a>
                <a href="laporan.php?status=Baru" class="<?= $status === 'Baru' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?> px-4 py-2 rounded-md flex items-center">
                    Baru
                    <span class="ml-2 bg-yellow-500 text-white text-xs px-2 py-1 rounded-full"><?= $newCount ?></span>
                </a>
                <a href="laporan.php?status=Ditangani" class="<?= $status === 'Ditangani' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?> px-4 py-2 rounded-md flex items-center">
                    Ditangani
                    <span class="ml-2 bg-blue-500 text-white text-xs px-2 py-1 rounded-full"><?= $inProgressCount ?></span>
                </a>
                <a href="laporan.php?status=Selesai" class="<?= $status === 'Selesai' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?> px-4 py-2 rounded-md flex items-center">
                    Selesai
                    <span class="ml-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full"><?= $completedCount ?></span>
                </a>
            </div>
        </div>
        
        <!-- Reports List -->
        <div class="bg-white rounded-md shadow-sm overflow-hidden">
            <?php if (empty($reports)): ?>
                <div class="p-8 text-center">
                    <p class="text-gray-500">Tidak ada laporan yang ditemukan.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 divide-y">
                    <?php foreach($reports as $report): ?>
                        <div class="p-6">
                            <div class="flex flex-wrap justify-between mb-4">
                                <h3 class="text-lg font-bold text-primary"><?= $report['nama_fasilitas'] ?></h3>
                                <span class="<?= getStatusClass($report['status']) ?> px-3 py-1 rounded-full text-sm">
                                    <?= $report['status'] ?>
                                </span>
                            </div>
                            <p class="text-gray-600 mb-4"><?= $report['deskripsi_kerusakan'] ?></p>
                            <div class="flex flex-wrap justify-between text-sm text-gray-500">
                                <div>
                                    <span>Dilaporkan oleh: <?= $report['nama_pelapor'] ?></span>
                                    <span class="mx-2">•</span>
                                    <span>Tanggal: <?= date('d M Y', strtotime($report['tanggal_lapor'])) ?></span>
                                </div>
                                <div>
                                    <?php if($report['status'] === 'Ditangani'): ?>
                                        <span>Estimasi selesai: <?= date('d M Y', strtotime($report['estimasi_selesai'])) ?></span>
                                    <?php elseif($report['status'] === 'Selesai'): ?>
                                        <span>Selesai pada: <?= date('d M Y', strtotime($report['tanggal_selesai'])) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'teknisi' && $report['status'] === 'Baru'): ?>
                                <div class="mt-4">
                                    <a href="proses-laporan.php?id=<?= $report['id_laporan'] ?>" class="bg-secondary text-white px-4 py-2 rounded-md hover:bg-secondary-light">Proses Laporan</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="flex justify-center mt-8">
                <nav class="flex items-center space-x-1">
                    <?php if ($page > 1): ?>
                        <a href="?status=<?= $status ?>&page=<?= $page - 1 ?>" class="px-3 py-1 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-md">
                            &laquo; Prev
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="?status=<?= $status ?>&page=<?= $i ?>" class="px-3 py-1 <?= $i === $page ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?> rounded-md">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?status=<?= $status ?>&page=<?= $page + 1 ?>" class="px-3 py-1 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-md">
                            Next &raquo;
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>
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
    ?>
</body>
</html>
