<?php
// teknisi_login.php (Versi Final)

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (isset($_SESSION['user_id']) && in_array($_SESSION['role'], ['teknisi', 'admin'])) {
    header('Location: teknisi_dashboard.php');
    exit();
}

require_once 'config/database.php';
require_once 'models/User.php';

$errors = [];

$database = new Database();
$db_connection = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email) {
        $errors[] = "Format email tidak valid.";
    }
    if (empty($password)) {
        $errors[] = "Password wajib diisi.";
    }

    if (empty($errors)) {
        $userModel = new User($db_connection);
        
        // ======================= PERUBAHAN UTAMA DI SINI =======================
        // Memanggil method findByEmail() yang benar dari model User
        $user = $userModel->findByEmail($email);

        // Verifikasi password dan peran dilakukan di sini, bukan di dalam model
        if ($user && password_verify($password, $user['password']) && in_array($user['role'], ['teknisi', 'admin'])) {
        // =====================================================================
            
            // Jika semua verifikasi berhasil
            session_regenerate_id(true); 
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['nama_lengkap'] = $user['nama'];
            $_SESSION['role'] = $user['role'];

            header('Location: teknisi_dashboard.php');
            exit();
        } else {
            $errors[] = "Login gagal. Pastikan email, password, dan hak akses Anda benar.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Panel Teknisi - FixItNow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center mb-4">Login Panel Teknisi</h2>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php foreach ($errors as $error): ?>
                                    <?= htmlspecialchars($error) ?><br>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form action="teknisi_login.php" method="post">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email teknisi" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary fw-bold">Login</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>