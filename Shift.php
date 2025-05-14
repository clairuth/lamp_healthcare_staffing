<?php
// app/models/Shift.php

class Shift {
    private $conn;
    private $table_name = "shifts";

    // Properties
    public $id;
    public $facility_user_id; // User ID of the facility admin/manager who posted the shift
    public $facility_id; // Foreign key to facilities table
    public $shift_title;
    public $profession_type_required; // e.g., RN, LPN, CNA
    public $specialty_required;
    public $job_description;
    public $shift_date;
    public $start_time;
    public $end_time;
    public $duration_hours;
    public $hourly_rate;
    public $bonus_offered;
    public $status; // open, filled, completed, cancelled
    public $notes_for_professional;
    public $required_skills; // Could be TEXT or JSON array of skill IDs/names
    public $required_credentials; // Could be TEXT or JSON array of credential types
    public $created_at;
    public $updated_at;

    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    // Create a new shift
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET
                    facility_user_id = :facility_user_id,
                    facility_id = :facility_id,
                    shift_title = :shift_title,
                    profession_type_required = :profession_type_required,
                    specialty_required = :specialty_required,
                    job_description = :job_description,
                    shift_date = :shift_date,
                    start_time = :start_time,
                    end_time = :end_time,
                    duration_hours = :duration_hours,
                    hourly_rate = :hourly_rate,
                    bonus_offered = :bonus_offered,
                    status = :status,
                    notes_for_professional = :notes_for_professional,
                    required_skills = :required_skills,
                    required_credentials = :required_credentials";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->facility_user_id = htmlspecialchars(strip_tags($this->facility_user_id));
        $this->facility_id = htmlspecialchars(strip_tags($this->facility_id));
        $this->shift_title = htmlspecialchars(strip_tags($this->shift_title));
        $this->profession_type_required = htmlspecialchars(strip_tags($this->profession_type_required));
        $this->specialty_required = htmlspecialchars(strip_tags($this->specialty_required ?? ""));
        $this->job_description = htmlspecialchars(strip_tags($this->job_description ?? ""));
        $this->shift_date = htmlspecialchars(strip_tags($this->shift_date));
        $this->start_time = htmlspecialchars(strip_tags($this->start_time));
        $this->end_time = htmlspecialchars(strip_tags($this->end_time));
        $this->duration_hours = (float)$this->duration_hours;
        $this->hourly_rate = (float)$this->hourly_rate;
        $this->bonus_offered = isset($this->bonus_offered) ? (float)$this->bonus_offered : null;
        $this->status = htmlspecialchars(strip_tags($this->status ?? "open"));
        $this->notes_for_professional = htmlspecialchars(strip_tags($this->notes_for_professional ?? ""));
        $this->required_skills = htmlspecialchars(strip_tags($this->required_skills ?? "")); // Consider JSON encoding if it's an array
        $this->required_credentials = htmlspecialchars(strip_tags($this->required_credentials ?? "")); // Consider JSON encoding

