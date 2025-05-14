<?php
// /home/ubuntu/lamp_healthcare_staffing/implementation/app/services/ZelleService.php

require_once __DIR__ . 
'/../../core/Database.php
';
require_once __DIR__ . 
'/../../config.php
';

class ZelleService {
    private $db;
    private $zelleRecipientInfo;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->zelleRecipientInfo = defined(
'ZELLE_RECIPIENT_INFO
') ? ZELLE_RECIPIENT_INFO : 
''
;

        if (empty($this->zelleRecipientInfo)) {
            error_log("Zelle recipient information not configured.");
            // Potentially throw an error or handle this, as instructions can't be provided.
        }
    }

    /**
     * Retrieves the Zelle payment instructions.
     * @return array Containing success status and Zelle recipient information or an error message.
     */
    public function getPaymentInstructions() {
        if (empty($this->zelleRecipientInfo)) {
            return [
                "success" => false, 
                "message" => "Zelle payment information is not currently available. Please contact support."
            ];
        }
        return [
            "success" => true, 
            "recipient_info" => $this->zelleRecipientInfo,
            "instructions" => "Please send your payment via Zelle to: " . $this->zelleRecipientInfo . ". Include your shift ID or invoice number in the payment memo."
        ];
    }

    /**
     * Allows an administrator to manually confirm a Zelle payment for a specific transaction.
     * This would typically update a payment record in the database.
     *
     * @param int $paymentId The ID of the payment record to confirm.
     * @param string $transactionReference Optional Zelle transaction reference provided by the admin.
     * @param int $adminUserId The ID of the admin confirming the payment.
     * @return array Result of the confirmation attempt.
     */
    public function confirmPaymentByAdmin($paymentId, $transactionReference = null, $adminUserId) {
        if (empty($paymentId) || empty($adminUserId)) {
            return ["success" => false, "message" => "Payment ID and Admin User ID are required."];
        }

        // Check if the payment record exists and is pending Zelle confirmation
        try {
            $stmt = $this->db->prepare("SELECT id, payment_status, payment_method FROM payments WHERE id = ?");
            $stmt->execute([$paymentId]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$payment) {
                return ["success" => false, "message" => "Payment record not found."];
            }

            // Optional: Check if payment_method is 'ZELLE' and status is appropriate for confirmation
            if (strtoupper($payment[
'payment_method
']) !== 
'ZELLE
') {
                 // return ["success" => false, "message" => "This payment was not designated as a Zelle payment."];
            }
            if ($payment[
'payment_status
'] === 
'completed
') {
                 return ["success" => false, "message" => "This Zelle payment has already been marked as completed."];
            }

            // Update the payment status to 'completed' and store the reference
            $updateStmt = $this->db->prepare(
                "UPDATE payments SET payment_status = 'completed', transaction_id = ?, updated_at = NOW(), confirmed_by_admin_id = ? WHERE id = ?"
            );
            $success = $updateStmt->execute([
                $transactionReference, // Store Zelle reference if provided
                $adminUserId,
                $paymentId
            ]);

            if ($success) {
                // Log this action
                // Trigger any post-payment confirmation logic (e.g., notifications, escrow update)
                return ["success" => true, "message" => "Zelle payment confirmed successfully for payment ID: " . $paymentId];
            } else {
                error_log("Failed to update Zelle payment status for payment ID: " . $paymentId);
                return ["success" => false, "message" => "Database error while confirming Zelle payment."];
            }
        } catch (PDOException $e) {
            error_log("ZelleService confirmPaymentByAdmin PDOException: " . $e->getMessage());
            return ["success" => false, "message" => "A database error occurred."];
        }
    }
    
    /**
     * Records an intended Zelle payment, setting its status to 'pending_manual_verification'.
     *
     * @param int $userId The ID of the user initiating the payment intent.
     * @param int $shiftId The ID of the shift this payment is for.
     * @param float $amount The amount of the payment.
     * @param string $currency The currency of the payment.
     * @return array Result of recording the payment intent.
     */
    public function recordZellePaymentIntent($userId, $shiftId, $amount, $currency = 'USD') {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO payments (user_id, shift_id, amount, currency, payment_method, payment_status, created_at, updated_at) 
                 VALUES (?, ?, ?, ?, 'ZELLE', 'pending_manual_verification', NOW(), NOW())"
            );
            $success = $stmt->execute([$userId, $shiftId, $amount, $currency]);
            $paymentId = $this->db->lastInsertId();

            if ($success && $paymentId) {
                return ["success" => true, "payment_id" => $paymentId, "message" => "Zelle payment intent recorded. Awaiting manual verification."];
            } else {
                error_log("Failed to record Zelle payment intent for user ID: " . $userId . " and shift ID: " . $shiftId);
                return ["success" => false, "message" => "Database error while recording Zelle payment intent."];
            }
        } catch (PDOException $e) {
            error_log("ZelleService recordZellePaymentIntent PDOException: " . $e->getMessage());
            return ["success" => false, "message" => "A database error occurred while recording payment intent."];
        }
    }
}
?>
