<?php
// app/controllers/AuthController.php

// require_once BASE_PATH . "/implementation/app/models/User.php"; // Handled by bootstrap
// require_once BASE_PATH . "/implementation/core/Database.php"; // Handled by bootstrap
// require_once BASE_PATH . "/implementation/includes/functions.php"; // Handled by bootstrap

class AuthController {
    private $db;
    private $user_model;

    public function __construct() {
        // Ensure bootstrap.php is included by the entry point (e.g., public/index.php)
        // which defines BASE_PATH and loads config.php
        $database = Database::getInstance();
        $this->db = $database->getConnection();
        $this->user_model = new User($this->db);
    }

    public function showRegistrationForm() {
        // This would load a view
        // For now, let's assume it prepares data for a view
        // load_view("auth/register");
        // echo "Registration form should be here.";
        // For API-driven approach with plain JS, this might not be needed if JS handles form display
    }

    public function register() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            // Basic CSRF check (implement more robustly)
            // if (!isset($_POST["csrf_token"]) || !verify_csrf_token($_POST["csrf_token"])) {
            //     // Handle CSRF error - maybe redirect back with an error message
            //     // For API: return json_encode(["status" => "error", "message" => "CSRF token mismatch."]);
            //     // exit;
            // }

            // Get POST data
            $data = json_decode(file_get_contents("php://input")); // If receiving JSON from JS frontend
            if (!$data && isset($_POST)) { // Fallback for form-data
                $data = (object)$_POST;
            }

            // Validate input (basic example, expand significantly)
            if (empty($data->full_name) || empty($data->email) || empty($data->password) || empty($data->user_type)) {
                // For API:
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "All fields (full_name, email, password, user_type) are required."]);
                return;
            }

            if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Invalid email format."]);
                return;
            }

            if (strlen($data->password) < 6) { // Example minimum password length
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Password must be at least 6 characters long."]);
                return;
            }
            
            if (!in_array($data->user_type, ["professional", "facility"])) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Invalid user type. Must be professional or facility."]);
                return;
            }

            $this->user_model->email = $data->email;
            if ($this->user_model->emailExists()) {
                http_response_code(409); // Conflict
                echo json_encode(["status" => "error", "message" => "Email already exists."]);
                return;
            }

            // Proceed with registration
            $this->user_model->full_name = $data->full_name;
            $this->user_model->email = $data->email;
            $this->user_model->password_hash = password_hash($data->password, PASSWORD_BCRYPT);
            $this->user_model->user_type = $data->user_type;
            $this->user_model->phone_number = $data->phone_number ?? null;
            $this->user_model->address_street = $data->address_street ?? null;
            $this->user_model->address_city = $data->address_city ?? null;
            $this->user_model->address_state = $data->address_state ?? null;
            $this->user_model->address_zip_code = $data->address_zip_code ?? null;
            $this->user_model->status = "active"; // Or "pending_verification" if email verification is added

            if ($this->user_model->create()) {
                // Optionally, create professional or facility specific record here
                if ($this->user_model->user_type === "professional") {
                    // $professionalModel = new HealthcareProfessional($this->db);
                    // $professionalModel->user_id = $this->user_model->id;
                    // $professionalModel->create(); // Basic record
                } elseif ($this->user_model->user_type === "facility") {
                    // $facilityModel = new Facility($this->db);
                    // $facilityModel->user_id = $this->user_model->id;
                    // $facilityModel->facility_name = $data->facility_name ?? $data->full_name; // Example
                    // $facilityModel->create(); // Basic record
                }
                
                http_response_code(201); // Created
                echo json_encode(["status" => "success", "message" => "User registered successfully.", "user_id" => $this->user_model->id]);
            } else {
                http_response_code(500); // Internal Server Error
                echo json_encode(["status" => "error", "message" => "Unable to register user. Please try again later."]);
            }
        } else {
            // Handle GET request or other methods if needed, or show registration form
            // load_view("auth/register");
            http_response_code(405); // Method Not Allowed
            echo json_encode(["status" => "error", "message" => "POST method required for registration."]);
        }
    }

    public function showLoginForm() {
        // load_view("auth/login");
        // echo "Login form should be here.";
    }

    public function login() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $data = json_decode(file_get_contents("php://input"));
            if (!$data && isset($_POST)) {
                $data = (object)$_POST;
            }

            if (empty($data->email) || empty($data->password)) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Email and password are required."]);
                return;
            }

            $user = $this->user_model->findByEmail($data->email);

            if ($user && password_verify($data->password, $user->password_hash)) {
                if ($user->status !== "active") {
                    http_response_code(403); // Forbidden
                    echo json_encode(["status" => "error", "message" => "Account is not active. Status: " . $user->status]);
                    return;
                }
                // Set session variables
                $_SESSION["user_id"] = $user->id;
                $_SESSION["user_type"] = $user->user_type;
                $_SESSION["user_email"] = $user->email;
                $_SESSION["user_full_name"] = $user->full_name;
                // Regenerate session ID for security
                session_regenerate_id(true);

                http_response_code(200);
                echo json_encode([
                    "status" => "success", 
                    "message" => "Login successful.",
                    "user" => [
                        "id" => $user->id,
                        "email" => $user->email,
                        "full_name" => $user->full_name,
                        "user_type" => $user->user_type
                    ]
                ]);
            } else {
                http_response_code(401); // Unauthorized
                echo json_encode(["status" => "error", "message" => "Invalid email or password."]);
            }
        } else {
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "POST method required for login."]);
        }
    }

    public function logout() {
        // Unset all session variables
        $_SESSION = array();

        // Destroy the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Destroy the session
        session_destroy();

        // For API, just return success. Frontend will handle redirect.
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Logout successful."]);
        // For traditional web app: redirect("login.php"); or redirect(base_url("login"));
    }
    
    // TODO: Add methods for password reset request, password reset form display, password reset processing, email verification etc.
}
?>
