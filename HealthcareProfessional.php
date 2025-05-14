<?php
// app/models/HealthcareProfessional.php

class HealthcareProfessional {
    private $conn;
    private $table_name = "healthcare_professionals";

    // Properties
    public $id;
    public $user_id; // Foreign key to users table
    public $professional_summary;
    public $years_experience;
    public $profession_type;
    public $specialties; // Could be TEXT or JSON
    public $desired_hourly_rate;
    public $availability_details; // Could be TEXT or JSON for a schedule
    public $background_check_status;
    public $created_at;
    public $updated_at;

    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    // Create a new healthcare professional profile (linked to an existing user)
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET
                    user_id = :user_id,
                    professional_summary = :professional_summary,
                    years_experience = :years_experience,
                    profession_type = :profession_type,
                    specialties = :specialties,
                    desired_hourly_rate = :desired_hourly_rate,
                    availability_details = :availability_details,
                    background_check_status = :background_check_status";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->professional_summary = htmlspecialchars(strip_tags($this->professional_summary ?? ""));
        $this->years_experience = isset($this->years_experience) ? (int)$this->years_experience : null;
        $this->profession_type = htmlspecialchars(strip_tags($this->profession_type ?? ""));
        $this->specialties = htmlspecialchars(strip_tags($this->specialties ?? ""));
        $this->desired_hourly_rate = isset($this->desired_hourly_rate) ? (float)$this->desired_hourly_rate : null;
        $this->availability_details = htmlspecialchars(strip_tags($this->availability_details ?? ""));
        $this->background_check_status = htmlspecialchars(strip_tags($this->background_check_status ?? "pending"));

        // Bind parameters
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":professional_summary", $this->professional_summary);
        $stmt->bindParam(":years_experience", $this->years_experience, PDO::PARAM_INT_OR_NULL);
        $stmt->bindParam(":profession_type", $this->profession_type);
        $stmt->bindParam(":specialties", $this->specialties);
        $stmt->bindParam(":desired_hourly_rate", $this->desired_hourly_rate);
        $stmt->bindParam(":availability_details", $this->availability_details);
        $stmt->bindParam(":background_check_status", $this->background_check_status);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        // error_log("HealthcareProfessional creation failed: " . implode(" ", $stmt->errorInfo()));
        return false;
    }

    // Find professional profile by user_id
    public function findByUserId($user_id) {
        $query = "SELECT hp.*, u.full_name, u.email, u.phone_number, u.address_street, u.address_city, u.address_state, u.address_zip_code, u.profile_image_path
                  FROM " . $this->table_name . " hp
                  JOIN users u ON hp.user_id = u.id
                  WHERE hp.user_id = :user_id LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $user_id = htmlspecialchars(strip_tags($user_id));
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row["id"];
            $this->user_id = $row["user_id"];
            $this->professional_summary = $row["professional_summary"];
            $this->years_experience = $row["years_experience"];
            $this->profession_type = $row["profession_type"];
            $this->specialties = $row["specialties"];
            $this->desired_hourly_rate = $row["desired_hourly_rate"];
            $this->availability_details = $row["availability_details"];
            $this->background_check_status = $row["background_check_status"];
            $this->created_at = $row["created_at"];
            $this->updated_at = $row["updated_at"];
            
            // Populate joined user fields if needed, or handle them separately via UserController
            // For example:
            // $this->user_full_name = $row["full_name"]; 
            // $this->user_email = $row["email"];
            return $this;
        }
        return null;
    }

    // Update professional profile
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET
                    professional_summary = :professional_summary,
                    years_experience = :years_experience,
                    profession_type = :profession_type,
                    specialties = :specialties,
                    desired_hourly_rate = :desired_hourly_rate,
                    availability_details = :availability_details,
                    background_check_status = :background_check_status,
                    updated_at = CURRENT_TIMESTAMP
                  WHERE user_id = :user_id"; // Usually update by user_id

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->professional_summary = htmlspecialchars(strip_tags($this->professional_summary ?? ""));
        $this->years_experience = isset($this->years_experience) ? (int)$this->years_experience : null;
        $this->profession_type = htmlspecialchars(strip_tags($this->profession_type ?? ""));
        $this->specialties = htmlspecialchars(strip_tags($this->specialties ?? ""));
        $this->desired_hourly_rate = isset($this->desired_hourly_rate) ? (float)$this->desired_hourly_rate : null;
        $this->availability_details = htmlspecialchars(strip_tags($this->availability_details ?? ""));
        $this->background_check_status = htmlspecialchars(strip_tags($this->background_check_status ?? "pending"));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));

        // Bind
        $stmt->bindParam(":professional_summary", $this->professional_summary);
        $stmt->bindParam(":years_experience", $this->years_experience, PDO::PARAM_INT_OR_NULL);
        $stmt->bindParam(":profession_type", $this->profession_type);
        $stmt->bindParam(":specialties", $this->specialties);
        $stmt->bindParam(":desired_hourly_rate", $this->desired_hourly_rate);
        $stmt->bindParam(":availability_details", $this->availability_details);
        $stmt->bindParam(":background_check_status", $this->background_check_status);
        $stmt->bindParam(":user_id", $this->user_id);

        if ($stmt->execute()) {
            return true;
        }
        // error_log("HealthcareProfessional update failed: " . implode(" ", $stmt->errorInfo()));
        return false;
    }
}
?>
