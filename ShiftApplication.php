<?php
// app/models/ShiftApplication.php

class ShiftApplication {
    private $conn;
    private $table_name = "shift_applications";

    // Properties
    public $id;
    public $shift_id;
    public $professional_user_id;
    public $application_status; // applied, accepted, rejected, withdrawn, completed_by_professional, confirmed_by_facility
    public $application_notes; // e.g., why professional is a good fit, or why facility rejected
    public $applied_at;
    public $updated_at;

    // Joined properties (for convenience when fetching)
    public $shift_title;
    public $shift_date;
    public $start_time;
    public $end_time;
    public $facility_name;
    public $professional_name;
    public $professional_email;

    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    // Create a new shift application
    public function create() {
        // Check if already applied
        if ($this->hasAlreadyApplied($this->shift_id, $this->professional_user_id)) {
            // error_log("User {" . $this->professional_user_id . "} already applied for shift {" . $this->shift_id . "}");
            return false; // Or throw an exception / return specific error code
        }

        $query = "INSERT INTO " . $this->table_name . " SET
                    shift_id = :shift_id,
                    professional_user_id = :professional_user_id,
                    application_status = :application_status,
                    application_notes = :application_notes";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->shift_id = htmlspecialchars(strip_tags($this->shift_id));
        $this->professional_user_id = htmlspecialchars(strip_tags($this->professional_user_id));
        $this->application_status = htmlspecialchars(strip_tags($this->application_status ?? "applied"));
        $this->application_notes = htmlspecialchars(strip_tags($this->application_notes ?? ""));

        // Bind parameters
        $stmt->bindParam(":shift_id", $this->shift_id);
        $stmt->bindParam(":professional_user_id", $this->professional_user_id);
        $stmt->bindParam(":application_status", $this->application_status);
        $stmt->bindParam(":application_notes", $this->application_notes);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        // error_log("ShiftApplication creation failed: " . implode(" ", $stmt->errorInfo()));
        return false;
    }

    // Check if a professional has already applied for a specific shift
    public function hasAlreadyApplied($shift_id, $professional_user_id) {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE shift_id = :shift_id AND professional_user_id = :professional_user_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":shift_id", $shift_id, PDO::PARAM_INT);
        $stmt->bindParam(":professional_user_id", $professional_user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Find application by its ID
    public function findById($application_id) {
        $query = "SELECT sa.*, s.shift_title, s.shift_date, s.start_time, s.end_time, f.facility_name, u.full_name as professional_name, u.email as professional_email
                  FROM " . $this->table_name . " sa
                  JOIN shifts s ON sa.shift_id = s.id
                  JOIN users u ON sa.professional_user_id = u.id
                  JOIN facilities f ON s.facility_id = f.id
                  WHERE sa.id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $application_id = htmlspecialchars(strip_tags($application_id));
        $stmt->bindParam(":id", $application_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row["id"];
            $this->shift_id = $row["shift_id"];
            $this->professional_user_id = $row["professional_user_id"];
            $this->application_status = $row["application_status"];
            $this->application_notes = $row["application_notes"];
            $this->applied_at = $row["applied_at"];
            $this->updated_at = $row["updated_at"];
            // Joined data
            $this->shift_title = $row["shift_title"];
            $this->shift_date = $row["shift_date"];
            $this->start_time = $row["start_time"];
            $this->end_time = $row["end_time"];
            $this->facility_name = $row["facility_name"];
            $this->professional_name = $row["professional_name"];
            $this->professional_email = $row["professional_email"];
            return $this;
        }
        return null;
    }

    // Get applications for a specific shift (for facility view)
    public function findByShiftId($shift_id) {
        $query = "SELECT sa.*, u.full_name as professional_name, u.email as professional_email, u.profile_image_path as professional_avatar
                  FROM " . $this->table_name . " sa
                  JOIN users u ON sa.professional_user_id = u.id
                  WHERE sa.shift_id = :shift_id
                  ORDER BY sa.applied_at DESC";
        $stmt = $this->conn->prepare($query);
        $shift_id = htmlspecialchars(strip_tags($shift_id));
        $stmt->bindParam(":shift_id", $shift_id);
        $stmt->execute();
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($applications as &$app) {
            if (!empty($app["professional_avatar"])) {
                $app["professional_avatar_url"] = base_url($app["professional_avatar"]);
            }
        }
        return $applications;
    }

    // Get applications made by a specific professional
    public function findByProfessionalId($professional_user_id) {
        $query = "SELECT sa.*, s.shift_title, s.shift_date, s.start_time, s.end_time, f.facility_name, f.logo_image_path as facility_logo
                  FROM " . $this->table_name . " sa
                  JOIN shifts s ON sa.shift_id = s.id
                  JOIN facilities f ON s.facility_id = f.id
                  WHERE sa.professional_user_id = :professional_user_id
                  ORDER BY sa.applied_at DESC";
        $stmt = $this->conn->prepare($query);
        $professional_user_id = htmlspecialchars(strip_tags($professional_user_id));
        $stmt->bindParam(":professional_user_id", $professional_user_id);
        $stmt->execute();
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($applications as &$app) {
            if (!empty($app["facility_logo"])) {
                $app["facility_logo_url"] = base_url($app["facility_logo"]);
            }
        }
        return $applications;
    }

    // Update application status (e.g., facility accepts/rejects, professional withdraws)
    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . " SET
                    application_status = :application_status,
                    application_notes = :application_notes,
                    updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->application_status = htmlspecialchars(strip_tags($this->application_status));
        $this->application_notes = htmlspecialchars(strip_tags($this->application_notes ?? ""));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":application_status", $this->application_status);
        $stmt->bindParam(":application_notes", $this->application_notes);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            // If status is 'accepted', update the shift status to 'filled'
            if ($this->application_status === 'accepted') {
                $shift_model = new Shift($this->conn);
                $shift = $shift_model->findById($this->shift_id);
                if ($shift) {
                    $shift->status = 'filled';
                    // The facility_user_id for shift update needs to be the owner of the shift
                    // This logic might need to be in the controller to ensure correct user ID.
                    // For now, assuming the shift model's update doesn't strictly require facility_user_id if called internally.
                    $shift->update(); 
                }
            }
            return true;
        }
        return false;
    }

    // Delete an application (e.g., if withdrawn by professional and system allows deletion)
    // Typically, status is changed to 'withdrawn' rather than hard delete.
    public function delete() {
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
