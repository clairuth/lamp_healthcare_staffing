<?php
// includes/functions.php

// Common helper functions for the application

// Function to sanitize output (prevent XSS)
function html_escape($string) {
    return htmlspecialchars($string ?? "", ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

// Function to redirect to a different page
function redirect($url) {
    // Ensure SITE_URL is defined and used for absolute URLs if needed
    // For now, assuming $url is relative to the site root or a full URL
    header("Location: " . $url);
    exit;
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION["user_id"]);
}

// Function to get current logged-in user ID
function get_current_user_id() {
    return $_SESSION["user_id"] ?? null;
}

// Function to get current logged-in user type
function get_current_user_type() {
    return $_SESSION["user_type"] ?? null;
}

// Function to generate a CSRF token
function generate_csrf_token() {
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

// Function to verify a CSRF token
function verify_csrf_token($token) {
    return isset($_SESSION["csrf_token"]) && hash_equals($_SESSION["csrf_token"], $token);
}

// Function to format dates
function format_date($datetime_string, $format = "M d, Y H:i") {
    if (empty($datetime_string)) return "N/A";
    try {
        $date = new DateTime($datetime_string);
        return $date->format($format);
    } catch (Exception $e) {
        return "Invalid Date";
    }
}

// Function to create a slug from a string (for URLs)
function create_slug($string) {
    $string = preg_replace("/[^\w\s-]/", "", $string);
    $string = strtolower(trim($string));
    $string = preg_replace("/[-\s]+/", "-", $string);
    return $string;
}

// Function to dd (dump and die) - for debugging
function dd(...$vars) {
    echo "<pre>";
    foreach ($vars as $var) {
        var_dump($var);
    }
    echo "</pre>";
    die();
}

// Function to get base URL (useful for links and assets)
function base_url($path = "") {
    if (defined("SITE_URL")) {
        return rtrim(SITE_URL, "/") . "/" . ltrim($path, "/");
    }
    // Fallback if SITE_URL is not perfectly set up in config for all contexts
    $protocol = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https://" : "http://";
    $host = $_SERVER["HTTP_HOST"] ?? "localhost";
    // Adjust if your app is in a subdirectory and not at the web root
    $subdir = dirname($_SERVER["SCRIPT_NAME"]); 
    if ($subdir === "/" || $subdir === "\\" ) $subdir = ""; // Avoid double slashes at root
    return rtrim($protocol . $host . $subdir, "/") . "/" . ltrim($path, "/");
}

// Function to load a view/template (simple version)
function load_view($view_name, $data = []) {
    // $view_name would be like "auth/login" corresponding to templates/auth/login.php
    $view_path = BASE_PATH . "/implementation/templates/" . $view_name . ".php";
    if (file_exists($view_path)) {
        extract($data); // Make data available as variables in the view
        include $view_path;
    } else {
        // Handle view not found error
        echo "Error: View '" . html_escape($view_name) . "' not found.";
    }
}

?>
