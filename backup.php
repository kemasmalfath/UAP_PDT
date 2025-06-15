<?php
session_start();

// Check if user is logged in and has role teknisi or admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['teknisi', 'admin'])) {
    header("Location: login.php?pesan=tidak_diizinkan");
    exit();
}

require_once 'config/database.php';

// Create backups directory if not exists
$backupDir = __DIR__ . '/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Database connection details from config/database.php class Database
$db = new Database();
$host = 'localhost'; // from config/database.php
$dbname = 'fixitnow_db';
$username = 'root';
$password = '';

// Filename for backup with timestamp
$backupFile = $backupDir . '/backup_' . date('Ymd_His') . '.sql';

// Command to execute mysqldump
// Adjust path to mysqldump if needed, assuming it's in PATH
$command = "mysqldump --user={$username} --password={$password} --host={$host} {$dbname} > " . escapeshellarg($backupFile);

// Execute the command
exec($command, $output, $return_var);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Backup Database - FixItNow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5">
    <h1>Backup Database</h1>
    <?php if ($return_var === 0): ?>
        <div class="alert alert-success">
            Backup berhasil dibuat: <a href="backups/<?= basename($backupFile) ?>" download><?= basename($backupFile) ?></a>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            Backup gagal dibuat. Pastikan mysqldump tersedia dan server mengizinkan eksekusi perintah.
        </div>
    <?php endif; ?>
    <a href="teknisi_dashboard.php" class="btn btn-primary mt-3">Kembali ke Dashboard</a>
</div>
</body>
</html>
