<?php
// app/controllers/ShiftApplicationController.php

class ShiftApplicationController {
    private $db;
    private $shift_application_model;
    private $shift_model; // To check shift status, ownership

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
        $this->shift_application_model = new ShiftApplication($this->db);
        $this->shift_model = new Shift($this->db); // For shift validation
    }

    // Professional: Apply for a shift
    public function apply($shift_id = null) {
        if (!is_logged_in() || get_current_user_type() !== "professional") {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Authentication required as a professional."]);
            return;
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "POST method required."]);
            return;
        }

        $professional_user_id = get_current_user_id();
        $data = json_decode(file_get_contents("php://input"));
        if (!$data && isset($_POST)) $data = (object)$_POST;

        if ($shift_id === null) $shift_id = $data->shift_id ?? null;
        $application_notes = $data->application_notes ?? "";

        if (empty($shift_id)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Shift ID is required."]);
            return;
        }

        // Check if shift exists and is open
        $shift = $this->shift_model->findById($shift_id);
        if (!$shift || $shift->status !== "open") {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Shift not found or is no longer open for applications."]);
            return;
        }

        // Check if already applied (model handles this, but good to double check in controller)
        if ($this->shift_application_model->hasAlreadyApplied($shift_id, $professional_user_id)) {
            http_response_code(409); // Conflict
            echo json_encode(["status" => "error", "message" => "You have already applied for this shift."]);
            return;
        }

        $this->shift_application_model->shift_id = $shift_id;
        $this->shift_application_model->professional_user_id = $professional_user_id;
        $this->shift_application_model->application_status = "applied";
        $this->shift_application_model->application_notes = $application_notes;

        if ($this->shift_application_model->create()) {
            http_response_code(201); // Created
            echo json_encode(["status" => "success", "message" => "Successfully applied for the shift.", "application_id" => $this->shift_application_model->id]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to apply for the shift. You might have already applied or an error occurred."]);
        }
    }

    // Professional: List their applications
    public function listByProfessional() {
        if (!is_logged_in() || get_current_user_type() !== "professional") {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Authentication required as a professional."]);
            return;
        }
        $professional_user_id = get_current_user_id();
        $applications = $this->shift_application_model->findByProfessionalId($professional_user_id);

        http_response_code(200);
        echo json_encode(["status" => "success", "data" => $applications]);
    }

    // Professional: Withdraw an application
    public function withdraw($application_id = null) {
        if (!is_logged_in() || get_current_user_type() !== "professional") {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Authentication required as a professional."]);
            return;
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") { // Or DELETE
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "POST (or DELETE) method required."]);
            return;
        }

        $professional_user_id = get_current_user_id();
        $data = json_decode(file_get_contents("php://input"));
        if (!$data && isset($_POST)) $data = (object)$_POST;
        if ($application_id === null) $application_id = $data->application_id ?? null;

        if (empty($application_id)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Application ID is required."]);
            return;
        }

        $application = $this->shift_application_model->findById($application_id);
        if (!$application || $application->professional_user_id != $professional_user_id) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Application not found or you do not have permission."]);
            return;
        }
        // Can only withdraw if status is 'applied'
        if ($application->application_status !== 'applied') {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Application cannot be withdrawn as it is no longer in 'applied' status."]);
            return;
        }

        $this->shift_application_model->id = $application_id;
        $this->shift_application_model->application_status = "withdrawn";
        $this->shift_application_model->application_notes = "Withdrawn by professional.";

        if ($this->shift_application_model->updateStatus()) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Application withdrawn successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to withdraw application."]);
        }
    }

    // Facility: List applications for a specific shift
    public function listByShift($shift_id = null) {
        if (!is_logged_in() || get_current_user_type() !== "facility") {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Authentication required as a facility."]);
            return;
        }
        $facility_user_id = get_current_user_id();

        if (empty($shift_id)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Shift ID is required."]);
            return;
        }

        // Verify facility owns the shift
        $shift = $this->shift_model->findById($shift_id);
        if (!$shift || $shift->facility_user_id != $facility_user_id) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Shift not found or you do not own this shift."]);
            return;
        }

        $applications = $this->shift_application_model->findByShiftId($shift_id);
        http_response_code(200);
        echo json_encode(["status" => "success", "data" => $applications]);
    }

    // Facility: Accept or Reject an application
    public function updateApplicationStatus($application_id = null) {
        if (!is_logged_in() || get_current_user_type() !== "facility") {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Authentication required as a facility."]);
            return;
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "POST method required."]);
            return;
        }

        $facility_user_id = get_current_user_id();
        $data = json_decode(file_get_contents("php://input"));
        if (!$data && isset($_POST)) $data = (object)$_POST;
        
        if ($application_id === null) $application_id = $data->application_id ?? null;
        $new_status = $data->new_status ?? null; // expecting 'accepted' or 'rejected'
        $notes = $data->notes ?? "";

        if (empty($application_id) || empty($new_status)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Application ID and new status are required."]);
            return;
        }
        if (!in_array($new_status, ["accepted", "rejected"])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid status. Must be 'accepted' or 'rejected'."]);
            return;
        }

        $application = $this->shift_application_model->findById($application_id);
        if (!$application) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Application not found."]);
            return;
        }

        // Verify facility owns the shift associated with this application
        $shift = $this->shift_model->findById($application->shift_id);
        if (!$shift || $shift->facility_user_id != $facility_user_id) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "You do not have permission to modify this application."]);
            return;
        }
        
        // Check if shift is still open and application is 'applied'
        if ($shift->status !== 'open' && $new_status === 'accepted') {
             http_response_code(403);
             echo json_encode(["status" => "error", "message" => "Cannot accept application for a shift that is not open."]);
             return;
        }
        if ($application->application_status !== 'applied') {
             http_response_code(403);
             echo json_encode(["status" => "error", "message" => "Application status is not 'applied', cannot change."]);
             return;
        }

        $this->shift_application_model->id = $application_id;
        $this->shift_application_model->application_status = $new_status;
        $this->shift_application_model->application_notes = $notes;
        $this->shift_application_model->shift_id = $application->shift_id; // Needed for the model's updateStatus to update shift if accepted

        if ($this->shift_application_model->updateStatus()) {
            // If accepted, potentially reject other 'applied' applications for this shift
            if ($new_status === 'accepted') {
                // This logic could be expanded. For now, just updates the shift to 'filled'.
                // The model's updateStatus already handles setting shift to 'filled'.
            }
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Application status updated to '{$new_status}'."]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to update application status."]);
        }
    }
}
?>
