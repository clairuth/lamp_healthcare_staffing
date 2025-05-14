<?php
// app/controllers/UserProfileController.php

class UserProfileController {
    private $db;
    private $user_model;
    private $professional_model;
    private $facility_model;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
        $this->user_model = new User($this->db);
        $this->professional_model = new HealthcareProfessional($this->db);
        $this->facility_model = new Facility($this->db);
    }

    // Get user profile (combined general user info and role-specific info)
    public function getProfile() {
        if (!is_logged_in()) {
            http_response_code(401); // Unauthorized
            echo json_encode(["status" => "error", "message" => "Authentication required."]);
            return;
        }

        $user_id = get_current_user_id();
        $user_type = get_current_user_type();

        $user_data = $this->user_model->findById($user_id);
        if (!$user_data) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "User not found."]);
            return;
        }

        $profile_data = [
            "id" => $user_data->id,
            "email" => $user_data->email,
            "user_type" => $user_data->user_type,
            "full_name" => $user_data->full_name,
            "phone_number" => $user_data->phone_number,
            "address_street" => $user_data->address_street,
            "address_city" => $user_data->address_city,
            "address_state" => $user_data->address_state,
            "address_zip_code" => $user_data->address_zip_code,
            "profile_image_path" => $user_data->profile_image_path ? base_url($user_data->profile_image_path) : null,
            "status" => $user_data->status,
            "created_at" => $user_data->created_at
        ];

        if ($user_type === "professional") {
            $professional_profile = $this->professional_model->findByUserId($user_id);
            if ($professional_profile) {
                $profile_data["professional_details"] = [
                    "professional_summary" => $professional_profile->professional_summary,
                    "years_experience" => $professional_profile->years_experience,
                    "profession_type" => $professional_profile->profession_type,
                    "specialties" => $professional_profile->specialties,
                    "desired_hourly_rate" => $professional_profile->desired_hourly_rate,
                    "availability_details" => $professional_profile->availability_details,
                    "background_check_status" => $professional_profile->background_check_status
                ];
            }
        } elseif ($user_type === "facility") {
            $facility_profile = $this->facility_model->findByUserId($user_id);
            if ($facility_profile) {
                $profile_data["facility_details"] = [
                    "facility_name" => $facility_profile->facility_name,
                    "facility_type" => $facility_profile->facility_type,
                    "facility_license_number" => $facility_profile->facility_license_number,
                    "tax_id" => $facility_profile->tax_id,
                    "contact_person_name" => $facility_profile->contact_person_name,
                    "contact_person_email" => $facility_profile->contact_person_email,
                    "contact_person_phone" => $facility_profile->contact_person_phone,
                    "website_url" => $facility_profile->website_url,
                    "description" => $facility_profile->description,
                    "logo_image_path" => $facility_profile->logo_image_path ? base_url($facility_profile->logo_image_path) : null,
                    "verification_status" => $facility_profile->verification_status
                ];
            }
        }

        http_response_code(200);
        echo json_encode(["status" => "success", "data" => $profile_data]);
    }

    // Update user profile (general info and role-specific info)
    public function updateProfile() {
        if (!is_logged_in()) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Authentication required."]);
            return;
        }

        if ($_SERVER["REQUEST_METHOD"] !== "POST") { // Using POST for updates, could be PUT
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "POST method required."]);
            return;
        }

        $user_id = get_current_user_id();
        $user_type = get_current_user_type();
        
        // For file uploads, we expect multipart/form-data
        // For JSON data, we would use file_get_contents("php://input")
        // This example assumes form-data for simplicity with file uploads.
        $data = (object)$_POST; // Contains text fields
        $files = $_FILES; // Contains uploaded files

        // Update general user information
        $current_user = $this->user_model->findById($user_id);
        if (!$current_user) {
            http_response_code(404); echo json_encode(["status" => "error", "message" => "User not found."]); return;
        }

        $current_user->full_name = $data->full_name ?? $current_user->full_name;
        $current_user->phone_number = $data->phone_number ?? $current_user->phone_number;
        $current_user->address_street = $data->address_street ?? $current_user->address_street;
        $current_user->address_city = $data->address_city ?? $current_user->address_city;
        $current_user->address_state = $data->address_state ?? $current_user->address_state;
        $current_user->address_zip_code = $data->address_zip_code ?? $current_user->address_zip_code;

        // Handle profile image upload
        if (isset($files["profile_image"]) && $files["profile_image"]["error"] == UPLOAD_ERR_OK) {
            $upload_dir = UPLOAD_DIR . "profile_images/"; // Ensure UPLOAD_DIR is defined in config.php
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $tmp_name = $files["profile_image"]["tmp_name"];
            $file_extension = strtolower(pathinfo($files["profile_image"]["name"], PATHINFO_EXTENSION));
            $safe_filename = "user_" . $user_id . "_" . uniqid() . "." . $file_extension;
            $destination = $upload_dir . $safe_filename;

            // Basic validation (add more robust checks: size, type)
            $allowed_extensions = ["jpg", "jpeg", "png", "gif"];
            if (in_array($file_extension, $allowed_extensions) && $files["profile_image"]["size"] < 5000000) { // Max 5MB
                if (move_uploaded_file($tmp_name, $destination)) {
                    // Delete old image if exists
                    if ($current_user->profile_image_path && file_exists(BASE_PATH . "/public/" . $current_user->profile_image_path)) {
                        unlink(BASE_PATH . "/public/" . $current_user->profile_image_path);
                    }
                    $current_user->profile_image_path = "uploads/profile_images/" . $safe_filename; // Relative to public dir
                } else {
                    // Handle upload error
                    // error_log("Profile image upload failed for user: " . $user_id);
                }
            }
        }

        $user_updated = $current_user->update();
        $role_specific_updated = false;

        // Update role-specific information
        if ($user_type === "professional") {
            $professional_profile = $this->professional_model->findByUserId($user_id);
            if (!$professional_profile) { // Create if doesn't exist
                $professional_profile = new HealthcareProfessional($this->db);
                $professional_profile->user_id = $user_id;
                $is_new_professional_profile = true;
            } else {
                $is_new_professional_profile = false;
            }
            
            $professional_profile->professional_summary = $data->professional_summary ?? $professional_profile->professional_summary;
            $professional_profile->years_experience = $data->years_experience ?? $professional_profile->years_experience;
            $professional_profile->profession_type = $data->profession_type ?? $professional_profile->profession_type;
            $professional_profile->specialties = $data->specialties ?? $professional_profile->specialties;
            $professional_profile->desired_hourly_rate = $data->desired_hourly_rate ?? $professional_profile->desired_hourly_rate;
            $professional_profile->availability_details = $data->availability_details ?? $professional_profile->availability_details;
            // background_check_status might be updated by admin or system

            if ($is_new_professional_profile) {
                $role_specific_updated = $professional_profile->create();
            } else {
                $role_specific_updated = $professional_profile->update();
            }

        } elseif ($user_type === "facility") {
            $facility_profile = $this->facility_model->findByUserId($user_id);
             if (!$facility_profile) { // Create if doesn't exist
                $facility_profile = new Facility($this->db);
                $facility_profile->user_id = $user_id;
                $is_new_facility_profile = true;
            } else {
                $is_new_facility_profile = false;
            }

            $facility_profile->facility_name = $data->facility_name ?? $facility_profile->facility_name;
            $facility_profile->facility_type = $data->facility_type ?? $facility_profile->facility_type;
            $facility_profile->facility_license_number = $data->facility_license_number ?? $facility_profile->facility_license_number;
            $facility_profile->tax_id = $data->tax_id ?? $facility_profile->tax_id;
            $facility_profile->contact_person_name = $data->contact_person_name ?? $facility_profile->contact_person_name;
            $facility_profile->contact_person_email = $data->contact_person_email ?? $facility_profile->contact_person_email;
            $facility_profile->contact_person_phone = $data->contact_person_phone ?? $facility_profile->contact_person_phone;
            $facility_profile->website_url = $data->website_url ?? $facility_profile->website_url;
            $facility_profile->description = $data->description ?? $facility_profile->description;
            // logo_image_path handled similarly to profile_image_path
            // verification_status usually updated by admin

            if ($is_new_facility_profile) {
                $role_specific_updated = $facility_profile->create();
            } else {
                $role_specific_updated = $facility_profile->update();
            }
        }

        if ($user_updated || $role_specific_updated) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Profile updated successfully."]);
        } else {
            // If nothing was changed or an error occurred (and not caught earlier)
            http_response_code(304); // Not Modified or 500 if error
            echo json_encode(["status" => "info", "message" => "No changes made or update failed."]);
        }
    }
    
    // Method to update password
    public function updatePassword() {
        if (!is_logged_in()) {
            http_response_code(401); echo json_encode(["status" => "error", "message" => "Authentication required."]); return;
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            http_response_code(405); echo json_encode(["status" => "error", "message" => "POST method required."]); return;
        }

        $user_id = get_current_user_id();
        $data = json_decode(file_get_contents("php://input"));
        if (!$data && isset($_POST)) $data = (object)$_POST;

        if (empty($data->current_password) || empty($data->new_password)) {
            http_response_code(400); echo json_encode(["status" => "error", "message" => "Current and new passwords are required."]); return;
        }
        if (strlen($data->new_password) < 6) {
            http_response_code(400); echo json_encode(["status" => "error", "message" => "New password must be at least 6 characters."]); return;
        }

        $user = $this->user_model->findById($user_id);
        if (!$user || !password_verify($data->current_password, $user->password_hash)) {
            http_response_code(401); echo json_encode(["status" => "error", "message" => "Incorrect current password."]); return;
        }

        if ($user->updatePassword($data->new_password)) {
            http_response_code(200); echo json_encode(["status" => "success", "message" => "Password updated successfully."]);
        } else {
            http_response_code(500); echo json_encode(["status" => "error", "message" => "Failed to update password."]);
        }
    }
}
?>
