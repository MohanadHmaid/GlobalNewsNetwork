<?php
require_once __DIR__ . '/../config/database.php';


class Article {
    private $conn;
    private $table_name = "articles";

    public $article_id;
    public $title;
    public $content;
    public $summary;
    public $image_url;
    public $category_id;
    public $author_id;
    public $published_date;
    public $is_featured;
    public $is_breaking;
    public $views;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new article
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET title=:title, content=:content, summary=:summary, 
                      image_url=:image_url, category_id=:category_id, 
                      author_id=:author_id, is_featured=:is_featured, 
                      is_breaking=:is_breaking";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->content = htmlspecialchars(strip_tags($this->content));
        $this->summary = htmlspecialchars(strip_tags($this->summary));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));

        // Bind values
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":summary", $this->summary);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":author_id", $this->author_id);
        $stmt->bindParam(":is_featured", $this->is_featured);
        $stmt->bindParam(":is_breaking", $this->is_breaking);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get all articles with pagination
    public function getAll($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT a.*, c.category_name, u.username as author_name 
                  FROM " . $this->table_name . " a
                  LEFT JOIN categories c ON a.category_id = c.category_id
                  LEFT JOIN users u ON a.author_id = u.user_id
                  ORDER BY a.published_date DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Get featured articles
    public function getFeatured($limit = 5) {
        $query = "SELECT a.*, c.category_name, u.username as author_name 
                  FROM " . $this->table_name . " a
                  LEFT JOIN categories c ON a.category_id = c.category_id
                  LEFT JOIN users u ON a.author_id = u.user_id
                  WHERE a.is_featured = 1
                  ORDER BY a.published_date DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Get breaking news
    public function getBreaking($limit = 3) {
        $query = "SELECT a.*, c.category_name, u.username as author_name 
                  FROM " . $this->table_name . " a
                  LEFT JOIN categories c ON a.category_id = c.category_id
                  LEFT JOIN users u ON a.author_id = u.user_id
                  WHERE a.is_breaking = 1
                  ORDER BY a.published_date DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Get articles by category
    public function getByCategory($category_id, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT a.*, c.category_name, u.username as author_name 
                  FROM " . $this->table_name . " a
                  LEFT JOIN categories c ON a.category_id = c.category_id
                  LEFT JOIN users u ON a.author_id = u.user_id
                  WHERE a.category_id = :category_id
                  ORDER BY a.published_date DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Search articles
    public function search($keyword, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $keyword = "%{$keyword}%";
        
        $query = "SELECT a.*, c.category_name, u.username as author_name 
                  FROM " . $this->table_name . " a
                  LEFT JOIN categories c ON a.category_id = c.category_id
                  LEFT JOIN users u ON a.author_id = u.user_id
                  WHERE a.title LIKE :keyword OR a.content LIKE :keyword OR a.summary LIKE :keyword
                  ORDER BY a.published_date DESC
                  LIMIT $limit OFFSET $offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":keyword", $keyword);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Get single article by ID
    public function getById($id) {
        $query = "SELECT a.*, c.category_name, u.username as author_name 
                  FROM " . $this->table_name . " a
                  LEFT JOIN categories c ON a.category_id = c.category_id
                  LEFT JOIN users u ON a.author_id = u.user_id
                  WHERE a.article_id = :article_id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":article_id", $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    // Update article
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET title=:title, content=:content, summary=:summary, 
                      image_url=:image_url, category_id=:category_id, 
                      is_featured=:is_featured, is_breaking=:is_breaking
                  WHERE article_id=:article_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->content = htmlspecialchars(strip_tags($this->content));
        $this->summary = htmlspecialchars(strip_tags($this->summary));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));

        // Bind values
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":summary", $this->summary);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":is_featured", $this->is_featured);
        $stmt->bindParam(":is_breaking", $this->is_breaking);
        $stmt->bindParam(":article_id", $this->article_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete article
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE article_id = :article_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":article_id", $this->article_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Increment view count
    public function incrementViews($id) {
        $query = "UPDATE " . $this->table_name . " SET views = views + 1 WHERE article_id = :article_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":article_id", $id);
        $stmt->execute();
    }

    // Get total count for pagination
    public function getTotalCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['total'];
    }
}
?>

