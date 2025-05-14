<?php
// public/index.php - Main application entry point and router

session_start();

require_once __DIR__ . 
// app/controllers/ShiftApplicationController.php
// app/controllers/SkillController.php
// app/controllers/SkillAssessmentController.php

// Basic Router
$request_uri = explode("?", $_SERVER["REQUEST_URI"])[0];
$request_path = trim($request_uri, "/");
$path_parts = explode("/", $request_path);

// Set a base path if your app is not in the web root
// e.g., if your app is at localhost/my_app/, then $base_path_offset = 1;
$base_path_offset = 0; // Assuming app is in web root or virtual host root

$controller_name = $path_parts[$base_path_offset] ?? "home";
$action_name = $path_parts[$base_path_offset + 1] ?? "index";
$param1 = $path_parts[$base_path_offset + 2] ?? null;
$param2 = $path_parts[$base_path_offset + 3] ?? null;

// Whitelist controllers and actions for security
$allowed_controllers = [
    "home" => "HomeController",
    "auth" => "AuthController",
    "userprofile" => "UserProfileController",
    "credential" => "CredentialController",
    "shift" => "ShiftController",
    "shiftapplication" => "ShiftApplicationController",
    "skill" => "SkillController",
    "skillassessment" => "SkillAssessmentController",
    // Add payment controller later
];

$controller_class = null;
if (array_key_exists(strtolower($controller_name), $allowed_controllers)) {
    $controller_class = $allowed_controllers[strtolower($controller_name)];
}

if ($controller_class && class_exists($controller_class)) {
    $controller_instance = new $controller_class();

    // Whitelist actions for each controller
    $allowed_actions = [
        "HomeController" => ["index", "dashboard", "adminDashboard"],
        "AuthController" => ["login", "register", "logout", "forgotPassword", "resetPassword"],
        "UserProfileController" => ["view", "edit", "updateAvatar"],
        "CredentialController" => ["upload", "listByUser", "delete", "verify"], // verify might be admin only
        "ShiftController" => ["create", "edit", "delete", "listByFacility", "listOpen", "view"],
        "ShiftApplicationController" => ["apply", "listByProfessional", "withdraw", "listByShift", "updateApplicationStatus"],
        "SkillController" => ["adminCreateSkill", "adminListSkills", "listAllSkills", "addProfessionalSkill", "listProfessionalSkills", "updateProfessionalSkill", "removeProfessionalSkill"],
        "SkillAssessmentController" => ["adminCreateAssessment", "adminListAssessments", "listAssessmentsForProfessionalSkill", "viewAssessment", "submitAssessmentAttempt", "listProfessionalAttempts"],
    ];

    if (isset($allowed_actions[$controller_class]) && in_array($action_name, $allowed_actions[$controller_class])) {
        // Call the action, passing parameters if they exist
        if ($param1 !== null && $param2 !== null) {
            $controller_instance->$action_name($param1, $param2);
        } elseif ($param1 !== null) {
            $controller_instance->$action_name($param1);
        } else {
            $controller_instance->$action_name();
        }
    } else {
        // Action not allowed or not found
        http_response_code(404);
        // error_log("Action not found or not allowed: Controller: {$controller_class}, Action: {$action_name}");
        echo json_encode(["status" => "error", "message" => "Endpoint not found."]);
        // You might want to render a 404 page for non-API requests
        // include __DIR__ . 
    }
} else {
    // Controller not found
    http_response_code(404);
    // error_log("Controller not found: {$controller_name}");
    echo json_encode(["status" => "error", "message" => "Resource not found."]);
    // include __DIR__ . 
}

?>
