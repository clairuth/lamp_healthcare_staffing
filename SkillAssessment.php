<?php
// app/models/SkillAssessment.php

class SkillAssessment {
    private $conn;
    private $table_name = "skill_assessments";

    // Properties
    public $id;
    public $skill_id;
    public $assessment_name;
    public $assessment_type; // e.g., multiple_choice, practical_simulation_link
    public $passing_score; // For multiple_choice
    public $total_questions; // For multiple_choice
    public $time_limit_minutes;
    public $instructions;
    public $created_by_user_id; // Admin who created it
    public $created_at;
    public $updated_at;

    // For storing questions and answers (could be JSON or separate tables)
    public $questions_data; // JSON string: [{question: "...", options: ["A", "B"], correct_answer: "A", points: 1}, ...]

    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    // Create a new skill assessment
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET
                    skill_id = :skill_id,
                    assessment_name = :assessment_name,
                    assessment_type = :assessment_type,
                    passing_score = :passing_score,
                    total_questions = :total_questions,
                    time_limit_minutes = :time_limit_minutes,
                    instructions = :instructions,
                    questions_data = :questions_data,
                    created_by_user_id = :created_by_user_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->skill_id = htmlspecialchars(strip_tags($this->skill_id));
        $this->assessment_name = htmlspecialchars(strip_tags($this->assessment_name));
        $this->assessment_type = htmlspecialchars(strip_tags($this->assessment_type ?? "multiple_choice"));
        $this->passing_score = isset($this->passing_score) ? (int)$this->passing_score : null;
        $this->total_questions = isset($this->total_questions) ? (int)$this->total_questions : null;
        $this->time_limit_minutes = isset($this->time_limit_minutes) ? (int)$this->time_limit_minutes : null;
        $this->instructions = htmlspecialchars(strip_tags($this->instructions ?? ""));
        $this->questions_data = $this->questions_data; // Assuming already JSON string or null
        $this->created_by_user_id = htmlspecialchars(strip_tags($this->created_by_user_id));

        // Bind parameters
        $stmt->bindParam(":skill_id", $this->skill_id);
        $stmt->bindParam(":assessment_name", $this->assessment_name);
        $stmt->bindParam(":assessment_type", $this->assessment_type);
        $stmt->bindParam(":passing_score", $this->passing_score);
        $stmt->bindParam(":total_questions", $this->total_questions);
        $stmt->bindParam(":time_limit_minutes", $this->time_limit_minutes);
        $stmt->bindParam(":instructions", $this->instructions);
        $stmt->bindParam(":questions_data", $this->questions_data);
        $stmt->bindParam(":created_by_user_id", $this->created_by_user_id);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        // error_log("SkillAssessment creation failed: " . implode(" ", $stmt->errorInfo()));
        return false;
    }

    // Find assessment by its ID
    public function findById($assessment_id) {
        $query = "SELECT sa.*, s.skill_name 
                  FROM " . $this->table_name . " sa
                  JOIN skills s ON sa.skill_id = s.id
                  WHERE sa.id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $assessment_id = htmlspecialchars(strip_tags($assessment_id));
        $stmt->bindParam(":id", $assessment_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row["id"];
            $this->skill_id = $row["skill_id"];
            $this->assessment_name = $row["assessment_name"];
            $this->assessment_type = $row["assessment_type"];
            $this->passing_score = $row["passing_score"];
            $this->total_questions = $row["total_questions"];
            $this->time_limit_minutes = $row["time_limit_minutes"];
            $this->instructions = $row["instructions"];
            $this->questions_data = $row["questions_data"];
            $this->created_by_user_id = $row["created_by_user_id"];
            $this->created_at = $row["created_at"];
            $this->updated_at = $row["updated_at"];
            $this->skill_name = $row["skill_name"]; // Joined data
            return $this;
        }
        return null;
    }

    // Get all assessments (e.g., for admin view or linking to skills)
    public function getAll($limit = 100, $offset = 0) {
        $query = "SELECT sa.*, s.skill_name 
                  FROM " . $this->table_name . " sa
                  JOIN skills s ON sa.skill_id = s.id
                  ORDER BY sa.assessment_name ASC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Find assessments for a specific skill_id
    public function findBySkillId($skill_id) {
        $query = "SELECT sa.*, s.skill_name 
                  FROM " . $this->table_name . " sa
                  JOIN skills s ON sa.skill_id = s.id
                  WHERE sa.skill_id = :skill_id ORDER BY sa.assessment_name ASC";
        $stmt = $this->conn->prepare($query);
        $skill_id = htmlspecialchars(strip_tags($skill_id));
        $stmt->bindParam(":skill_id", $skill_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update assessment details
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET
                    skill_id = :skill_id,
                    assessment_name = :assessment_name,
                    assessment_type = :assessment_type,
                    passing_score = :passing_score,
                    total_questions = :total_questions,
                    time_limit_minutes = :time_limit_minutes,
                    instructions = :instructions,
                    questions_data = :questions_data,
                    -- created_by_user_id should not change on update
                    updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        // Sanitize other fields as in create()
        $this->skill_id = htmlspecialchars(strip_tags($this->skill_id));
        $this->assessment_name = htmlspecialchars(strip_tags($this->assessment_name));
        $this->assessment_type = htmlspecialchars(strip_tags($this->assessment_type));
        $this->passing_score = isset($this->passing_score) ? (int)$this->passing_score : null;
        $this->total_questions = isset($this->total_questions) ? (int)$this->total_questions : null;
        $this->time_limit_minutes = isset($this->time_limit_minutes) ? (int)$this->time_limit_minutes : null;
        $this->instructions = htmlspecialchars(strip_tags($this->instructions));
        $this->questions_data = $this->questions_data; // Assuming JSON string

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":skill_id", $this->skill_id);
        $stmt->bindParam(":assessment_name", $this->assessment_name);
        $stmt->bindParam(":assessment_type", $this->assessment_type);
        $stmt->bindParam(":passing_score", $this->passing_score);
        $stmt->bindParam(":total_questions", $this->total_questions);
        $stmt->bindParam(":time_limit_minutes", $this->time_limit_minutes);
        $stmt->bindParam(":instructions", $this->instructions);
        $stmt->bindParam(":questions_data", $this->questions_data);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete an assessment
    public function delete() {
        // Consider implications: what if professionals have taken this assessment?
        // May need to archive or prevent deletion if results exist.
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
