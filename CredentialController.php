<?php
// app/controllers/CredentialController.php

class CredentialController {
    private $db;
    private $credential_model;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
        $this->credential_model = new Credential($this->db);
    }

    // List credentials for the logged-in professional
    public function list() {
        if (!is_logged_in() || get_current_user_type() !== "professional") {
            http_response_code(401); // Unauthorized or Forbidden
            echo json_encode(["status" => "error", "message" => "Authentication required as a professional."]);
            return;
        }

        $professional_user_id = get_current_user_id();
        $credentials = $this->credential_model->findByProfessionalUserId($professional_user_id);

        if ($credentials) {
            // Add full URL for file_path
            foreach ($credentials as &$cred) {
                if (!empty($cred["file_path"])) {
                    $cred["file_url"] = base_url($cred["file_path"]);
                }
            }
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $credentials]);
        } else {
            http_response_code(200); // Success, but no data
            echo json_encode(["status" => "success", "data" => [], "message" => "No credentials found."]);
        }
    }

    // Upload a new credential
    public function upload() {
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
        $data = (object)$_POST; // Text fields
        $file = $_FILES["credential_file"] ?? null; // Uploaded file

        // Validate input (basic example)
        if (empty($data->credential_type) || empty($data->credential_name) || empty($file)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Credential type, name, and file are required."]);
            return;
        }

        // File upload handling
        $upload_dir = UPLOAD_DIR . "credentials/"; // Ensure UPLOAD_DIR is defined in config.php
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $tmp_name = $file["tmp_name"];
        $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        // Create a more unique filename to avoid collisions
        $safe_filename = "cred_" . $professional_user_id . "_" . uniqid() . "_" . preg_replace("/[^a-zA-Z0-9_.-]/", "", basename($file["name"]));
        $destination = $upload_dir . $safe_filename;

        // Basic validation (add more robust checks: size, actual MIME type)
        $allowed_extensions = ["pdf", "jpg", "jpeg", "png", "doc", "docx"];
        $max_file_size = 10 * 1024 * 1024; // 10MB

        if (!in_array($file_extension, $allowed_extensions)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid file type. Allowed: " . implode(", ", $allowed_extensions)]);
            return;
        }
        if ($file["size"] > $max_file_size) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "File size exceeds limit of " . ($max_file_size / 1024 / 1024) . "MB."]);
            return;
        }

        if (move_uploaded_file($tmp_name, $destination)) {
            $this->credential_model->professional_user_id = $professional_user_id;
            $this->credential_model->credential_type = $data->credential_type;
            $this->credential_model->credential_name = $data->credential_name;
            $this->credential_model->issuing_organization = $data->issuing_organization ?? null;
            $this->credential_model->license_number = $data->license_number ?? null;
            $this->credential_model->issue_date = !empty($data->issue_date) ? $data->issue_date : null;
            $this->credential_model->expiration_date = !empty($data->expiration_date) ? $data->expiration_date : null;
            $this->credential_model->file_path = "uploads/credentials/" . $safe_filename; // Relative to public dir
            $this->credential_model->verification_status = "pending";

            if ($this->credential_model->create()) {
                http_response_code(201); // Created
                echo json_encode(["status" => "success", "message" => "Credential uploaded successfully.", "credential_id" => $this->credential_model->id]);
            } else {
                // If DB insert fails, attempt to delete uploaded file
                if (file_exists($destination)) unlink($destination);
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => "Failed to save credential to database."]);
            }
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to upload file."]);
        }
    }

    // Delete a credential
    public function delete($credential_id = null) {
        if (!is_logged_in() || get_current_user_type() !== "professional") {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Authentication required as a professional."]);
            return;
        }

        if ($_SERVER["REQUEST_METHOD"] !== "POST") { // Or DELETE method
             http_response_code(405);
             echo json_encode(["status" => "error", "message" => "POST (or DELETE) method required."]);
             return;
        }
        
        // If ID is not in URL, try to get from POST data (e.g. from a form submission)
        if ($credential_id === null) {
            $data = json_decode(file_get_contents("php://input"));
            if (!$data && isset($_POST)) $data = (object)$_POST;
            $credential_id = $data->credential_id ?? null;
        }

        if (empty($credential_id)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Credential ID is required."]);
            return;
        }

        $professional_user_id = get_current_user_id();
        $credential = $this->credential_model->findById($credential_id);

        if (!$credential || $credential->professional_user_id != $professional_user_id) {
            http_response_code(404); // Not Found or Forbidden
            echo json_encode(["status" => "error", "message" => "Credential not found or you do not have permission to delete it."]);
            return;
        }

        // Attempt to delete the file first
        $file_deleted_successfully = true;
        if ($credential->file_path && file_exists(BASE_PATH . "/public/" . $credential->file_path)) {
            if (!unlink(BASE_PATH . "/public/" . $credential->file_path)) {
                $file_deleted_successfully = false;
                // Log error, but might still proceed to delete DB record
                error_log("Failed to delete credential file: " . BASE_PATH . "/public/" . $credential->file_path);
            }
        }

        if ($credential->delete()) { // The model's delete method needs to be called on the instance
            http_response_code(200);
            $message = "Credential deleted successfully.";
            if (!$file_deleted_successfully) {
                $message .= " However, the associated file could not be removed from storage.";
            }
            echo json_encode(["status" => "success", "message" => $message]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to delete credential from database."]);
        }
    }
    
    // Admin: List all credentials (or by status, user etc. - needs more params and checks)
    public function adminListAll() {
        // TODO: Add admin role check
        // if (!is_logged_in() || get_current_user_type() !== "admin") { ... }
        
        // This is a placeholder - a real admin list would have pagination, filters etc.
        $query = "SELECT c.*, u.full_name as professional_name, u.email as professional_email 
                  FROM " . $this->credential_model->table_name . " c 
                  JOIN users u ON c.professional_user_id = u.id 
                  ORDER BY c.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $credentials = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($credentials as &$cred) {
            if (!empty($cred["file_path"])) {
                $cred["file_url"] = base_url($cred["file_path"]);
            }
        }

        http_response_code(200);
        echo json_encode(["status" => "success", "data" => $credentials]);
    }

    // Admin: Update verification status of a credential
    public function updateVerification($credential_id = null) {
        // TODO: Add admin role check
        // if (!is_logged_in() || get_current_user_type() !== "admin") { ... }

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
             http_response_code(405); echo json_encode(["status" => "error", "message" => "POST method required."]); return;
        }

        $admin_user_id = get_current_user_id(); // Assuming admin is logged in
        $data = json_decode(file_get_contents("php://input"));
        if (!$data && isset($_POST)) $data = (object)$_POST;

        if ($credential_id === null) $credential_id = $data->credential_id ?? null;
        $new_status = $data->verification_status ?? null;
        $notes = $data->verification_notes ?? null;

        if (empty($credential_id) || empty($new_status)) {
            http_response_code(400); echo json_encode(["status" => "error", "message" => "Credential ID and new status are required."]); return;
        }
        if (!in_array($new_status, ["pending", "verified", "rejected", "expired"])) {
            http_response_code(400); echo json_encode(["status" => "error", "message" => "Invalid verification status."]); return;
        }

        $credential = $this->credential_model->findById($credential_id);
        if (!$credential) {
            http_response_code(404); echo json_encode(["status" => "error", "message" => "Credential not found."]); return;
        }

        $credential->verification_status = $new_status;
        $credential->verification_notes = $notes;
        // The model's updateVerificationStatus method will set verified_by_user_id and verified_at
        if ($credential->updateVerificationStatus($admin_user_id)) {
            http_response_code(200); echo json_encode(["status" => "success", "message" => "Credential verification status updated."]);
        } else {
            http_response_code(500); echo json_encode(["status" => "error", "message" => "Failed to update credential status."]);
        }
    }
}
?>
