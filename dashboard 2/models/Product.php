<?php
class Product {
    private $conn;
    private $table_name = "products";

    public $id;
    public $category_id;
    public $name;
    public $description;
    public $price;
    public $stock;
    public $image;
    private $upload_path = "uploads/products/";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create product with image upload
    public function create() {
        try {
            $this->conn->beginTransaction();

            $query = "INSERT INTO " . $this->table_name . "
                    (name, description, price, stock, category_id, image_path)
                    VALUES (?, ?, ?, ?, ?, ?)";

            // Handle image upload if present
            $image_path = null;
            if ($this->image && $this->image['error'] === UPLOAD_ERR_OK) {
                $image_path = $this->handleImageUpload();
                if (!$image_path) {
                    throw new Exception("Image upload failed");
                }
            }

            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                $this->name,
                $this->description,
                $this->price,
                $this->stock,
                $this->category_id,
                $image_path
            ]);

            $this->conn->commit();
            return $result;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    // Read all products
    public function read() {
        $query = "SELECT p.*, c.name as category_name 
                FROM " . $this->table_name . " p
                LEFT JOIN categories c ON p.category_id = c.id
                ORDER BY p.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read single product
    public function readOne() {
        $query = "SELECT p.*, c.name as category_name 
                FROM " . $this->table_name . " p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        return $stmt;
    }

    // Update product with image
    public function update() {
        try {
            $this->conn->beginTransaction();

            $image_path = null;
            if ($this->image && $this->image['error'] === UPLOAD_ERR_OK) {
                // Delete old image if exists
                $this->deleteOldImage();
                
                // Upload new image
                $image_path = $this->handleImageUpload();
                if (!$image_path) {
                    throw new Exception("Image upload failed");
                }
            }

            $query = "UPDATE " . $this->table_name . "
                    SET name = ?, description = ?, price = ?, 
                        stock = ?, category_id = ?" .
                    ($image_path ? ", image_path = ?" : "") .
                    " WHERE id = ?";

            $params = [
                $this->name,
                $this->description,
                $this->price,
                $this->stock,
                $this->category_id
            ];

            if ($image_path) {
                $params[] = $image_path;
            }
            $params[] = $this->id;

            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute($params);

            $this->conn->commit();
            return $result;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    // Handle image upload
    private function handleImageUpload() {
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png'];
        if (!in_array($this->image['type'], $allowed_types)) {
            throw new Exception("Invalid file type");
        }

        // Validate file size (3MB)
        if ($this->image['size'] > 3 * 1024 * 1024) {
            throw new Exception("File too large");
        }

        // Create upload directory if it doesn't exist
        if (!file_exists($this->upload_path)) {
            mkdir($this->upload_path, 0777, true);
        }

        // Generate unique filename
        $extension = pathinfo($this->image['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $filepath = $this->upload_path . $filename;

        // Move uploaded file
        if (!move_uploaded_file($this->image['tmp_name'], $filepath)) {
            return false;
        }

        return $filepath;
    }

    // Delete old image
    private function deleteOldImage() {
        $query = "SELECT image_path FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && $row['image_path'] && file_exists($row['image_path'])) {
            unlink($row['image_path']);
        }
    }

    // Delete product with image
    public function delete() {
        try {
            $this->conn->beginTransaction();

            // Delete image file
            $this->deleteOldImage();

            // Delete database record
            $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([$this->id]);

            $this->conn->commit();
            return $result;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}
?> 