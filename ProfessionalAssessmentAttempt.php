<?php
// app/models/ProfessionalAssessmentAttempt.php

class ProfessionalAssessmentAttempt {
    private $conn;
    private $table_name = "professional_assessment_attempts";

    // Properties
    public $id;
    public $professional_user_id;
    public $skill_assessment_id;
    public $skill_id; // Denormalized for easier querying, or join
    public $attempt_date;
    public $score_achieved;
    public $is_passed; // boolean
    public $answers_data; // JSON string of answers provided by professional
    public $time_taken_seconds;
    public $created_at;

    // Joined properties
    public $assessment_name;
    public $skill_name;

    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    // Record a new assessment attempt
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET
                    professional_user_id = :professional_user_id,
                    skill_assessment_id = :skill_assessment_id,
                    skill_id = :skill_id,
                    attempt_date = :attempt_date,
                    score_achieved = :score_achieved,
                    is_passed = :is_passed,
                    answers_data = :answers_data,
                    time_taken_seconds = :time_taken_seconds";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->professional_user_id = htmlspecialchars(strip_tags($this->professional_user_id));
        $this->skill_assessment_id = htmlspecialchars(strip_tags($this->skill_assessment_id));
        $this->skill_id = htmlspecialchars(strip_tags($this->skill_id));
        $this->attempt_date = htmlspecialchars(strip_tags($this->attempt_date ?? date("Y-m-d H:i:s")));
        $this->score_achieved = isset($this->score_achieved) ? (float)$this->score_achieved : null;
        $this->is_passed = isset($this->is_passed) ? (bool)$this->is_passed : false;
        $this->answers_data = $this->answers_data; // Assuming JSON string or null
        $this->time_taken_seconds = isset($this->time_taken_seconds) ? (int)$this->time_taken_seconds : null;

        // Bind parameters
        $stmt->bindParam(":professional_user_id", $this->professional_user_id);
        $stmt->bindParam(":skill_assessment_id", $this->skill_assessment_id);
        $stmt->bindParam(":skill_id", $this->skill_id);
        $stmt->bindParam(":attempt_date", $this->attempt_date);
        $stmt->bindParam(":score_achieved", $this->score_achieved);
        $stmt->bindParam(":is_passed", $this->is_passed, PDO::PARAM_BOOL);
        $stmt->bindParam(":answers_data", $this->answers_data);
        $stmt->bindParam(":time_taken_seconds", $this->time_taken_seconds);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        // error_log("ProfessionalAssessmentAttempt creation failed: " . implode(" ", $stmt->errorInfo()));
        return false;
    }

    // Find attempt by its ID
    public function findById($attempt_id) {
        $query = "SELECT paa.*, sa.assessment_name, sk.skill_name
                  FROM " . $this->table_name . " paa
                  JOIN skill_assessments sa ON paa.skill_assessment_id = sa.id
                  JOIN skills sk ON paa.skill_id = sk.id
                  WHERE paa.id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $attempt_id = htmlspecialchars(strip_tags($attempt_id));
        $stmt->bindParam(":id", $attempt_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row["id"];
            $this->professional_user_id = $row["professional_user_id"];
            $this->skill_assessment_id = $row["skill_assessment_id"];
            $this->skill_id = $row["skill_id"];
            $this->attempt_date = $row["attempt_date"];
            $this->score_achieved = $row["score_achieved"];
            $this->is_passed = (bool)$row["is_passed"];
            $this->answers_data = $row["answers_data"];
            $this->time_taken_seconds = $row["time_taken_seconds"];
            $this->created_at = $row["created_at"];
            $this->assessment_name = $row["assessment_name"];
            $this->skill_name = $row["skill_name"];
            return $this;
        }
        return null;
    }

    // Get all assessment attempts for a specific professional
    public function findByProfessionalUserId($professional_user_id) {
        $query = "SELECT paa.*, sa.assessment_name, sk.skill_name
                  FROM " . $this->table_name . " paa
                  JOIN skill_assessments sa ON paa.skill_assessment_id = sa.id
                  JOIN skills sk ON paa.skill_id = sk.id
                  WHERE paa.professional_user_id = :professional_user_id
                  ORDER BY paa.attempt_date DESC";
        $stmt = $this->conn->prepare($query);
        $professional_user_id = htmlspecialchars(strip_tags($professional_user_id));
        $stmt->bindParam(":professional_user_id", $professional_user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all attempts for a specific assessment (for admin/reporting)
    public function findByAssessmentId($skill_assessment_id) {
        $query = "SELECT paa.*, u.full_name as professional_name, u.email as professional_email
                  FROM " . $this->table_name . " paa
                  JOIN users u ON paa.professional_user_id = u.id
                  WHERE paa.skill_assessment_id = :skill_assessment_id
                  ORDER BY paa.attempt_date DESC";
        $stmt = $this->conn->prepare($query);
        $skill_assessment_id = htmlspecialchars(strip_tags($skill_assessment_id));
        $stmt->bindParam(":skill_assessment_id", $skill_assessment_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get latest passed attempt for a specific skill by a professional
    public function getLatestPassedAttemptForSkill($professional_user_id, $skill_id) {
        $query = "SELECT paa.*, sa.assessment_name
                  FROM " . $this->table_name . " paa
                  JOIN skill_assessments sa ON paa.skill_assessment_id = sa.id
                  WHERE paa.professional_user_id = :professional_user_id 
                  AND paa.skill_id = :skill_id 
                  AND paa.is_passed = 1
                  ORDER BY paa.attempt_date DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":professional_user_id", $professional_user_id, PDO::PARAM_INT);
        $stmt->bindParam(":skill_id", $skill_id, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }
}
?>
