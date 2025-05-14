<?php
// app/models/Facility.php

class Facility {
    private $conn;
    private $table_name = "facilities";

    // Properties
    public $id;
    public $user_id; // Foreign key to users table (facility admin user)
    public $facility_name;
    public $facility_type;
    public $facility_license_number;
    public $tax_id;
    public $contact_person_name;
    public $contact_person_email;
    public $contact_person_phone;
    public $website_url;
    public $description;
    public $logo_image_path;
    public $verification_status; // pending, verified, rejected
    public $created_at;
    public $updated_at;

    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    // Create a new facility profile (linked to an existing user)
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET
                    user_id = :user_id,
                    facility_name = :facility_name,
                    facility_type = :facility_type,
                    facility_license_number = :facility_license_number,
                    tax_id = :tax_id,
                    contact_person_name = :contact_person_name,
                    contact_person_email = :contact_person_email,
                    contact_person_phone = :contact_person_phone,
                    website_url = :website_url,
                    description = :description,
                    logo_image_path = :logo_image_path,
                    verification_status = :verification_status";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->facility_name = htmlspecialchars(strip_tags($this->facility_name));
        $this->facility_type = htmlspecialchars(strip_tags($this->facility_type ?? ""));
        $this->facility_license_number = htmlspecialchars(strip_tags($this->facility_license_number ?? ""));
        $this->tax_id = htmlspecialchars(strip_tags($this->tax_id ?? ""));
        $this->contact_person_name = htmlspecialchars(strip_tags($this->contact_person_name ?? ""));
        $this->contact_person_email = htmlspecialchars(strip_tags($this->contact_person_email ?? ""));
        $this->contact_person_phone = htmlspecialchars(strip_tags($this->contact_person_phone ?? ""));
        $this->website_url = htmlspecialchars(strip_tags($this->website_url ?? ""));
        $this->description = htmlspecialchars(strip_tags($this->description ?? ""));
        $this->logo_image_path = htmlspecialchars(strip_tags($this->logo_image_path ?? ""));
        $this->verification_status = htmlspecialchars(strip_tags($this->verification_status ?? "pending"));

        // Bind parameters
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":facility_name", $this->facility_name);
        $stmt->bindParam(":facility_type", $this->facility_type);
        $stmt->bindParam(":facility_license_number", $this->facility_license_number);
        $stmt->bindParam(":tax_id", $this->tax_id);
        $stmt->bindParam(":contact_person_name", $this->contact_person_name);
        $stmt->bindParam(":contact_person_email", $this->contact_person_email);
        $stmt->bindParam(":contact_person_phone", $this->contact_person_phone);
        $stmt->bindParam(":website_url", $this->website_url);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":logo_image_path", $this->logo_image_path);
        $stmt->bindParam(":verification_status", $this->verification_status);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        // error_log("Facility creation failed: " . implode(" ", $stmt->errorInfo()));
        return false;
    }

    // Find facility profile by user_id
    public function findByUserId($user_id) {
        $query = "SELECT f.*, u.full_name AS admin_full_name, u.email AS admin_email, u.phone_number AS admin_phone_number
                  FROM " . $this->table_name . " f
                  JOIN users u ON f.user_id = u.id
                  WHERE f.user_id = :user_id LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $user_id = htmlspecialchars(strip_tags($user_id));
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row["id"];
            $this->user_id = $row["user_id"];
            $this->facility_name = $row["facility_name"];
            $this->facility_type = $row["facility_type"];
            $this->facility_license_number = $row["facility_license_number"];
            $this->tax_id = $row["tax_id"];
            $this->contact_person_name = $row["contact_person_name"];
            $this->contact_person_email = $row["contact_person_email"];
            $this->contact_person_phone = $row["contact_person_phone"];
            $this->website_url = $row["website_url"];
            $this->description = $row["description"];
            $this->logo_image_path = $row["logo_image_path"];
            $this->verification_status = $row["verification_status"];
            $this->created_at = $row["created_at"];
            $this->updated_at = $row["updated_at"];
            // You can also populate joined admin user details if needed
            return $this;
        }
        return null;
    }

    // Find facility by its own ID
    public function findById($facility_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $facility_id = htmlspecialchars(strip_tags($facility_id));
        $stmt->bindParam(":id", $facility_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            // Populate all properties similar to findByUserId
            $this->id = $row["id"];
            $this->user_id = $row["user_id"];
            $this->facility_name = $row["facility_name"];
            // ... and so on
            return $this;
        }
        return null;
    }

    // Update facility profile
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET
                    facility_name = :facility_name,
                    facility_type = :facility_type,
                    facility_license_number = :facility_license_number,
                    tax_id = :tax_id,
                    contact_person_name = :contact_person_name,
                    contact_person_email = :contact_person_email,
                    contact_person_phone = :contact_person_phone,
                    website_url = :website_url,
                    description = :description,
                    logo_image_path = :logo_image_path,
                    verification_status = :verification_status,
                    updated_at = CURRENT_TIMESTAMP
                  WHERE user_id = :user_id"; // Or use facility id if preferred

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->facility_name = htmlspecialchars(strip_tags($this->facility_name));
        // ... sanitize other fields ...
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));

        // Bind
        $stmt->bindParam(":facility_name", $this->facility_name);
        // ... bind other params ...
        $stmt->bindParam(":user_id", $this->user_id);

        if ($stmt->execute()) {
            return true;
        }
        // error_log("Facility update failed: " . implode(" ", $stmt->errorInfo()));
        return false;
    }
    
    // Method to get all facilities (e.g., for admin or public listing)
    public function getAll($limit = 10, $offset = 0) {
        $query = "SELECT f.*, u.full_name AS admin_full_name, u.email AS admin_email 
                  FROM " . $this->table_name . " f
                  JOIN users u ON f.user_id = u.id
                  ORDER BY f.facility_name ASC
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Method to count all facilities
    public function countAll() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row["total"] ?? 0;
    }
}
?>
