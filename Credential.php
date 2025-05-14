<?php
// app/models/Credential.php

class Credential {
    private $conn;
    private $table_name = "credentials";

    // Properties
    public $id;
    public $professional_user_id; // Foreign key to users table (professional)
    public $credential_type; // e.g., RN_LICENSE, BLS_CERT, TB_TEST, COVID_VACCINE, DRIVERS_LICENSE, ID_CARD
    public $credential_name; // e.g., "Texas RN License", "AHA BLS Certification"
    public $issuing_organization;
    public $license_number; // Or certificate number
    public $issue_date;
    public $expiration_date;
    public $file_path; // Path to the uploaded document
    public $verification_status; // pending, verified, rejected, expired
    public $verification_notes;
    public $verified_by_user_id; // Admin who verified
    public $verified_at;
    public $created_at;
    public $updated_at;

    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    // Create a new credential record
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET
                    professional_user_id = :professional_user_id,
                    credential_type = :credential_type,
                    credential_name = :credential_name,
                    issuing_organization = :issuing_organization,
                    license_number = :license_number,
                    issue_date = :issue_date,
                    expiration_date = :expiration_date,
                    file_path = :file_path,
                    verification_status = :verification_status";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->professional_user_id = htmlspecialchars(strip_tags($this->professional_user_id));
        $this->credential_type = htmlspecialchars(strip_tags($this->credential_type));
        $this->credential_name = htmlspecialchars(strip_tags($this->credential_name));
        $this->issuing_organization = htmlspecialchars(strip_tags($this->issuing_organization ?? ""));
        $this->license_number = htmlspecialchars(strip_tags($this->license_number ?? ""));
        $this->issue_date = !empty($this->issue_date) ? htmlspecialchars(strip_tags($this->issue_date)) : null;
        $this->expiration_date = !empty($this->expiration_date) ? htmlspecialchars(strip_tags($this->expiration_date)) : null;
        $this->file_path = htmlspecialchars(strip_tags($this->file_path));
        $this->verification_status = htmlspecialchars(strip_tags($this->verification_status ?? "pending"));

        // Bind parameters
        $stmt->bindParam(":professional_user_id", $this->professional_user_id);
        $stmt->bindParam(":credential_type", $this->credential_type);
        $stmt->bindParam(":credential_name", $this->credential_name);
        $stmt->bindParam(":issuing_organization", $this->issuing_organization);
        $stmt->bindParam(":license_number", $this->license_number);
        $stmt->bindParam(":issue_date", $this->issue_date);
        $stmt->bindParam(":expiration_date", $this->expiration_date);
        $stmt->bindParam(":file_path", $this->file_path);
        $stmt->bindParam(":verification_status", $this->verification_status);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        // error_log("Credential creation failed: " . implode(" ", $stmt->errorInfo()));
        return false;
    }

    // Find credentials by professional_user_id
    public function findByProfessionalUserId($professional_user_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE professional_user_id = :professional_user_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $professional_user_id = htmlspecialchars(strip_tags($professional_user_id));
        $stmt->bindParam(":professional_user_id", $professional_user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Find a specific credential by its ID
    public function findById($credential_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $credential_id = htmlspecialchars(strip_tags($credential_id));
        $stmt->bindParam(":id", $credential_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row["id"];
            $this->professional_user_id = $row["professional_user_id"];
            $this->credential_type = $row["credential_type"];
            $this->credential_name = $row["credential_name"];
            $this->issuing_organization = $row["issuing_organization"];
            $this->license_number = $row["license_number"];
            $this->issue_date = $row["issue_date"];
            $this->expiration_date = $row["expiration_date"];
            $this->file_path = $row["file_path"];
            $this->verification_status = $row["verification_status"];
            $this->verification_notes = $row["verification_notes"];
            $this->verified_by_user_id = $row["verified_by_user_id"];
            $this->verified_at = $row["verified_at"];
            $this->created_at = $row["created_at"];
            $this->updated_at = $row["updated_at"];
            return $this;
        }
        return null;
    }

    // Update credential details (e.g., if user re-uploads or corrects info)
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET
                    credential_type = :credential_type,
                    credential_name = :credential_name,
                    issuing_organization = :issuing_organization,
                    license_number = :license_number,
                    issue_date = :issue_date,
                    expiration_date = :expiration_date,
                    file_path = :file_path,
                    -- verification_status = :verification_status, -- Usually updated by admin
                    updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id AND professional_user_id = :professional_user_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->credential_type = htmlspecialchars(strip_tags($this->credential_type));
        $this->credential_name = htmlspecialchars(strip_tags($this->credential_name));
        // ... sanitize other updatable fields ...
        $this->file_path = htmlspecialchars(strip_tags($this->file_path));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->professional_user_id = htmlspecialchars(strip_tags($this->professional_user_id));

        // Bind
        $stmt->bindParam(":credential_type", $this->credential_type);
        $stmt->bindParam(":credential_name", $this->credential_name);
        // ... bind other params ...
        $stmt->bindParam(":file_path", $this->file_path);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":professional_user_id", $this->professional_user_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Update verification status (by admin)
    public function updateVerificationStatus($admin_user_id) {
        $query = "UPDATE " . $this->table_name . " SET
                    verification_status = :verification_status,
                    verification_notes = :verification_notes,
                    verified_by_user_id = :verified_by_user_id,
                    verified_at = CURRENT_TIMESTAMP,
                    updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);

        $this->verification_status = htmlspecialchars(strip_tags($this->verification_status));
        $this->verification_notes = htmlspecialchars(strip_tags($this->verification_notes ?? ""));
        $admin_user_id = htmlspecialchars(strip_tags($admin_user_id));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":verification_status", $this->verification_status);
        $stmt->bindParam(":verification_notes", $this->verification_notes);
        $stmt->bindParam(":verified_by_user_id", $admin_user_id);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // Delete a credential
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND professional_user_id = :professional_user_id";
        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->professional_user_id = htmlspecialchars(strip_tags($this->professional_user_id));

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":professional_user_id", $this->professional_user_id);

        // Also delete the associated file from storage
        if ($this->file_path && file_exists(BASE_PATH . "/public/" . $this->file_path)) {
            // Be careful with file deletions, ensure path is correct and secured
            // unlink(BASE_PATH . "/public/" . $this->file_path);
        }

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
