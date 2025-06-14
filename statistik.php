<?php
// Start session for user authentication
session_start();
require_once 'config/database.php';
require_once 'models/Laporan.php';
require_once 'models/Fasilitas.php';

// Initialize models
$db = new Database();
$conn = $db->getConnection();
$laporanModel = new Laporan($conn);
$fasilitasModel = new Fasilitas($conn);

// Get statistics data
$totalReports = $laporanModel->getTotalReports();
$newReports = count($laporanModel->getReportsByStatus('Baru'));
$inProgressReports = count($laporanModel->getReportsByStatus('Ditangani'));
$completedReports = count($laporanModel->getReportsByStatus('Selesai'));

// Get categories for average repair time
$categories = $fasilitasModel->getAllCategories();
$avgRepairTimes = [];

foreach ($categories as $category) {
    $avgRepairTimes[$category['id_kategori']] = $laporanModel->getRataRataPerbaikan($category['id_kategori']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik - FixItNow</title>
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
                <a href="laporan.php" class="text-gray-600 hover:text-secondary">Laporan</a>
                <a href="fasilitas.php" class="text-gray-600 hover:text-secondary">Fasilitas</a>
                <a href="statistik.php" class="text-secondary font-medium">Statistik</a>
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
        <h1 class="text-3xl font-bold text-primary mb-8">Statistik Laporan Kerusakan</h1>
        
        <!-- Overview Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
            <div class="bg-white p-6 rounded-md shadow-sm">
                <h2 class="text-lg font-bold text-primary mb-2">Total Laporan</h2>
                <p class="text-4xl font-bold text-secondary"><?= $totalReports ?></p>
            </div>
            <div class="bg-white p-6 rounded-md shadow-sm">
                <h2 class="text-lg font-bold text-primary mb-2">Laporan Baru</h2>
                <p class="text-4xl font-bold text-yellow-500"><?= $newReports ?></p>
            </div>
            <div class="bg-white p-6 rounded-md shadow-sm">
                <h2 class="text-lg font-bold text-primary mb-2">Sedang Ditangani</h2>
                <p class="text-4xl font-bold text-blue-500"><?= $inProgressReports ?></p>
            </div>
            <div class="bg-white p-6 rounded-md shadow-sm">
                <h2 class="text-lg font-bold text-primary mb-2">Selesai</h2>
                <p class="text-4xl font-bold text-green-500"><?= $completedReports ?></p>
            </div>
        </div>
        
        <!-- Average Repair Time -->
        <div class="bg-white p-6 rounded-md shadow-sm mb-12">
            <h2 class="text-xl font-bold text-primary mb-6">Rata-rata Waktu Perbaikan per Kategori (Jam)</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-2 text-left">Kategori</th>
                            <th class="px-4 py-2 text-left">Rata-rata Waktu (Jam)</th>
                            <th class="px-4 py-2 text-left">Visualisasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr class="border-t">
                                <td class="px-4 py-3"><?= $category['nama_kategori'] ?></td>
                                <td class="px-4 py-3"><?= number_format($avgRepairTimes[$category['id_kategori']], 2) ?></td>
                                <td class="px-4 py-3">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <?php 
                                        // Calculate percentage (max 100 hours for scale)
                                        $percentage = min(100, ($avgRepairTimes[$category['id_kategori']] / 100) * 100);
                                        ?>
                                        <div class="bg-secondary h-2.5 rounded-full" style="width: <?= $percentage ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Status Distribution -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
            <div class="bg-white p-6 rounded-md shadow-sm">
                <h2 class="text-xl font-bold text-primary mb-6">Distribusi Status Laporan</h2>
                
                <div id="statusChart"></div>

            </div>
            
            <div class="bg-white p-6 rounded-md shadow-sm">
                <h2 class="text-xl font-bold text-primary mb-6">Performa Penanganan</h2>
                <div class="space-y-6">
                    <?php $total = $totalReports > 0 ? $totalReports : 1; // Menghindari pembagian dengan nol ?>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium">Tingkat Penyelesaian</span>
                            <span class="text-sm font-medium"><?= number_format(($completedReports / $total) * 100, 1) ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-green-500 h-2.5 rounded-full" style="width: <?= ($completedReports / $total) * 100 ?>%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium">Tingkat Penanganan</span>
                            <span class="text-sm font-medium"><?= number_format(($inProgressReports / $total) * 100, 1) ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-blue-500 h-2.5 rounded-full" style="width: <?= ($inProgressReports / $total) * 100 ?>%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium">Laporan Belum Ditangani</span>
                            <span class="text-sm font-medium"><?= number_format(($newReports / $total) * 100, 1) ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-yellow-500 h-2.5 rounded-full" style="width: <?= ($newReports / $total) * 100 ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
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

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    // 2. Siapkan opsi untuk grafik
    const statusChartOptions = {
        // Data Series: Jumlah laporan untuk tiap status
        series: [<?= $newReports ?>, <?= $inProgressReports ?>, <?= $completedReports ?>],
        
        // Label untuk tiap data series
        labels: ['Baru', 'Ditangani', 'Selesai'],
        
        // Tipe dan ukuran grafik
        chart: {
            type: 'donut',
            width: '100%',
            height: 350
        },
        
        // Warna untuk tiap segmen (sesuai legenda Anda)
        colors: ['#f59e0b', '#3b82f6', '#22c55e'], // Tailwind's yellow-500, blue-500, green-500
        
        // Pengaturan legenda
        legend: {
            position: 'bottom'
        },
        
        // Menampilkan persentase pada grafik
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return val.toFixed(1) + "%"
            }
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    // 3. Buat dan render grafik
    const statusChart = new ApexCharts(document.querySelector("#statusChart"), statusChartOptions);
    statusChart.render();
</script>
</body>
</html>
