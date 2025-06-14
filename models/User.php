<?php
// models/User.php (Versi Final yang Disesuaikan)

class User {
    // Menggunakan $pdo agar konsisten dengan file lain (login.php, dll)
    protected $pdo;

    // Mengubah parameter $db menjadi $pdo agar seragam
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * INI ADALAH PENGGANTI FUNGSI login().
     * Tugasnya lebih sederhana dan fokus: hanya mencari data user berdasarkan email.
     * Ini adalah method yang akan dipanggil oleh teknisi_login.php.
     *
     * @param string $email
     * @return array|false Mengembalikan data user atau false jika tidak ditemukan.
     */
    public function findByEmail($email) {
        try {
            // Query ini mencari user berdasarkan kolom 'email'
            $query = "SELECT * FROM users WHERE email = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$email]);
            
            return $stmt->fetch(); // Kembalikan data mentahnya
        } catch (PDOException $e) {
            throw new Exception("Error saat mencari user: " . $e->getMessage());
        }
    }

    /**
     * Fungsi register disesuaikan agar konsisten.
     * Menggunakan nama kolom 'nama' dan 'role' sesuai struktur database Anda.
     */
    public function register($nama, $email, $password, $role = 'user') {
        try {
            // Cek apakah email sudah ada
            $stmt = $this->pdo->prepare("SELECT id_user FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                throw new Exception("Email sudah terdaftar.");
            }
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Query INSERT menggunakan kolom 'nama' dan 'role'
            $stmt = $this->pdo->prepare(
                "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$nama, $email, $hashedPassword, $role]);
            
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Error saat registrasi: " . $e->getMessage());
        }
    }
    
    /**
     * Fungsi ini sudah OK, hanya memastikan nama kolomnya benar.
     */
    public function getUserById($id) {
        try {
            $query = "SELECT id_user, nama, email, role FROM users WHERE id_user = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Error mengambil data user: " . $e->getMessage());
        }
    }
    
    /**
     * Fungsi ini juga sudah OK, hanya memastikan nama kolomnya benar.
     */
    public function getAllTeknisi() {
        try {
            $query = "SELECT id_user, nama FROM users WHERE role = 'teknisi' ORDER BY nama ASC";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Error mengambil data teknisi: " . $e->getMessage());
        }
    }
}
?>  