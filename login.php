<?php
// Start session for user authentication
session_start();
require_once 'config/database.php';
require_once 'models/User.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Initialize models
$db = new Database();
$conn = $db->getConnection();
$userModel = new User($conn);

// Process form submission
$errors = [];
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    if (!$email) {
        $errors[] = "Email tidak valid";
    }
    
    if (empty($password)) {
        $errors[] = "Password tidak boleh kosong";
    }
    
    // If no errors, attempt login
    if (empty($errors)) {
        try {
            $user = $userModel->login($email, $password);
            if ($user) {
                // Set session variables
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['user_name'] = $user['nama'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirect to requested page or index
                header('Location: ' . $redirect);
                exit;
            } else {
                $errors[] = "Email atau password salah";
            }
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
    <title>Login - FixItNow</title>
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
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-md mx-auto">
            <h1 class="text-3xl font-bold text-primary mb-8 text-center">Login</h1>
            
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
            
            <form action="login.php?redirect=<?= urlencode($redirect) ?>" method="post" class="bg-white shadow-md rounded-md p-6">
                <div class="mb-6">
                    <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
                    <input type="email" id="email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-secondary" placeholder="email@example.com" required>
                </div>
                
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                    <input type="password" id="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-secondary" placeholder="••••••••" required>
                </div>
                
                <div class="flex justify-between items-center mb-6">
                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-secondary focus:ring-secondary border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-700">Ingat saya</label>
                    </div>
                    <a href="#" class="text-sm text-secondary hover:underline">Lupa password?</a>
                </div>
                
                <div class="flex justify-center">
                    <button type="submit" class="bg-secondary text-white px-6 py-3 rounded-md hover:bg-secondary-light w-full">Login</button>
                </div>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-gray-600">Belum punya akun? <a href="register.php" class="text-secondary hover:underline">Daftar sekarang</a></p>
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