        // Bind parameters
        $stmt->bindParam(":facility_user_id", $this->facility_user_id);
        $stmt->bindParam(":facility_id", $this->facility_id);
        $stmt->bindParam(":shift_title", $this->shift_title);
        $stmt->bindParam(":profession_type_required", $this->profession_type_required);
        $stmt->bindParam(":specialty_required", $this->specialty_required);
        $stmt->bindParam(":job_description", $this->job_description);
        $stmt->bindParam(":shift_date", $this->shift_date);
        $stmt->bindParam(":start_time", $this->start_time);
        $stmt->bindParam(":end_time", $this->end_time);
        $stmt->bindParam(":duration_hours", $this->duration_hours);
        $stmt->bindParam(":hourly_rate", $this->hourly_rate);
        $stmt->bindParam(":bonus_offered", $this->bonus_offered);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":notes_for_professional", $this->notes_for_professional);
        $stmt->bindParam(":required_skills", $this->required_skills);
        $stmt->bindParam(":required_credentials", $this->required_credentials);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        // error_log("Shift creation failed: " . implode(" ", $stmt->errorInfo()));
        return false;
    }

    // Find shift by its ID
    public function findById($shift_id) {
        $query = "SELECT s.*, f.facility_name, f.logo_image_path as facility_logo 
                  FROM " . $this->table_name . " s 
                  JOIN facilities f ON s.facility_id = f.id
                  WHERE s.id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $shift_id = htmlspecialchars(strip_tags($shift_id));
        $stmt->bindParam(":id", $shift_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row["id"];
            $this->facility_user_id = $row["facility_user_id"];
            $this->facility_id = $row["facility_id"];
            $this->shift_title = $row["shift_title"];
            $this->profession_type_required = $row["profession_type_required"];
            $this->specialty_required = $row["specialty_required"];
            $this->job_description = $row["job_description"];
            $this->shift_date = $row["shift_date"];
            $this->start_time = $row["start_time"];
            $this->end_time = $row["end_time"];
            $this->duration_hours = $row["duration_hours"];
            $this->hourly_rate = $row["hourly_rate"];
            $this->bonus_offered = $row["bonus_offered"];
            $this->status = $row["status"];
            $this->notes_for_professional = $row["notes_for_professional"];
            $this->required_skills = $row["required_skills"];
            $this->required_credentials = $row["required_credentials"];
            $this->created_at = $row["created_at"];
            $this->updated_at = $row["updated_at"];
            // Joined facility details
            $this->facility_name = $row["facility_name"];
            $this->facility_logo = $row["facility_logo"];
            return $this;
        }
        return null;
    }

    // Update shift details
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET
                    shift_title = :shift_title,
                    profession_type_required = :profession_type_required,
                    specialty_required = :specialty_required,
                    job_description = :job_description,
                    shift_date = :shift_date,
                    start_time = :start_time,
                    end_time = :end_time,
                    duration_hours = :duration_hours,
                    hourly_rate = :hourly_rate,
                    bonus_offered = :bonus_offered,
                    status = :status,
                    notes_for_professional = :notes_for_professional,
                    required_skills = :required_skills,
                    required_credentials = :required_credentials,
                    updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id AND facility_user_id = :facility_user_id"; // Ensure only owner can update

        $stmt = $this->conn->prepare($query);

        // Sanitize (similar to create, ensure all fields are sanitized)
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->facility_user_id = htmlspecialchars(strip_tags($this->facility_user_id));
        // ... sanitize all other fields ...
        $this->shift_title = htmlspecialchars(strip_tags($this->shift_title));
        $this->profession_type_required = htmlspecialchars(strip_tags($this->profession_type_required));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":facility_user_id", $this->facility_user_id);
        // ... bind all other params ...
        $stmt->bindParam(":shift_title", $this->shift_title);
        $stmt->bindParam(":profession_type_required", $this->profession_type_required);
        $stmt->bindParam(":status", $this->status);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete a shift (or mark as cancelled)
    public function delete() {
        // Consider changing status to "cancelled" instead of hard delete
        // if applications exist.
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND facility_user_id = :facility_user_id";
        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->facility_user_id = htmlspecialchars(strip_tags($this->facility_user_id));

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":facility_user_id", $this->facility_user_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get all shifts (e.g., for browsing by professionals, with filters)
    public function getAllOpenShifts($filters = [], $limit = 10, $offset = 0) {
        $sql_conditions = [];
        $params = [];

        // Example filters (expand as needed)
        if (!empty($filters["profession_type"])) {
            $sql_conditions[] = "s.profession_type_required = :profession_type";
            $params[":profession_type"] = $filters["profession_type"];
        }
        if (!empty($filters["shift_date_from"])) {
            $sql_conditions[] = "s.shift_date >= :shift_date_from";
            $params[":shift_date_from"] = $filters["shift_date_from"];
        }
        if (!empty($filters["facility_id"])) {
            $sql_conditions[] = "s.facility_id = :facility_id";
            $params[":facility_id"] = $filters["facility_id"];
        }
        // Always filter by open status for browsing
        $sql_conditions[] = "s.status = 'open'";

        $where_clause = "";
        if (!empty($sql_conditions)) {
            $where_clause = " WHERE " . implode(" AND ", $sql_conditions);
        }

        $query = "SELECT s.*, f.facility_name, f.logo_image_path as facility_logo, u.address_city as facility_city, u.address_state as facility_state
                  FROM " . $this->table_name . " s
                  JOIN facilities f ON s.facility_id = f.id
                  JOIN users u ON f.user_id = u.id " . // Assuming facility address is on the facility's user record
                  $where_clause . 
                  " ORDER BY s.shift_date ASC, s.start_time ASC
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Count all open shifts (with filters)
    public function countAllOpenShifts($filters = []) {
        // Similar logic to getAllOpenShifts for $where_clause and $params
        $sql_conditions = [];
        $params = [];
        if (!empty($filters["profession_type"])) {
            $sql_conditions[] = "s.profession_type_required = :profession_type";
            $params[":profession_type"] = $filters["profession_type"];
        }
        $sql_conditions[] = "s.status = 'open'";
        $where_clause = "";
        if (!empty($sql_conditions)) {
            $where_clause = " WHERE " . implode(" AND ", $sql_conditions);
        }

        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " s " . $where_clause;
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row["total"] ?? 0;
    }
    
    // Get shifts posted by a specific facility user
    public function findByFacilityUserId($facility_user_id, $limit = 10, $offset = 0) {
        $query = "SELECT s.*, f.facility_name 
                  FROM " . $this->table_name . " s
                  JOIN facilities f ON s.facility_id = f.id
                  WHERE s.facility_user_id = :facility_user_id 
                  ORDER BY s.shift_date DESC, s.start_time DESC
                  LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":facility_user_id", $facility_user_id, PDO::PARAM_INT);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
