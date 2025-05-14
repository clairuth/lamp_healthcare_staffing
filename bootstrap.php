<?php
// core/bootstrap.php

// This file is responsible for bootstrapping the application,
// including autoloading classes, starting sessions, and other initial setup.

// Ensure BASE_PATH is defined (usually from public/index.php)
if (!defined("BASE_PATH")) {
    // Attempt to define it if this file is included directly in some context (e.g. CLI scripts)
    // This assumes bootstrap.php is in /implementation/core/
    define("BASE_PATH", realpath(dirname(__FILE__) . "/../"));
}

// Include the main configuration file if not already included
// (config.php might also try to define BASE_PATH, ensure consistency)
if (!defined("DB_HOST")) { // Check if config constants are loaded
    require_once BASE_PATH . "/implementation/app/config.php";
}

// Autoloader (Simple PSR-4 like autoloader for app, core, lib)
spl_autoload_register(function ($class_name) {
    // Define base directories for different namespaces/prefixes if you plan to use them.
    // For a simpler structure, we can map top-level directories.
    $directories = [
        "app/controllers/",
        "app/models/",
        "core/",
        "lib/",
        "app/services/" // Example for service classes
    ];

    // Replace namespace separators with directory separators
    $class_file = str_replace("\\", "/", $class_name) . ".php";

    foreach ($directories as $dir_prefix) {
        $file = BASE_PATH . "/implementation/" . $dir_prefix . $class_file;
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    // Fallback for classes directly in core or lib without deeper namespacing
    // e.g. class Database in core/Database.php
    $simple_class_paths = [
        BASE_PATH . "/implementation/core/" . $class_name . ".php",
        BASE_PATH . "/implementation/lib/" . $class_name . ".php",
    ];
    foreach ($simple_class_paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    if (defined("SESSION_NAME")) {
        session_name(SESSION_NAME);
    }
    // Ensure SESSION_LIFETIME, SESSION_SECURE, SESSION_HTTP_ONLY are defined in config.php
    $lifetime = defined("SESSION_LIFETIME") ? SESSION_LIFETIME : 0;
    $path = "/"; // Session cookie path
    $domain = isset($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : "localhost"; // Session cookie domain
    $secure = defined("SESSION_SECURE") ? SESSION_SECURE : false;
    $httponly = defined("SESSION_HTTP_ONLY") ? SESSION_HTTP_ONLY : true;

    session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);
    session_start();
}

// Include common helper functions
if (file_exists(BASE_PATH . "/implementation/includes/functions.php")) {
    require_once BASE_PATH . "/implementation/includes/functions.php";
}

// Initialize Database connection (optional, can be done on demand)
// $db = Database::getInstance();
// $GLOBALS["conn"] = $db->getConnection(); // Make it globally accessible or pass as dependency

?>
