<?php
// app/controllers/ShiftController.php

class ShiftController {
    private $db;
    private $shift_model;
    private $facility_model; // To get facility_id from facility_user_id

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
        $this->shift_model = new Shift($this->db);
        $this->facility_model = new Facility($this->db); // Initialize Facility model
    }

    // Facility: Create a new shift
    public function create() {
        if (!is_logged_in() || get_current_user_type() !== "facility") {
            http_response_code(401); // Unauthorized or Forbidden
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
        if (!$data && isset($_POST)) $data = (object)$_POST; // Fallback for form-data

        // Get facility_id associated with the logged-in facility user
        $facility_profile = $this->facility_model->findByUserId($facility_user_id);
        if (!$facility_profile || !$facility_profile->id) {
            http_response_code(403); // Forbidden
            echo json_encode(["status" => "error", "message" => "Facility profile not found or not set up for this user."]);
            return;
        }
        $facility_id = $facility_profile->id;

        // Validate input (basic example, expand significantly)
        if (empty($data->shift_title) || empty($data->profession_type_required) || empty($data->shift_date) || 
            empty($data->start_time) || empty($data->end_time) || !isset($data->duration_hours) || !isset($data->hourly_rate)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Required fields are missing (title, profession, date, times, duration, rate)."]);
            return;
        }

        $this->shift_model->facility_user_id = $facility_user_id;
        $this->shift_model->facility_id = $facility_id;
        $this->shift_model->shift_title = $data->shift_title;
        $this->shift_model->profession_type_required = $data->profession_type_required;
        $this->shift_model->specialty_required = $data->specialty_required ?? null;
        $this->shift_model->job_description = $data->job_description ?? null;
        $this->shift_model->shift_date = $data->shift_date;
        $this->shift_model->start_time = $data->start_time;
        $this->shift_model->end_time = $data->end_time;
        $this->shift_model->duration_hours = (float)$data->duration_hours;
        $this->shift_model->hourly_rate = (float)$data->hourly_rate;
        $this->shift_model->bonus_offered = isset($data->bonus_offered) ? (float)$data->bonus_offered : null;
        $this->shift_model->status = "open"; // Default status
        $this->shift_model->notes_for_professional = $data->notes_for_professional ?? null;
        $this->shift_model->required_skills = $data->required_skills ?? null; // Expects text or JSON string
        $this->shift_model->required_credentials = $data->required_credentials ?? null; // Expects text or JSON string

        if ($this->shift_model->create()) {
            http_response_code(201); // Created
            echo json_encode(["status" => "success", "message" => "Shift created successfully.", "shift_id" => $this->shift_model->id]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to create shift."]);
        }
    }

    // Facility: List shifts posted by the logged-in facility user
    public function listByFacility() {
        if (!is_logged_in() || get_current_user_type() !== "facility") {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Authentication required as a facility."]);
            return;
        }
        $facility_user_id = get_current_user_id();
        // Add pagination parameters from $_GET if needed
        $limit = $_GET["limit"] ?? 10;
        $offset = $_GET["offset"] ?? 0;

        $shifts = $this->shift_model->findByFacilityUserId($facility_user_id, (int)$limit, (int)$offset);
        // TODO: Add count for pagination total

        http_response_code(200);
        echo json_encode(["status" => "success", "data" => $shifts]);
    }

    // Facility: Update a shift
    public function update($shift_id = null) {
        if (!is_logged_in() || get_current_user_type() !== "facility") {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Authentication required as a facility."]);
            return;
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") { // Or PUT
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "POST (or PUT) method required."]);
            return;
        }

        $facility_user_id = get_current_user_id();
        $data = json_decode(file_get_contents("php://input"));
        if (!$data && isset($_POST)) $data = (object)$_POST;
        
        if ($shift_id === null) $shift_id = $data->shift_id ?? null; // Get ID from payload if not in URL

        if (empty($shift_id)) {
            http_response_code(400); echo json_encode(["status" => "error", "message" => "Shift ID is required."]); return;
        }

        $existing_shift = $this->shift_model->findById($shift_id);
        if (!$existing_shift || $existing_shift->facility_user_id != $facility_user_id) {
            http_response_code(404); // Not Found or Forbidden
            echo json_encode(["status" => "error", "message" => "Shift not found or you do not have permission to update it."]);
            return;
        }

        // Populate model with existing data then overwrite with new data
        $this->shift_model->id = $existing_shift->id;
        $this->shift_model->facility_user_id = $existing_shift->facility_user_id;
        $this->shift_model->facility_id = $existing_shift->facility_id;
        
        $this->shift_model->shift_title = $data->shift_title ?? $existing_shift->shift_title;
        $this->shift_model->profession_type_required = $data->profession_type_required ?? $existing_shift->profession_type_required;
        $this->shift_model->specialty_required = $data->specialty_required ?? $existing_shift->specialty_required;
        $this->shift_model->job_description = $data->job_description ?? $existing_shift->job_description;
        $this->shift_model->shift_date = $data->shift_date ?? $existing_shift->shift_date;
        $this->shift_model->start_time = $data->start_time ?? $existing_shift->start_time;
        $this->shift_model->end_time = $data->end_time ?? $existing_shift->end_time;
        $this->shift_model->duration_hours = isset($data->duration_hours) ? (float)$data->duration_hours : $existing_shift->duration_hours;
        $this->shift_model->hourly_rate = isset($data->hourly_rate) ? (float)$data->hourly_rate : $existing_shift->hourly_rate;
        $this->shift_model->bonus_offered = isset($data->bonus_offered) ? (float)$data->bonus_offered : $existing_shift->bonus_offered;
        $this->shift_model->status = $data->status ?? $existing_shift->status;
        $this->shift_model->notes_for_professional = $data->notes_for_professional ?? $existing_shift->notes_for_professional;
        $this->shift_model->required_skills = $data->required_skills ?? $existing_shift->required_skills;
        $this->shift_model->required_credentials = $data->required_credentials ?? $existing_shift->required_credentials;

        if ($this->shift_model->update()) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Shift updated successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to update shift."]);
        }
    }

    // Facility: Delete (or cancel) a shift
    public function delete($shift_id = null) {
        if (!is_logged_in() || get_current_user_type() !== "facility") {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Authentication required as a facility."]);
            return;
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") { // Or DELETE
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "POST (or DELETE) method required."]);
            return;
        }

        $facility_user_id = get_current_user_id();
        if ($shift_id === null) { // Get ID from payload if not in URL
            $data = json_decode(file_get_contents("php://input"));
            if (!$data && isset($_POST)) $data = (object)$_POST;
            $shift_id = $data->shift_id ?? null;
        }

        if (empty($shift_id)) {
            http_response_code(400); echo json_encode(["status" => "error", "message" => "Shift ID is required."]); return;
        }

        $existing_shift = $this->shift_model->findById($shift_id);
        if (!$existing_shift || $existing_shift->facility_user_id != $facility_user_id) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Shift not found or you do not have permission to delete it."]);
            return;
        }
        
        // Instead of direct delete, consider changing status to "cancelled"
        // For now, using the model's delete method which does a hard delete.
        $this->shift_model->id = $existing_shift->id;
        $this->shift_model->facility_user_id = $existing_shift->facility_user_id; // For the delete method's check

        if ($this->shift_model->delete()) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Shift deleted successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to delete shift."]);
        }
    }

    // Professional: Browse open shifts
    public function browse() {
        // No login required to browse, or professional login required depending on policy
        // For now, let's assume no login required to browse.
        
        // Get filter parameters from $_GET
        $filters = [];
        if (!empty($_GET["profession_type"])) $filters["profession_type"] = $_GET["profession_type"];
        if (!empty($_GET["shift_date_from"])) $filters["shift_date_from"] = $_GET["shift_date_from"];
        // Add more filters: location (city/state/zip), specialty, facility_id, etc.
        
        $limit = $_GET["limit"] ?? 10;
        $offset = $_GET["offset"] ?? 0;

        $shifts = $this->shift_model->getAllOpenShifts($filters, (int)$limit, (int)$offset);
        $total_shifts = $this->shift_model->countAllOpenShifts($filters);
        
        // Add full URL for facility_logo
        foreach ($shifts as &$shift) {
            if (!empty($shift["facility_logo"])) {
                $shift["facility_logo_url"] = base_url($shift["facility_logo"]);
            }
        }

        http_response_code(200);
        echo json_encode([
            "status" => "success", 
            "data" => $shifts,
            "pagination" => [
                "total" => (int)$total_shifts,
                "limit" => (int)$limit,
                "offset" => (int)$offset,
                "current_page" => $offset / $limit + 1,
                "total_pages" => ceil($total_shifts / $limit)
            ]
        ]);
    }

    // Professional: View a single shift details
    public function view($shift_id = null) {
        if (empty($shift_id)) {
            http_response_code(400); echo json_encode(["status" => "error", "message" => "Shift ID is required."]); return;
        }

        $shift = $this->shift_model->findById($shift_id);
        if ($shift && $shift->id) {
             if (!empty($shift->facility_logo)) {
                $shift->facility_logo_url = base_url($shift->facility_logo);
            }
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $shift]);
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Shift not found."]);
        }
    }
    
    // TODO: Shift application logic (Professional applies for a shift)
    // TODO: Facility views applications for a shift
    // TODO: Facility accepts/rejects an application
    // TODO: Professional checks in/out of a shift
}
?>
