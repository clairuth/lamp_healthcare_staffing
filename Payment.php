<?php
// app/models/Payment.php

class Payment {
    private $db;
    private $table = "payments";
    private $user_table = "users";
    private $shift_table = "shifts";
    private $payment_method_table = "payment_methods";

    public function __construct($db) {
        $this->db = $db;
    }

    // Create a new payment record (initially in escrow)
    public function create($professional_id, $facility_id, $shift_id, $amount, $payment_method_id, $currency = "USD", $transaction_id = null, $status = "escrow") {
        if (empty($professional_id) || empty($facility_id) || empty($shift_id) || !is_numeric($amount) || $amount <= 0 || empty($payment_method_id)) {
            return ["status" => "error", "message" => "Missing or invalid payment details."];
        }

        $query = "INSERT INTO " . $this->table . " 
                    (professional_user_id, facility_user_id, shift_id, amount, currency, payment_method_id, transaction_id, status, created_at, updated_at)
                  VALUES
                    (:professional_id, :facility_id, :shift_id, :amount, :currency, :payment_method_id, :transaction_id, :status, NOW(), NOW())";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":professional_id", $professional_id, PDO::PARAM_INT);
        $stmt->bindParam(":facility_id", $facility_id, PDO::PARAM_INT);
        $stmt->bindParam(":shift_id", $shift_id, PDO::PARAM_INT);
        $stmt->bindParam(":amount", $amount);
        $stmt->bindParam(":currency", $currency);
        $stmt->bindParam(":payment_method_id", $payment_method_id, PDO::PARAM_INT);
        $stmt->bindParam(":transaction_id", $transaction_id);
        $stmt->bindParam(":status", $status);

        try {
            if ($stmt->execute()) {
                return ["status" => "success", "message" => "Payment initiated and placed in escrow.", "payment_id" => $this->db->lastInsertId()];
            }
            return ["status" => "error", "message" => "Failed to initiate payment."];
        } catch (PDOException $e) {
            // Log error $e->getMessage();
            return ["status" => "error", "message" => "Database error during payment initiation: " . $e->getMessage()];
        }
    }

    // Update payment status (e.g., release from escrow, refund)
    public function updateStatus($payment_id, $new_status, $admin_notes = null) {
        if (empty($payment_id) || empty($new_status)) {
            return ["status" => "error", "message" => "Payment ID or new status cannot be empty."];
        }
        // Validate new_status against allowed values: completed, refunded, disputed, cancelled
        $allowed_statuses = ["completed", "refunded", "disputed", "cancelled", "escrow_released"];
        if (!in_array($new_status, $allowed_statuses)) {
            return ["status" => "error", "message" => "Invalid payment status provided."];
        }

        $query = "UPDATE " . $this->table . " SET status = :new_status, admin_notes = :admin_notes, updated_at = NOW() WHERE id = :payment_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":new_status", $new_status);
        $stmt->bindParam(":admin_notes", $admin_notes);
        $stmt->bindParam(":payment_id", $payment_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                return ["status" => "success", "message" => "Payment status updated successfully."];
            }
            return ["status" => "error", "message" => "Payment record not found or status not changed."];
        }
        return ["status" => "error", "message" => "Failed to update payment status."];
    }

    // Get payment details by ID
    public function getById($payment_id) {
        $query = "SELECT p.*, 
                         prof.username as professional_username, prof_details.full_name as professional_name, 
                         fac.username as facility_username, fac_details.facility_name as facility_name,
                         s.shift_date, s.start_time, s.end_time,
                         pm.method_name, pm.provider
                  FROM " . $this->table . " p
                  JOIN " . $this->user_table . " prof ON p.professional_user_id = prof.id
                  LEFT JOIN healthcare_professionals prof_details ON prof.id = prof_details.user_id
                  JOIN " . $this->user_table . " fac ON p.facility_user_id = fac.id
                  LEFT JOIN facilities fac_details ON fac.id = fac_details.user_id
                  JOIN " . $this->shift_table . " s ON p.shift_id = s.id
                  JOIN " . $this->payment_method_table . " pm ON p.payment_method_id = pm.id
                  WHERE p.id = :payment_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":payment_id", $payment_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get payments for a specific professional
    public function getByProfessional($professional_id) {
        $query = "SELECT p.*, fac_details.facility_name, s.shift_date, s.start_time, pm.method_name 
                  FROM " . $this->table . " p
                  LEFT JOIN facilities fac_details ON p.facility_user_id = fac_details.user_id
                  JOIN " . $this->shift_table . " s ON p.shift_id = s.id
                  JOIN " . $this->payment_method_table . " pm ON p.payment_method_id = pm.id
                  WHERE p.professional_user_id = :professional_id ORDER BY p.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":professional_id", $professional_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get payments initiated by a specific facility
    public function getByFacility($facility_id) {
        $query = "SELECT p.*, prof_details.full_name as professional_name, s.shift_date, s.start_time, pm.method_name 
                  FROM " . $this->table . " p
                  LEFT JOIN healthcare_professionals prof_details ON p.professional_user_id = prof_details.user_id
                  JOIN " . $this->shift_table . " s ON p.shift_id = s.id
                  JOIN " . $this->payment_method_table . " pm ON p.payment_method_id = pm.id
                  WHERE p.facility_user_id = :facility_id ORDER BY p.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":facility_id", $facility_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all payments (for admin)
    public function getAll($limit = 10, $offset = 0) {
        $query = "SELECT p.*, 
                         prof.username as professional_username, prof_details.full_name as professional_name, 
                         fac.username as facility_username, fac_details.facility_name as facility_name,
                         s.shift_date, s.start_time, s.end_time,
                         pm.method_name, pm.provider
                  FROM " . $this->table . " p
                  JOIN " . $this->user_table . " prof ON p.professional_user_id = prof.id
                  LEFT JOIN healthcare_professionals prof_details ON prof.id = prof_details.user_id
                  JOIN " . $this->user_table . " fac ON p.facility_user_id = fac.id
                  LEFT JOIN facilities fac_details ON fac.id = fac_details.user_id
                  JOIN " . $this->shift_table . " s ON p.shift_id = s.id
                  JOIN " . $this->payment_method_table . " pm ON p.payment_method_id = pm.id
                  ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll() {
        $query = "SELECT COUNT(*) FROM " . $this->table;
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // TODO: Add methods for escrow release logic (e.g., after X days or admin approval)
    // TODO: Add methods for dispute handling
}

