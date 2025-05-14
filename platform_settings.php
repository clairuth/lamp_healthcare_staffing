<?php
// templates/admin/platform_settings.php

$page_title = "Platform Configuration - Admin - StaffingPlus";
require_once APP_ROOT . "/templates/header.php";

if (!is_logged_in() || get_current_user_type() !== "admin") {
    display_error_message("Access Denied: You must be logged in as an administrator.");
    require_once APP_ROOT . "/templates/footer.php";
    exit;
}

// Fetch current settings - this should be handled by the controller
// For now, let's assume $current_settings is an associative array populated by AdminController::platformSettings()
// Example: $current_settings = ["site_name" => "StaffingPlus", "default_user_role" => "professional", ...];
if (!isset($current_settings)) {
    // $current_settings = []; // Simulate or fetch if necessary
}

?>

<div class="container-fluid mt-4 admin-management-page">
    <h1 class="mb-4">Platform Configuration & Settings</h1>

    <div id="messageContainerGlobal"></div>

    <form id="platformSettingsForm" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">

        <div class="row">
            <!-- General Settings -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">General Site Settings</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="setting_site_name">Site Name</label>
                            <input type="text" class="form-control" id="setting_site_name" name="settings[site_name]" value="<?php echo htmlspecialchars($current_settings["site_name"] ?? "StaffingPlus"); ?>" required>
                            <div class="invalid-feedback">Site name is required.</div>
                        </div>
                        <div class="form-group">
                            <label for="setting_admin_email">Administrator Email</label>
                            <input type="email" class="form-control" id="setting_admin_email" name="settings[admin_email]" value="<?php echo htmlspecialchars($current_settings["admin_email"] ?? "admin@example.com"); ?>" required>
                            <div class="invalid-feedback">A valid admin email is required.</div>
                        </div>
                        <div class="form-group">
                            <label for="setting_maintenance_mode">Maintenance Mode</label>
                            <select class="form-control" id="setting_maintenance_mode" name="settings[maintenance_mode]">
                                <option value="0" <?php echo (isset($current_settings["maintenance_mode"]) && $current_settings["maintenance_mode"] == "0") ? "selected" : ""; ?>>Disabled</option>
                                <option value="1" <?php echo (isset($current_settings["maintenance_mode"]) && $current_settings["maintenance_mode"] == "1") ? "selected" : ""; ?>>Enabled</option>
                            </select>
                            <small class="form-text text-muted">If enabled, only admins can access the site.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User & Registration Settings -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">User & Registration Settings</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="setting_allow_registration">Allow New User Registrations</label>
                            <select class="form-control" id="setting_allow_registration" name="settings[allow_registration]">
                                <option value="1" <?php echo (isset($current_settings["allow_registration"]) && $current_settings["allow_registration"] == "1") ? "selected" : ""; ?>>Yes</option>
                                <option value="0" <?php echo (isset($current_settings["allow_registration"]) && $current_settings["allow_registration"] == "0") ? "selected" : ""; ?>>No</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="setting_default_professional_role">Default Role for Professionals</label>
                            <input type="text" class="form-control" id="setting_default_professional_role" name="settings[default_professional_role]" value="<?php echo htmlspecialchars($current_settings["default_professional_role"] ?? "Professional"); ?>" readonly>
                            <small class="form-text text-muted">System defined role.</small>
                        </div>
                         <div class="form-group">
                            <label for="setting_credential_expiry_reminders">Credential Expiry Reminder (Days Before)</label>
                            <input type="number" class="form-control" id="setting_credential_expiry_reminders" name="settings[credential_expiry_reminders]" value="<?php echo htmlspecialchars($current_settings["credential_expiry_reminders"] ?? "30"); ?>" min="1" max="90">
                            <small class="form-text text-muted">Send reminder email X days before credential expires.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Payment Settings Placeholder -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">Payment Gateway Settings (Basic)</div>
                    <div class="card-body">
                        <p class="text-muted">Detailed payment gateway API keys and settings will be managed in a dedicated section after integration.</p>
                        <div class="form-group">
                            <label for="setting_currency_code">Default Currency Code</label>
                            <input type="text" class="form-control" id="setting_currency_code" name="settings[currency_code]" value="<?php echo htmlspecialchars($current_settings["currency_code"] ?? "USD"); ?>" placeholder="e.g., USD, CAD">
                        </div>
                        <div class="form-group">
                            <label for="setting_escrow_duration_days">Default Escrow Hold Duration (Days)</label>
                            <input type="number" class="form-control" id="setting_escrow_duration_days" name="settings[escrow_duration_days]" value="<?php echo htmlspecialchars($current_settings["escrow_duration_days"] ?? "3"); ?>" min="0" max="30">
                            <small class="form-text text-muted">Days payment is held in escrow after shift completion.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Other Settings Placeholder -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">Miscellaneous Settings</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="setting_items_per_page">Default Items Per Page (Pagination)</label>
                            <input type="number" class="form-control" id="setting_items_per_page" name="settings[items_per_page]" value="<?php echo htmlspecialchars($current_settings["items_per_page"] ?? "10"); ?>" min="5" max="50">
                        </div>
                        <!-- Add more settings as needed -->
                        <p class="text-muted">More advanced settings can be added here.</p>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg">Save All Settings</button>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const platformSettingsForm = document.getElementById("platformSettingsForm");

    // Fetch current settings and populate form (if not done by PHP)
    async function loadCurrentSettings() {
        // This function would be used if settings are not pre-populated by PHP
        // For this example, we assume PHP has populated initial values.
        // If you need to fetch dynamically:
        /*
        try {
            const response = await fetchData("<?php echo base_url("admin/settings/getAPI"); ?>");
            if (response.status === "success" && response.data) {
                for (const key in response.data) {
                    const inputElement = document.querySelector(`[name="settings[${key}]"]`);
                    if (inputElement) {
                        if (inputElement.type === "checkbox" || inputElement.type === "radio") {
                            inputElement.checked = response.data[key] == "1" || response.data[key] === true;
                        } else {
                            inputElement.value = response.data[key];
                        }
                    }
                }
            } else {
                console.warn("Could not load platform settings via API.");
            }
        } catch (error) {
            console.error("Error fetching platform settings:", error);
        }
        */
    }
    // loadCurrentSettings(); // Call if needed

    if (platformSettingsForm) {
        platformSettingsForm.addEventListener("submit", async function(event) {
            event.preventDefault();
            event.stopPropagation();

            if (!platformSettingsForm.checkValidity()) {
                platformSettingsForm.classList.add("was-validated");
                displayMessage("danger", "Please correct the errors in the form.", "messageContainerGlobal");
                return;
            }

            const formData = new FormData(platformSettingsForm);
            const data = {};
            // FormData doesn't directly support nested names like settings[key] for Object.fromEntries
            // We need to build the settings object manually or adjust backend to accept flat keys.
            // For this example, we assume backend can handle settings[key] if sent as individual fields.
            // Or, construct the nested object:
            data.settings = {};
            data.csrf_token = formData.get("csrf_token");
            for (let [key, value] of formData.entries()) {
                if (key.startsWith("settings[")) {
                    const actualKey = key.substring(9, key.length - 1);
                    data.settings[actualKey] = value;
                }
            }

            const submitButton = platformSettingsForm.querySelector("button[type=\"submit\"]");
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = 
            submitButton.disabled = true;

            try {
                const response = await fetchData("<?php echo base_url("admin/settings/update"); ?>", {
                    method: "POST",
                    body: JSON.stringify(data) 
                });

                if (response.status === "success") {
                    displayMessage("success", response.message || "Platform settings updated successfully!", "messageContainerGlobal");
                    platformSettingsForm.classList.remove("was-validated");
                    // Optionally, reload settings or specific parts of the page if needed
                } else {
                    displayMessage("danger", response.message || "Failed to update settings. Please try again.", "messageContainerGlobal");
                }
            } catch (error) {
                displayMessage("danger", error.message || "An unexpected error occurred while saving settings.", "messageContainerGlobal");
            } finally {
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
            }
        });
    }
});
</script>

<?php
require_once APP_ROOT . "/templates/footer.php";
?>
