<?php
// app/models/User.php

// require_once BASE_PATH . "/implementation/core/Database.php"; // Already handled by bootstrap

class User {
    private $conn;
    private $table_name = "users";

    // User Properties
    public $id;
    public $email;
    public $password_hash;
    public $user_type; // professional, facility, admin
    public $full_name;
    public $phone_number;
    public $address_street;
    public $address_city;
    public $address_state;
    public $address_zip_code;
    public $profile_image_path;
    public $status; // active, inactive, suspended
    public $email_verified_at;
    public $password_reset_token;
    public $password_reset_expires_at;
    public $created_at;
    public $updated_at;

    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    // Create a new user
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET
                    email = :email,
                    password_hash = :password_hash,
                    user_type = :user_type,
                    full_name = :full_name,
                    phone_number = :phone_number,
                    address_street = :address_street,
                    address_city = :address_city,
                    address_state = :address_state,
                    address_zip_code = :address_zip_code,
                    status = :status";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password_hash = htmlspecialchars(strip_tags($this->password_hash)); // Password should already be hashed before calling create
        $this->user_type = htmlspecialchars(strip_tags($this->user_type));
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->phone_number = htmlspecialchars(strip_tags($this->phone_number ?? ""));
        $this->address_street = htmlspecialchars(strip_tags($this->address_street ?? ""));
        $this->address_city = htmlspecialchars(strip_tags($this->address_city ?? ""));
        $this->address_state = htmlspecialchars(strip_tags($this->address_state ?? ""));
        $this->address_zip_code = htmlspecialchars(strip_tags($this->address_zip_code ?? ""));
        $this->status = htmlspecialchars(strip_tags($this->status ?? "active"));

        // Bind parameters
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $this->password_hash);
        $stmt->bindParam(":user_type", $this->user_type);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":phone_number", $this->phone_number);
        $stmt->bindParam(":address_street", $this->address_street);
        $stmt->bindParam(":address_city", $this->address_city);
        $stmt->bindParam(":address_state", $this->address_state);
        $stmt->bindParam(":address_zip_code", $this->address_zip_code);
        $stmt->bindParam(":status", $this->status);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        // Print error if something goes wrong
        // error_log("User creation failed: " . implode(" ", $stmt->errorInfo()));
        return false;
    }

    // Check if email exists
    public function emailExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $this->email = htmlspecialchars(strip_tags($this->email));
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();
        $num = $stmt->rowCount();
        return $num > 0;
    }

    // Find user by email
    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $email = htmlspecialchars(strip_tags($email));
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row["id"];
            $this->email = $row["email"];
            $this->password_hash = $row["password_hash"];
            $this->user_type = $row["user_type"];
            $this->full_name = $row["full_name"];
            $this->phone_number = $row["phone_number"];
            $this->address_street = $row["address_street"];
            $this->address_city = $row["address_city"];
            $this->address_state = $row["address_state"];
            $this->address_zip_code = $row["address_zip_code"];
            $this->profile_image_path = $row["profile_image_path"];
            $this->status = $row["status"];
            $this->email_verified_at = $row["email_verified_at"];
            $this->created_at = $row["created_at"];
            $this->updated_at = $row["updated_at"];
            return $this;
        }
        return null;
    }

    // Find user by ID
    public function findById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $id = htmlspecialchars(strip_tags($id));
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row["id"];
            $this->email = $row["email"];
            $this->password_hash = $row["password_hash"]; // Be careful exposing this
            $this->user_type = $row["user_type"];
            $this->full_name = $row["full_name"];
            // ... populate all other properties ...
            $this->status = $row["status"];
            $this->created_at = $row["created_at"];
            return $this;
        }
        return null;
    }

    // Update user details
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET
                    full_name = :full_name,
                    phone_number = :phone_number,
                    address_street = :address_street,
                    address_city = :address_city,
                    address_state = :address_state,
                    address_zip_code = :address_zip_code,
                    profile_image_path = :profile_image_path,
                    status = :status,
                    updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->phone_number = htmlspecialchars(strip_tags($this->phone_number ?? ""));
        $this->address_street = htmlspecialchars(strip_tags($this->address_street ?? ""));
        $this->address_city = htmlspecialchars(strip_tags($this->address_city ?? ""));
        $this->address_state = htmlspecialchars(strip_tags($this->address_state ?? ""));
        $this->address_zip_code = htmlspecialchars(strip_tags($this->address_zip_code ?? ""));
        $this->profile_image_path = htmlspecialchars(strip_tags($this->profile_image_path ?? ""));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":phone_number", $this->phone_number);
        $stmt->bindParam(":address_street", $this->address_street);
        $stmt->bindParam(":address_city", $this->address_city);
        $stmt->bindParam(":address_state", $this->address_state);
        $stmt->bindParam(":address_zip_code", $this->address_zip_code);
        $stmt->bindParam(":profile_image_path", $this->profile_image_path);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        // error_log("User update failed: " . implode(" ", $stmt->errorInfo()));
        return false;
    }

    // Update user password
    public function updatePassword($new_password) {
        $query = "UPDATE " . $this->table_name . " SET password_hash = :password_hash, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->password_hash = password_hash($new_password, PASSWORD_BCRYPT);
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":password_hash", $this->password_hash);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Set password reset token
    public function setPasswordResetToken($token, $expires_at) {
        $query = "UPDATE " . $this->table_name . " SET password_reset_token = :token, password_reset_expires_at = :expires_at, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $this->id = htmlspecialchars(strip_tags($this->id));
        $token = htmlspecialchars(strip_tags($token));
        // expires_at should be a pre-formatted timestamp string

        $stmt->bindParam(":token", $token);
        $stmt->bindParam(":expires_at", $expires_at);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // Find user by password reset token
    public function findByPasswordResetToken($token) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE password_reset_token = :token AND password_reset_expires_at > NOW() LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $token = htmlspecialchars(strip_tags($token));
        $stmt->bindParam(":token", $token);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            // Populate user properties from $row as in findByEmail/findById
            $this->id = $row["id"];
            $this->email = $row["email"];
            // ... and so on for all properties
            return $this;
        }
        return null;
    }

    // Clear password reset token
    public function clearPasswordResetToken() {
        $query = "UPDATE " . $this->table_name . " SET password_reset_token = NULL, password_reset_expires_at = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    // Mark email as verified
    public function setEmailVerified() {
        $query = "UPDATE " . $this->table_name . " SET email_verified_at = CURRENT_TIMESTAMP, status = \'active\' WHERE id = :id";
        // You might also want to clear an email verification token if you use one
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }
    
    // TODO: Add methods for password update, email verification, password reset token handling, etc.
}
?>
