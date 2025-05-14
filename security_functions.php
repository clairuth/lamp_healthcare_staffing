<?php
// includes/security_functions.php

if (!defined("APP_ROOT")) {
    define("APP_ROOT", dirname(__DIR__));
}

/**
 * Sanitize string input.
 * Removes tags and then HTML-encodes special characters.
 *
 * @param string|null $input The string to sanitize.
 * @return string|null The sanitized string or null if input was null.
 */
function sanitize_string($input) {
    if ($input === null) {
        return null;
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, "UTF-8");
}

/**
 * Sanitize an entire array recursively.
 *
 * @param array $array The array to sanitize.
 * @return array The sanitized array.
 */
function sanitize_array(array $array) {
    $sanitized_array = [];
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $sanitized_array[sanitize_string($key)] = sanitize_array($value);
        } elseif (is_string($value)) {
            $sanitized_array[sanitize_string($key)] = sanitize_string($value);
        } else {
            // For non-string, non-array values (like int, bool), just pass them through after sanitizing the key
            $sanitized_array[sanitize_string($key)] = $value;
        }
    }
    return $sanitized_array;
}

/**
 * Validate if a value is a non-empty string.
 *
 * @param mixed $value
 * @return boolean
 */
function validate_not_empty_string($value) {
    return isset($value) && is_string($value) && trim($value) !== "";
}

/**
 * Validate if a value is a valid email address.
 *
 * @param mixed $email
 * @return boolean
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate if a value is a valid integer.
 *
 * @param mixed $value
 * @param int|null $min Optional minimum value.
 * @param int|null $max Optional maximum value.
 * @return boolean
 */
function validate_integer($value, $min = null, $max = null) {
    if (filter_var($value, FILTER_VALIDATE_INT) === false) {
        return false;
    }
    $int_value = (int)$value;
    if ($min !== null && $int_value < $min) {
        return false;
    }
    if ($max !== null && $int_value > $max) {
        return false;
    }
    return true;
}

/**
 * Validate if a value is a valid boolean representation.
 *
 * @param mixed $value
 * @return boolean
 */
function validate_boolean($value) {
    return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null;
}

/**
 * Validate if a value is one of the allowed options.
 *
 * @param mixed $value The value to check.
 * @param array $allowed_values Array of allowed values.
 * @return boolean
 */
function validate_in_array($value, array $allowed_values) {
    return in_array($value, $allowed_values, true);
}

/**
 * Generate a CSRF token.
 */
function generate_csrf_token() {
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

/**
 * Validate a CSRF token.
 *
 * @param string $token The token from the request.
 * @return boolean True if valid, false otherwise.
 */
function validate_csrf_token($token) {
    if (isset($_SESSION["csrf_token"]) && hash_equals($_SESSION["csrf_token"], $token)) {
        // Optionally, unset the token after use if it's a one-time token
        // unset($_SESSION["csrf_token"]); 
        return true;
    }
    return false;
}

/**
 * Basic file upload validation.
 * 
 * @param array $file_input The $_FILES["input_name"] array.
 * @param array $allowed_mime_types Array of allowed MIME types.
 * @param int $max_size Maximum file size in bytes.
 * @return array ["status" => boolean, "message" => string, "validated_name" => string|null]
 */
function validate_file_upload($file_input, $allowed_mime_types, $max_size) {
    if (!isset($file_input["error"]) || is_array($file_input["error"])) {
        return ["status" => false, "message" => "Invalid file upload parameters.", "validated_name" => null];
    }

    switch ($file_input["error"]) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return ["status" => false, "message" => "No file sent.", "validated_name" => null];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ["status" => false, "message" => "Exceeded filesize limit.", "validated_name" => null];
        default:
            return ["status" => false, "message" => "Unknown errors.", "validated_name" => null];
    }

    if ($file_input["size"] > $max_size) {
        return ["status" => false, "message" => "Exceeded filesize limit ({$max_size} bytes).", "validated_name" => null];
    }

    // Check MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file_input["tmp_name"]);
    if (false === array_search($mime_type, $allowed_mime_types, true)) {
        return ["status" => false, "message" => "Invalid file format. Allowed: " . implode(", ", $allowed_mime_types), "validated_name" => null];
    }

    // Generate a safe filename
    $file_extension = pathinfo($file_input["name"], PATHINFO_EXTENSION);
    $safe_filename = bin2hex(random_bytes(16)) . "." . strtolower($file_extension);

    return ["status" => true, "message" => "File is valid.", "validated_name" => $safe_filename];
}

// Add more validation and sanitization functions as needed.

?>
