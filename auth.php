<?php
// auth.php

// Selalu mulai session jika belum ada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Fungsi untuk memeriksa apakah pengguna sudah login.
 * Jika belum, akan dilempar ke halaman login.
 */
function cek_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?pesan=belum_login");
        exit();
    }
}

/**
 * Fungsi untuk memeriksa apakah pengguna memiliki peran yang diizinkan.
 * @param array $peran_diizinkan Array peran yang boleh akses (misal: ['teknisi', 'admin']).
 */
function cek_peran(array $peran_diizinkan) {
    cek_login(); // Pastikan sudah login terlebih dahulu
    if (!isset($_SESSION['peran']) || !in_array($_SESSION['peran'], $peran_diizinkan)) {
        // Jika peran tidak ada dalam daftar yang diizinkan, tendang ke halaman utama.
        header("Location: index.php?pesan=tidak_diizinkan");
        exit();
    }
}
?>