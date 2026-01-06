<?php
require_once __DIR__ . '/../config/database.php';

class Category {
    private $conn;
    private $table_name = "categories";

    public $category_id;
    public $category_name;
    public $description;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all categories
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY category_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get category by ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE category_id = :category_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category_id", $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Create new category
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET category_name=:category_name, description=:description";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->category_name = htmlspecialchars(strip_tags($this->category_name));
        $this->description = htmlspecialchars(strip_tags($this->description));

        // Bind values
        $stmt->bindParam(":category_name", $this->category_name);
        $stmt->bindParam(":description", $this->description);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Update category
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET category_name=:category_name, description=:description
                  WHERE category_id=:category_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->category_name = htmlspecialchars(strip_tags($this->category_name));
        $this->description = htmlspecialchars(strip_tags($this->description));

        // Bind values
        $stmt->bindParam(":category_name", $this->category_name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":category_id", $this->category_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete category
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE category_id = :category_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category_id", $this->category_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>

