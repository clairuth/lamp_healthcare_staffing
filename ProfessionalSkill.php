<?php
// app/models/ProfessionalSkill.php

class ProfessionalSkill {
    private $conn;
    private $table_name = "professional_skills";

    // Properties
    public $id;
    public $professional_user_id;
    public $skill_id;
    public $skill_level; // e.g., Beginner, Intermediate, Advanced, Expert (could be ENUM or TEXT)
    public $years_experience;
    public $is_verified; // boolean, if skill was verified (e.g., via assessment or admin)
    public $verified_at;
    public $created_at;
    public $updated_at;

    // Joined properties
    public $skill_name;
    public $skill_category;

    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    // Add a skill to a professional's profile
    public function create() {
        // Check if this skill is already added for this professional
        $check_query = "SELECT id FROM " . $this->table_name . " WHERE professional_user_id = :professional_user_id AND skill_id = :skill_id LIMIT 1";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(":professional_user_id", $this->professional_user_id, PDO::PARAM_INT);
        $check_stmt->bindParam(":skill_id", $this->skill_id, PDO::PARAM_INT);
        $check_stmt->execute();
        if ($check_stmt->rowCount() > 0) {
            // error_log("Skill ID {" . $this->skill_id . "} already exists for professional ID {" . $this->professional_user_id . "}");
            return false; // Skill already exists for this professional
        }

        $query = "INSERT INTO " . $this->table_name . " SET
                    professional_user_id = :professional_user_id,
                    skill_id = :skill_id,
                    skill_level = :skill_level,
                    years_experience = :years_experience,
                    is_verified = :is_verified";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->professional_user_id = htmlspecialchars(strip_tags($this->professional_user_id));
        $this->skill_id = htmlspecialchars(strip_tags($this->skill_id));
        $this->skill_level = htmlspecialchars(strip_tags($this->skill_level ?? "Beginner"));
        $this->years_experience = isset($this->years_experience) ? (int)$this->years_experience : null;
        $this->is_verified = isset($this->is_verified) ? (bool)$this->is_verified : false;

        // Bind parameters
        $stmt->bindParam(":professional_user_id", $this->professional_user_id);
        $stmt->bindParam(":skill_id", $this->skill_id);
        $stmt->bindParam(":skill_level", $this->skill_level);
        $stmt->bindParam(":years_experience", $this->years_experience);
        $stmt->bindParam(":is_verified", $this->is_verified, PDO::PARAM_BOOL);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        // error_log("ProfessionalSkill creation failed: " . implode(" ", $stmt->errorInfo()));
        return false;
    }

    // Get all skills for a specific professional
    public function findByProfessionalUserId($professional_user_id) {
        $query = "SELECT ps.*, s.skill_name, s.skill_category 
                  FROM " . $this->table_name . " ps
                  JOIN skills s ON ps.skill_id = s.id
                  WHERE ps.professional_user_id = :professional_user_id
                  ORDER BY s.skill_name ASC";
        $stmt = $this->conn->prepare($query);
        $professional_user_id = htmlspecialchars(strip_tags($professional_user_id));
        $stmt->bindParam(":professional_user_id", $professional_user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Find a specific professional skill entry by its ID
    public function findById($id) {
        $query = "SELECT ps.*, s.skill_name, s.skill_category 
                  FROM " . $this->table_name . " ps
                  JOIN skills s ON ps.skill_id = s.id
                  WHERE ps.id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $id = htmlspecialchars(strip_tags($id));
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row["id"];
            $this->professional_user_id = $row["professional_user_id"];
            $this->skill_id = $row["skill_id"];
            $this->skill_level = $row["skill_level"];
            $this->years_experience = $row["years_experience"];
            $this->is_verified = (bool)$row["is_verified"];
            $this->verified_at = $row["verified_at"];
            $this->created_at = $row["created_at"];
            $this->updated_at = $row["updated_at"];
            $this->skill_name = $row["skill_name"];
            $this->skill_category = $row["skill_category"];
            return $this;
        }
        return null;
    }

    // Update a professional's skill details (e.g., level, experience)
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET
                    skill_level = :skill_level,
                    years_experience = :years_experience,
                    is_verified = :is_verified,
                    verified_at = :verified_at,
                    updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id AND professional_user_id = :professional_user_id";

        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->professional_user_id = htmlspecialchars(strip_tags($this->professional_user_id));
        $this->skill_level = htmlspecialchars(strip_tags($this->skill_level));
        $this->years_experience = isset($this->years_experience) ? (int)$this->years_experience : null;
        $this->is_verified = isset($this->is_verified) ? (bool)$this->is_verified : false;
        $this->verified_at = $this->is_verified && !empty($this->verified_at) ? htmlspecialchars(strip_tags($this->verified_at)) : ($this->is_verified ? date('Y-m-d H:i:s') : null);
        
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":professional_user_id", $this->professional_user_id);
        $stmt->bindParam(":skill_level", $this->skill_level);
        $stmt->bindParam(":years_experience", $this->years_experience);
        $stmt->bindParam(":is_verified", $this->is_verified, PDO::PARAM_BOOL);
        $stmt->bindParam(":verified_at", $this->verified_at);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Remove a skill from a professional's profile
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND professional_user_id = :professional_user_id";
        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->professional_user_id = htmlspecialchars(strip_tags($this->professional_user_id));

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":professional_user_id", $this->professional_user_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
