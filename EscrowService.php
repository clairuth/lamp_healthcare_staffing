<?php
// /home/ubuntu/lamp_healthcare_staffing/implementation/app/services/EscrowService.php

require_once __DIR__ . 
'/../../core/Database.php
';
require_once __DIR__ . 
'/../../config.php
';
// Potentially require other services if direct interaction is needed, e.g., for initiating payouts
// require_once __DIR__ . 
'/PayPalService.php
'; 

class EscrowService {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Creates an escrow transaction record.
     * This is typically called after a payment is successfully made by a facility for a shift.
     *
     * @param int $shiftId The ID of the shift.
     * @param int $facilityId The ID of the facility (buyer).
     * @param int $professionalId The ID of the healthcare professional (seller).
     * @param int $paymentId The ID of the payment record from the payments table.
     * @param float $amount The amount held in escrow.
     * @param string $currency The currency of the amount.
     * @return array Result of the creation attempt.
     */
    public function createEscrowTransaction($shiftId, $facilityId, $professionalId, $paymentId, $amount, $currency) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO escrow_transactions (shift_id, facility_id, professional_id, payment_id, amount, currency, escrow_status, funded_at, created_at, updated_at) 
                 VALUES (?, ?, ?, ?, ?, ?, 'funded', NOW(), NOW(), NOW())"
            );
            $success = $stmt->execute([$shiftId, $facilityId, $professionalId, $paymentId, $amount, $currency]);
            $escrowId = $this->db->lastInsertId();

            if ($success && $escrowId) {
                // Update the main payment record to link to this escrow transaction
                $paymentUpdateStmt = $this->db->prepare("UPDATE payments SET escrow_transaction_id = ? WHERE id = ?");
                $paymentUpdateStmt->execute([$escrowId, $paymentId]);
                
                return ["success" => true, "escrow_id" => $escrowId, "message" => "Escrow transaction created successfully."];
            } else {
                error_log("Failed to create escrow transaction for payment ID: " . $paymentId);
                return ["success" => false, "message" => "Database error while creating escrow transaction."];
            }
        } catch (PDOException $e) {
            error_log("EscrowService createEscrowTransaction PDOException: " . $e->getMessage());
            return ["success" => false, "message" => "A database error occurred while creating escrow transaction."];
        }
    }

    /**
     * Updates the status of an escrow transaction.
     *
     * @param int $escrowId The ID of the escrow transaction.
     * @param string $newStatus The new status (e.g., 'pending_release', 'released', 'disputed', 'refunded', 'cancelled').
     * @param int|null $adminUserId Optional ID of the admin performing the update.
     * @param string|null $notes Optional notes for the status change.
     * @return array Result of the update attempt.
     */
    public function updateEscrowStatus($escrowId, $newStatus, $adminUserId = null, $notes = null) {
        $allowedStatuses = ['funded', 'pending_release', 'released', 'disputed', 'refunded', 'cancelled'];
        if (!in_array($newStatus, $allowedStatuses)) {
            return ["success" => false, "message" => "Invalid escrow status provided."];
        }

        try {
            // Build the SQL query dynamically based on what needs to be updated
            $sql = "UPDATE escrow_transactions SET escrow_status = ?, updated_at = NOW()";
            $params = [$newStatus];

            if ($newStatus === 'released') {
                $sql .= ", released_at = NOW()";
            } elseif ($newStatus === 'disputed') {
                $sql .= ", dispute_opened_at = NOW()";
            } elseif ($newStatus === 'refunded' || $newStatus === 'cancelled') {
                // Potentially add other specific timestamp updates here
            }
            
            if ($notes !== null) {
                $sql .= ", notes = CONCAT(IFNULL(notes, ''), CHAR(10 USING utf8mb4), ?)"; // Append notes
                $params[] = "Status changed to " . $newStatus . " by admin " . $adminUserId . " at " . date('Y-m-d H:i:s') . ": " . $notes;
            }

            $sql .= " WHERE id = ?";
            $params[] = $escrowId;

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute($params);

            if ($success) {
                // If status is 'released', trigger payout logic (conceptually here, actual payout via Payment Service)
                if ($newStatus === 'released') {
                    // This is where you might call a method to initiate payout to the professional
                    // For example: $this->initiatePayoutForReleasedEscrow($escrowId);
                }
                return ["success" => true, "message" => "Escrow status updated to '" . $newStatus . "'."];
            } else {
                error_log("Failed to update escrow status for ID: " . $escrowId);
                return ["success" => false, "message" => "Database error while updating escrow status."];
            }
        } catch (PDOException $e) {
            error_log("EscrowService updateEscrowStatus PDOException: " . $e->getMessage());
            return ["success" => false, "message" => "A database error occurred while updating escrow status."];
        }
    }

    /**
     * Retrieves details of a specific escrow transaction.
     *
     * @param int $escrowId The ID of the escrow transaction.
     * @return array Escrow transaction details or error message.
     */
    public function getEscrowTransactionDetails($escrowId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM escrow_transactions WHERE id = ?");
            $stmt->execute([$escrowId]);
            $escrow = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($escrow) {
                return ["success" => true, "data" => $escrow];
            } else {
                return ["success" => false, "message" => "Escrow transaction not found."];
            }
        } catch (PDOException $e) {
            error_log("EscrowService getEscrowTransactionDetails PDOException: " . $e->getMessage());
            return ["success" => false, "message" => "A database error occurred."];
        }
    }

    /**
     * Marks an escrow transaction that conditions for release have been met.
     * This would typically be after a shift is confirmed completed by both parties or admin.
     *
     * @param int $escrowId The ID of the escrow transaction.
     * @param int|null $confirmedByUserId ID of the user/admin confirming conditions.
     * @return array Result of the operation.
     */
    public function markReleaseConditionsMet($escrowId, $confirmedByUserId = null) {
        // First, update status to 'pending_release'
        $updateResult = $this->updateEscrowStatus($escrowId, 'pending_release', $confirmedByUserId, 'Release conditions met.');
        
        if ($updateResult["success"]) {
            // Additionally, set the release_conditions_met_at timestamp
            try {
                $stmt = $this->db->prepare("UPDATE escrow_transactions SET release_conditions_met_at = NOW() WHERE id = ? AND escrow_status = 'pending_release'");
                $stmt->execute([$escrowId]);
                return ["success" => true, "message" => "Escrow conditions for release marked as met."];
            } catch (PDOException $e) {
                error_log("EscrowService markReleaseConditionsMet PDOException: " . $e->getMessage());
                return ["success" => false, "message" => "Database error while marking release conditions met."];
            }
        } else {
            return $updateResult; // Return the error from updateEscrowStatus
        }
    }

    // Further methods for dispute resolution, refunds, etc., would follow a similar pattern,
    // updating the escrow_status and relevant timestamps, and logging actions.
    // For example: handleDispute($escrowId, $reason, $disputingUserId)
    // resolveDispute($escrowId, $resolutionNotes, $adminUserId, $resolutionAction ('release_to_professional' or 'refund_to_facility'))

}
?>
