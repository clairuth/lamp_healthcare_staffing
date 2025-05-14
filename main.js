// Global JavaScript for the LAMP Healthcare Staffing Platform

// Base URL for API calls - ensure this is set correctly, e.g., via a data attribute on <html> or a global JS variable
const base_url = function(path = "") {
    const base = document.documentElement.dataset.baseUrl || (window.location.origin + "/index.php/"); // Fallback if not set
    // Ensure base ends with a slash if path is provided and doesn't start with one
    if (path && base.slice(-1) !== "/" && path[0] !== "/") {
        return base + "/" + path;
    }
    // Ensure no double slashes if base ends with / and path starts with /
    if (path && base.slice(-1) === "/" && path[0] === "/") {
        return base + path.substring(1);
    }
    return base + path;
};

// CSRF Token - Get from a meta tag or a hidden input if your forms use them
function getCsrfToken() {
    const tokenElement = document.querySelector("input[name=\"csrf_token\"]") || document.querySelector("meta[name=\"csrf-token\"]");
    return tokenElement ? tokenElement.value || tokenElement.content : "";
}

// Centralized API call function
async function fetchData(url, options = {}) {
    const defaultHeaders = {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest"
    };

    // Add CSRF token to relevant requests (POST, PUT, DELETE)
    const csrfToken = getCsrfToken();
    if (csrfToken && options.method && ["POST", "PUT", "DELETE"].includes(options.method.toUpperCase())) {
        if (options.body instanceof FormData) {
            // FormData will handle CSRF if it's a field in the form
            // If not, and you need to send it separately, this becomes more complex
            // For JSON, it's easier:
        } else if (typeof options.body === "string") { // Assuming JSON string
            try {
                const bodyData = JSON.parse(options.body);
                bodyData.csrf_token = csrfToken; // Add CSRF token to JSON body
                options.body = JSON.stringify(bodyData);
            } catch (e) {
                console.warn("Could not parse JSON body to add CSRF token", e);
            }
        }
    }

    options.headers = { ...defaultHeaders, ...options.headers };
    
    // If body is FormData, Content-Type should not be set to application/json
    if (options.body instanceof FormData) {
        delete options.headers["Content-Type"]; // Browser will set it with boundary
    }

    try {
        const response = await fetch(url, options);
        if (!response.ok) {
            // Try to parse error from JSON response if possible
            let errorData;
            try {
                errorData = await response.json();
            } catch (e) {
                // Not a JSON response
            }
            const errorMessage = errorData?.message || `HTTP error! status: ${response.status}`;
            throw new Error(errorMessage);
        }
        return await response.json();
    } catch (error) {
        console.error("Fetch error:", error);
        // Return a consistent error structure if possible
        return { status: "error", message: error.message || "A network error occurred." };
    }
}

// Display messages to the user
function displayMessage(type, message, containerId, targetElement = null, clearExisting = true) {
    const container = targetElement || document.getElementById(containerId);
    if (!container) {
        console.warn(`Message container with ID "${containerId}" not found.`);
        alert(`${type.toUpperCase()}: ${message}`); // Fallback to alert
        return;
    }

    const alertDiv = document.createElement("div");
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.setAttribute("role", "alert");
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    if(clearExisting){
        container.innerHTML = ""; // Clear previous messages in the specific container
    }
    container.appendChild(alertDiv);

    // Automatically dismiss after some time for non-error messages
    if (type === "success" || type === "info") {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getInstance(alertDiv);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 5000);
    }
}

// Sanitize HTML to prevent XSS - basic version
function sanitizeHTML(str) {
    if (str === null || typeof str === "undefined") return "";
    const temp = document.createElement("div");
    temp.textContent = str;
    return temp.innerHTML;
}

// Format date as YYYY-MM-DD or similar user-friendly format
function formatDate(dateString) {
    if (!dateString) return "N/A";
    try {
        const date = new Date(dateString);
        // Adjust to local timezone before formatting to avoid off-by-one day issues
        const userTimezoneOffset = date.getTimezoneOffset() * 60000;
        const localDate = new Date(date.getTime() + userTimezoneOffset); // This might be problematic, careful with timezone handling
        return localDate.toLocaleDateString(undefined, { year: "numeric", month: "long", day: "numeric" });
    } catch (e) {
        return dateString; // Return original if parsing fails
    }
}

// Format time as HH:MM AM/PM
function formatTime(timeString) {
    if (!timeString) return "N/A";
    // Assuming timeString is in HH:MM:SS or HH:MM format
    const [hours, minutes] = timeString.split(":");
    if (hours === undefined || minutes === undefined) return timeString;
    const date = new Date();
    date.setHours(parseInt(hours, 10));
    date.setMinutes(parseInt(minutes, 10));
    return date.toLocaleTimeString(undefined, { hour: "2-digit", minute: "2-digit", hour12: true });
}

// Format date and time
function formatDateTime(dateTimeString) {
    if (!dateTimeString) return "N/A";
    try {
        const date = new Date(dateTimeString);
        const userTimezoneOffset = date.getTimezoneOffset() * 60000;
        const localDate = new Date(date.getTime() + userTimezoneOffset);
        return localDate.toLocaleString(undefined, { year: "numeric", month: "long", day: "numeric", hour: "2-digit", minute: "2-digit" });
    } catch (e) {
        return dateTimeString;
    }
}

// Handle mobile navigation toggle
document.addEventListener("DOMContentLoaded", function() {
    const navbarToggler = document.querySelector(".navbar-toggler");
    const navbarCollapse = document.querySelector(".navbar-collapse");

    if (navbarToggler && navbarCollapse) {
        navbarToggler.addEventListener("click", function() {
            navbarCollapse.classList.toggle("show");
        });
    }

    // Bootstrap form validation styling
    const forms = document.querySelectorAll(".needs-validation");
    Array.from(forms).forEach(form => {
        form.addEventListener("submit", event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add("was-validated");
        }, false);
    });

    // Initialize Bootstrap tooltips if any
    const tooltipTriggerList = [].slice.call(document.querySelectorAll("[data-bs-toggle=\"tooltip\"]"));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize Bootstrap popovers if any
    const popoverTriggerList = [].slice.call(document.querySelectorAll("[data-bs-toggle=\"popover\"]"));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Set base URL on HTML element if not already set by PHP
    if (!document.documentElement.dataset.baseUrl) {
        // A more robust way to determine base URL might be needed if not deploying to root
        let pathArray = window.location.pathname.split("/");
        let appPath = "";
        // Check if index.php is in the path, if so, base is up to and including it
        let indexOfIndexPhp = pathArray.indexOf("index.php");
        if (indexOfIndexPhp > -1) {
            appPath = pathArray.slice(0, indexOfIndexPhp + 1).join("/") + "/";
        } else {
            // If no index.php, assume it's a clean URL setup and base is just the origin or a subfolder
            // This part is tricky and depends on server config. For simplicity, assume root or /index.php/
            appPath = window.location.origin + "/"; 
        }
        document.documentElement.dataset.baseUrl = window.location.origin + appPath;
        console.warn("Base URL dynamically set in JS. Consider setting it via PHP in header.php for reliability: <html lang=\"en\" data-base-url=\"<?php echo rtrim(BASE_URL, '/') . '/'; ?>\">");
    }
});

console.log("Main JavaScript file loaded.");

