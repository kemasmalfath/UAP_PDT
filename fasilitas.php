<?php
// Start session for user authentication
session_start();
require_once 'config/database.php';
require_once 'models/Fasilitas.php';

// Initialize models
$db = new Database();
$conn = $db->getConnection();
$fasilitasModel = new Fasilitas($conn);

// Get all categories
$categories = $fasilitasModel->getAllCategories();

// Get facilities by category
$selectedCategory = isset($_GET['category']) ? (int)$_GET['category'] : null;
$facilities = $selectedCategory ? $fasilitasModel->getFacilitiesByCategory($selectedCategory) : $fasilitasModel->getAllFacilities();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fasilitas - FixItNow</title>
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
                <a href="fasilitas.php" class="text-secondary font-medium">Fasilitas</a>
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
        <h1 class="text-3xl font-bold text-primary mb-8">Fasilitas Kampus</h1>
        
        <!-- Category Filters -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-primary mb-4">Kategori</h2>
            <div class="flex flex-wrap gap-2">
                <a href="fasilitas.php" class="<?= !$selectedCategory ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?> px-4 py-2 rounded-md">
                    Semua
                </a>
                <?php foreach($categories as $category): ?>
                    <a href="fasilitas.php?category=<?= $category['id_kategori'] ?>" class="<?= $selectedCategory === $category['id_kategori'] ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?> px-4 py-2 rounded-md">
                        <?= $category['nama_kategori'] ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Facilities Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach($facilities as $facility): ?>
                <div class="bg-white rounded-md shadow-sm overflow-hidden">
                    <div class="bg-primary p-4">
                        <h3 class="text-white text-lg font-bold"><?= $facility['nama_fasilitas'] ?></h3>
                    </div>
                    <div class="p-6">
                        <div class="mb-4">
                            <span class="text-sm font-medium text-gray-500">Kategori:</span>
                            <span class="ml-2"><?= $facility['nama_kategori'] ?></span>
                        </div>
                        <div class="mb-4">
                            <span class="text-sm font-medium text-gray-500">Lokasi:</span>
                            <span class="ml-2"><?= $facility['lokasi'] ?></span>
                        </div>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <a href="buat-laporan.php?facility=<?= $facility['id_fasilitas'] ?>" class="bg-secondary text-white px-4 py-2 rounded-md hover:bg-secondary-light inline-block mt-2">
                                Laporkan Kerusakan
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if(empty($facilities)): ?>
            <div class="bg-white p-8 rounded-md shadow-sm text-center">
                <p class="text-gray-500">Tidak ada fasilitas yang ditemukan.</p>
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
</body>
</html>
