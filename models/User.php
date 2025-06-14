<?php
class User {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function login($email, $password) {
        try {
            $query = "SELECT * FROM users WHERE email = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password
                if (password_verify($password, $user['password'])) {
                    return $user;
                }
            }
            
            return false;
        } catch (PDOException $e) {
            throw new Exception("Error during login: " . $e->getMessage());
        }
    }
    
    public function register($nama, $email, $password, $role = 'user') {
        try {
            // Check if email already exists
            $query = "SELECT * FROM users WHERE email = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                throw new Exception("Email sudah terdaftar");
            }
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $query = "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$nama, $email, $hashedPassword, $role]);
            
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Error during registration: " . $e->getMessage());
        }
    }
    
    public function getUserById($id) {
        try {
            $query = "SELECT id_user, nama, email, role FROM users WHERE id_user = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error fetching user: " . $e->getMessage());
        }
    }
    
    public function getAllTeknisi() {
        try {
            $query = "SELECT id_user, nama FROM users WHERE role = 'teknisi' ORDER BY nama ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error fetching technicians: " . $e->getMessage());
        }
    }
}
?>
