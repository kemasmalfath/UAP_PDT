<?php
// Masukkan password yang diinginkan di sini
$password_untuk_teknisi_baru = 'teknisi123';

$hash = password_hash($password_untuk_teknisi_baru, PASSWORD_DEFAULT);

echo "Gunakan hash ini di query SQL Anda: <br><br>";
echo "<b>" . $hash . "</b>";
?>