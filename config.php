<?php
// Main configuration file for the Healthcare Staffing Platform

// Error Reporting (turn off in production)
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

// Timezone
date_default_timezone_set("UTC"); // Or your preferred timezone, e.g., America/New_York

// Site URL and Root Path
// Define these based on your server setup. For local development, it might be:
// For a local setup like http://localhost/lamp_healthcare_staffing/public/
// $protocol = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https://" : "http://";
// $host = $_SERVER["HTTP_HOST"];
// $script_name = dirname($_SERVER["SCRIPT_NAME"]);
// define("SITE_URL", $protocol . $host . ($script_name === "/" ? "" : $script_name));
// define("BASE_PATH", realpath(dirname(__FILE__) . "/../")); // Assumes config.php is in /app or /core

// For a simpler local setup where public is the document root:
// define("SITE_URL", "http://localhost:8000"); // Example if using PHP built-in server or specific Apache/Nginx port
// define("BASE_PATH", realpath(dirname(__FILE__) . "/../")); // Assuming config.php is in /app or /core

// Database Configuration
define("DB_HOST", "localhost"); // Or your MySQL server host
define("DB_USER", "your_db_user");      // Your MySQL username
define("DB_PASS", "your_db_password");  // Your MySQL password
define("DB_NAME", "healthcare_staffing_db"); // Your database name
define("DB_CHARSET", "utf8mb4");

// Session Configuration
define("SESSION_NAME", "HealthcareStaffingSession");
define("SESSION_LIFETIME", 0); // 0 = until browser closes
define("SESSION_SECURE", false); // Set to true if using HTTPS
define("SESSION_HTTP_ONLY", true);

// File Upload Configuration
define("UPLOAD_DIR", BASE_PATH . "/public/uploads/"); // Ensure this directory exists and is writable
define("MAX_FILE_SIZE", 5 * 1024 * 1024); // 5 MB
$allowed_file_types = [
    "jpg" => "image/jpeg",
    "jpeg" => "image/jpeg",
    "png" => "image/png",
    "pdf" => "application/pdf"
];
define("ALLOWED_FILE_TYPES", serialize($allowed_file_types));

// Email Configuration (Example for local testing with mailhog or similar)
// define("SMTP_HOST", "localhost");
// define("SMTP_PORT", 1025);
// define("SMTP_USER", "");
// define("SMTP_PASS", "");
// define("MAIL_FROM_ADDRESS", "noreply@yourdomain.com");
// define("MAIL_FROM_NAME", "Healthcare Staffing Platform");

// API Keys (Store securely, not directly in config for production if possible, e.g., use .env files or server vars)
// define("PAYPAL_CLIENT_ID", "YOUR_PAYPAL_CLIENT_ID");
// define("PAYPAL_CLIENT_SECRET", "YOUR_PAYPAL_CLIENT_SECRET");
// define("PAYPAL_MODE", "sandbox"); // or "live"

// define("COINBASE_API_KEY", "YOUR_COINBASE_API_KEY");

// define("TEXAS_LICENSE_API_KEY", "YOUR_TEXAS_LICENSE_API_KEY"); // If applicable

// Other Constants
define("ITEMS_PER_PAGE", 10);

// Function to establish database connection (Example)
/*
function get_db_connection() {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // In a real app, log this error and show a user-friendly message
        throw new PDOException($e->getMessage(), (int)$e->getCode());
    }
}
*/

// Autoloader (Simple example, consider using Composer for robust autoloading)
/*
spl_autoload_register(function ($class_name) {
    // Define your class directories
    $directories = [
        BASE_PATH . "/app/controllers/",
        BASE_PATH . "/app/models/",
        BASE_PATH . "/core/",
        BASE_PATH . "/lib/"
    ];

    foreach ($directories as $directory) {
        $file = $directory . str_replace("\\", "/", $class_name) . ".php";
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
*/

// Start session
/*
if (session_status() == PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params(SESSION_LIFETIME, "/", $_SERVER["SERVER_NAME"], SESSION_SECURE, SESSION_HTTP_ONLY);
    session_start();
}
*/

?>
