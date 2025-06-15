<?php
// Start session for user authentication
session_start();
require_once 'config/database.php';
require_once 'models/Laporan.php';
require_once 'models/Fasilitas.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=buat-laporan.php');
    exit;
}

// Initialize models
$db = new Database();
$conn = $db->getConnection();
$laporanModel = new Laporan($conn);
$fasilitasModel = new Fasilitas($conn);

// Get all facilities for dropdown
$facilities = $fasilitasModel->getAllFacilities();

// Process form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $idFasilitas = filter_input(INPUT_POST, 'id_fasilitas', FILTER_VALIDATE_INT);
    $deskripsi = trim(filter_input(INPUT_POST, 'deskripsi', FILTER_SANITIZE_SPECIAL_CHARS));
    
    if (!$idFasilitas) {
        $errors[] = "Pilih fasilitas yang valid";
    }
    
    if (empty($deskripsi)) {
        $errors[] = "Deskripsi kerusakan tidak boleh kosong";
    }
    
    // If no errors, create new report
    if (empty($errors)) {
        try {
            $idPelapor = $_SESSION['user_id'];
            $laporanModel->buatLaporanBaru($idPelapor, $idFasilitas, $deskripsi);
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
    <title>Buat Laporan Kerusakan - FixItNow</title>
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
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold text-primary mb-8">Buat Laporan Kerusakan</h1>
            
            <?php if ($success): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p>Laporan berhasil dibuat! Tim teknisi akan segera menindaklanjuti.</p>
                    <p class="mt-2">
                        <a href="laporan.php" class="text-green-700 font-medium underline">Lihat daftar laporan</a> atau 
                        <a href="buat-laporan.php" class="text-green-700 font-medium underline">buat laporan baru</a>
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
            
            <form action="buat-laporan.php" method="post" class="bg-white shadow-md rounded-md p-6">
                <div class="mb-6">
                    <label for="id_fasilitas" class="block text-gray-700 font-medium mb-2">Fasilitas</label>
                    <select id="id_fasilitas" name="id_fasilitas" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-secondary">
                        <option value="">-- Pilih Fasilitas --</option>
                        <?php foreach ($facilities as $facility): ?>
                            <option value="<?= $facility['id_fasilitas'] ?>"><?= $facility['nama_fasilitas'] ?> (<?= $facility['lokasi'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-6">
                    <label for="deskripsi" class="block text-gray-700 font-medium mb-2">Deskripsi Kerusakan</label>
                    <textarea id="deskripsi" name="deskripsi" rows="5" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-secondary" placeholder="Jelaskan detail kerusakan yang Anda temukan..."></textarea>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="bg-secondary text-white px-6 py-3 rounded-md hover:bg-secondary-light">Kirim Laporan</button>
                </div>
            </form>
            
            <div class="mt-8 bg-primary text-white p-6 rounded-md">
                <h2 class="text-xl font-bold mb-4">Panduan Pelaporan</h2>
                <ul class="space-y-2">
                    <li class="flex items-start">
                        <span class="text-secondary mr-2">1.</span>
                        <span>Pilih fasilitas yang mengalami kerusakan dari daftar.</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-secondary mr-2">2.</span>
                        <span>Berikan deskripsi detail tentang kerusakan yang terjadi.</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-secondary mr-2">3.</span>
                        <span>Laporan akan diproses dan ditindaklanjuti oleh tim teknisi.</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-secondary mr-2">4.</span>
                        <span>Anda dapat memantau status laporan di halaman "Laporan".</span>
                    </li>
                </ul>
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
</body>
</html>
