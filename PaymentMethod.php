<?php
// app/models/PaymentMethod.php

class PaymentMethod {
    private $db;
    private $table = "payment_methods";
    private $user_table = "users";

    public function __construct($db) {
        $this->db = $db;
    }

    // Add a new payment method for a user
    public function create($user_id, $method_name, $provider, $account_details, $is_default = 0) {
        if (empty($user_id) || empty($method_name) || empty($provider) || empty($account_details)) {
            return ["status" => "error", "message" => "Missing required payment method details."];
        }

        // If this is set as default, unset other defaults for this user
        if ($is_default == 1) {
            $this->unsetDefaultMethods($user_id);
        }

        $query = "INSERT INTO " . $this->table . " (user_id, method_name, provider, account_details, is_default, created_at)
                  VALUES (:user_id, :method_name, :provider, :account_details, :is_default, NOW())";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindParam(":method_name", $method_name);
        $stmt->bindParam(":provider", $provider);
        // Consider encrypting account_details before storing
        $encrypted_details = $this->encryptDetails($account_details); // Placeholder for actual encryption
        $stmt->bindParam(":account_details", $encrypted_details);
        $stmt->bindParam(":is_default", $is_default, PDO::PARAM_INT);

        try {
            if ($stmt->execute()) {
                return ["status" => "success", "message" => "Payment method added successfully.", "method_id" => $this->db->lastInsertId()];
            }
            return ["status" => "error", "message" => "Failed to add payment method."];
        } catch (PDOException $e) {
            // Log error $e->getMessage();
            return ["status" => "error", "message" => "Database error: " . $e->getMessage()];
        }
    }

    // Get payment methods for a user
    public function getByUserId($user_id) {
        $query = "SELECT id, method_name, provider, account_details, is_default, created_at FROM " . $this->table . " 
                  WHERE user_id = :user_id ORDER BY is_default DESC, created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $methods = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Decrypt account_details before returning
        foreach ($methods as &$method) {
            $method["account_details"] = $this->decryptDetails($method["account_details"]); // Placeholder
        }
        return $methods;
    }

    // Get a single payment method by its ID and user ID
    public function getByIdAndUserId($method_id, $user_id) {
        $query = "SELECT id, method_name, provider, account_details, is_default FROM " . $this->table . " 
                  WHERE id = :method_id AND user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":method_id", $method_id, PDO::PARAM_INT);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $method = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($method) {
            $method["account_details"] = $this->decryptDetails($method["account_details"]); // Placeholder
        }
        return $method;
    }

    // Update a payment method
    public function update($method_id, $user_id, $method_name, $provider, $account_details, $is_default = null) {
        if (empty($method_id) || empty($user_id) || empty($method_name) || empty($provider) || empty($account_details)) {
            return ["status" => "error", "message" => "Missing required fields for update."];
        }

        // If setting as default, unset others
        if ($is_default !== null && $is_default == 1) {
            $this->unsetDefaultMethods($user_id, $method_id);
        }

        $query = "UPDATE " . $this->table . " SET method_name = :method_name, provider = :provider, account_details = :account_details";
        if ($is_default !== null) {
            $query .= ", is_default = :is_default";
        }
        $query .= " WHERE id = :method_id AND user_id = :user_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":method_name", $method_name);
        $stmt->bindParam(":provider", $provider);
        $encrypted_details = $this->encryptDetails($account_details); // Placeholder
        $stmt->bindParam(":account_details", $encrypted_details);
        if ($is_default !== null) {
            $stmt->bindParam(":is_default", $is_default, PDO::PARAM_INT);
        }
        $stmt->bindParam(":method_id", $method_id, PDO::PARAM_INT);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                return ["status" => "success", "message" => "Payment method updated successfully."];
            }
            return ["status" => "error", "message" => "Payment method not found or no changes made."];
        }
        return ["status" => "error", "message" => "Failed to update payment method."];
    }

    // Set a payment method as default
    public function setDefault($method_id, $user_id) {
        $this->unsetDefaultMethods($user_id, $method_id); // Unset others first
        $query = "UPDATE " . $this->table . " SET is_default = 1 WHERE id = :method_id AND user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":method_id", $method_id, PDO::PARAM_INT);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        if ($stmt->execute() && $stmt->rowCount() > 0) {
            return ["status" => "success", "message" => "Payment method set as default."];
        }
        return ["status" => "error", "message" => "Failed to set payment method as default."];
    }

    // Unset other default payment methods for a user
    private function unsetDefaultMethods($user_id, $exclude_method_id = null) {
        $query = "UPDATE " . $this->table . " SET is_default = 0 WHERE user_id = :user_id";
        if ($exclude_method_id) {
            $query .= " AND id != :exclude_method_id";
        }
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        if ($exclude_method_id) {
            $stmt->bindParam(":exclude_method_id", $exclude_method_id, PDO::PARAM_INT);
        }
        $stmt->execute();
    }

    // Delete a payment method
    public function delete($method_id, $user_id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :method_id AND user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":method_id", $method_id, PDO::PARAM_INT);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                return ["status" => "success", "message" => "Payment method deleted successfully."];
            }
            return ["status" => "error", "message" => "Payment method not found or you do not have permission to delete it."];
        }
        return ["status" => "error", "message" => "Failed to delete payment method."];
    }

    // Placeholder for encryption - REPLACE WITH STRONG ENCRYPTION
    private function encryptDetails($details) {
        // Example: return base64_encode(openssl_encrypt($details, "aes-256-cbc", "your-secret-key", 0, "your-iv-16bytes"));
        return "encrypted_" . $details; // DO NOT USE IN PRODUCTION
    }

    // Placeholder for decryption - REPLACE WITH STRONG ENCRYPTION
    private function decryptDetails($encrypted_details) {
        // Example: return openssl_decrypt(base64_decode($encrypted_details), "aes-256-cbc", "your-secret-key", 0, "your-iv-16bytes");
        if (strpos($encrypted_details, "encrypted_") === 0) {
            return substr($encrypted_details, strlen("encrypted_")); // DO NOT USE IN PRODUCTION
        }
        return $encrypted_details;
    }
}

