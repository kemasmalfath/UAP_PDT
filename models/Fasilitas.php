<?php
class Fasilitas {
    private $conn;
    private $table_name = "fasilitas";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllFacilities() {
        $query = "
            SELECT 
                f.id_fasilitas, f.nama_fasilitas, f.lokasi, k.nama_kategori 
            FROM 
                " . $this->table_name . " f
            JOIN 
                kategori_fasilitas k ON f.id_kategori = k.id_kategori
            ORDER BY 
                f.nama_fasilitas ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFacilitiesByCategory($categoryId) {
        $query = "
            SELECT 
                f.id_fasilitas, f.nama_fasilitas, f.lokasi, k.nama_kategori 
            FROM 
                " . $this->table_name . " f
            JOIN 
                kategori_fasilitas k ON f.id_kategori = k.id_kategori
            WHERE 
                f.id_kategori = ?
            ORDER BY 
                f.nama_fasilitas ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllCategories() {
        $query = "SELECT * FROM kategori_fasilitas ORDER BY nama_kategori ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getFacilityById($id) {
        try {
            $query = "SELECT f.*, k.nama_kategori 
                      FROM fasilitas f 
                      JOIN kategori_fasilitas k ON f.id_kategori = k.id_kategori 
                      WHERE f.id_fasilitas = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error fetching facility: " . $e->getMessage());
        }
    }
}
?>