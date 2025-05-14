<?php
// app/models/Skill.php

class Skill {
    private $conn;
    private $table_name = "skills";

    // Properties
    public $id;
    public $skill_name;
    public $skill_category; // e.g., Clinical, Technical, Soft Skill
    public $description;
    public $created_at;
    public $updated_at;

    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    // Create a new skill
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET
                    skill_name = :skill_name,
                    skill_category = :skill_category,
                    description = :description";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->skill_name = htmlspecialchars(strip_tags($this->skill_name));
        $this->skill_category = htmlspecialchars(strip_tags($this->skill_category ?? "General"));
        $this->description = htmlspecialchars(strip_tags($this->description ?? ""));

        // Bind parameters
        $stmt->bindParam(":skill_name", $this->skill_name);
        $stmt->bindParam(":skill_category", $this->skill_category);
        $stmt->bindParam(":description", $this->description);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        // error_log("Skill creation failed: " . implode(" ", $stmt->errorInfo()));
        return false;
    }

    // Find skill by its ID
    public function findById($skill_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $skill_id = htmlspecialchars(strip_tags($skill_id));
        $stmt->bindParam(":id", $skill_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row["id"];
            $this->skill_name = $row["skill_name"];
            $this->skill_category = $row["skill_category"];
            $this->description = $row["description"];
            $this->created_at = $row["created_at"];
            $this->updated_at = $row["updated_at"];
            return $this;
        }
        return null;
    }

    // Find skill by name (exact match)
    public function findByName($skill_name) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE skill_name = :skill_name LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $skill_name = htmlspecialchars(strip_tags($skill_name));
        $stmt->bindParam(":skill_name", $skill_name);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    // Get all skills
    public function getAll($limit = 100, $offset = 0) {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY skill_name ASC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Count all skills
    public function countAll() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row["total"] ?? 0;
    }

    // Update skill details
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET
                    skill_name = :skill_name,
                    skill_category = :skill_category,
                    description = :description,
                    updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->skill_name = htmlspecialchars(strip_tags($this->skill_name));
        $this->skill_category = htmlspecialchars(strip_tags($this->skill_category));
        $this->description = htmlspecialchars(strip_tags($this->description));

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":skill_name", $this->skill_name);
        $stmt->bindParam(":skill_category", $this->skill_category);
        $stmt->bindParam(":description", $this->description);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete a skill
    public function delete() {
        // Consider implications: what if this skill is linked to assessments or professionals?
        // May need to handle or prevent deletion if in use.
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
