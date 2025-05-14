<?php
// templates/userprofile/edit_profile.php

$page_title = "Edit Profile - StaffingPlus";
require_once APP_ROOT . "/templates/header.php";

// The user_id to edit is passed as a parameter in the URL, e.g., /userprofile/edit/123
// This is handled by the router in public/index.php, which calls UserProfileController::edit($profile_user_id)
// The controller should fetch $profile_data and make it available to this template.

if (!is_logged_in()) {
    display_error_message("Access Denied: You must be logged in to edit a profile.");
    require_once APP_ROOT . "/templates/footer.php";
    exit;
}

$current_user_id = get_current_user_id();

if (!isset($profile_data) || empty($profile_data) || $current_user_id != $profile_data["id"]) {
    display_error_message("User profile not found or you do not have permission to edit this profile.");
    require_once APP_ROOT . "/templates/footer.php";
    exit;
}

$user_type = $profile_data["user_type"];

?>

<div class="container mt-4">
    <h1>Edit Your Profile</h1>

    <div id="messageContainerGlobal"></div>

    <form id="editProfileForm" class="needs-validation" novalidate>
        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($profile_data["id"]); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
        <input type="hidden" name="user_type" value="<?php echo htmlspecialchars($user_type); ?>">

        <div class="card mb-4">
            <div class="card-header">Account Information</div>
            <div class="card-body">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($profile_data["username"]); ?>" required>
                    <div class="invalid-feedback">Please choose a username.</div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($profile_data["email"]); ?>" required>
                    <div class="invalid-feedback">Please enter a valid email address.</div>
                </div>
                
                <div class="form-group">
                    <label for="current_password">Current Password (only if changing password)</label>
                    <input type="password" class="form-control" id="current_password" name="current_password">
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password (leave blank if not changing)</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" minlength="8">
                     <div class="invalid-feedback">Password must be at least 8 characters.</div>
                </div>

                <div class="form-group">
                    <label for="confirm_new_password">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password">
                    <div class="invalid-feedback">New passwords do not match.</div>
                </div>
            </div>
        </div>

        <?php if ($user_type === "professional"): ?>
            <div class="card mb-4">
                <div class="card-header">Professional Details</div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="professional_details[first_name]" value="<?php echo htmlspecialchars($professional_details["first_name"] ?? ""); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="professional_details[last_name]" value="<?php echo htmlspecialchars($professional_details["last_name"] ?? ""); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone_number">Phone Number</label>
                        <input type="tel" class="form-control" id="phone_number" name="professional_details[phone_number]" value="<?php echo htmlspecialchars($professional_details["phone_number"] ?? ""); ?>">
                    </div>
                    <div class="form-group">
                        <label for="license_number">License Number (e.g., RN, CNA)</label>
                        <input type="text" class="form-control" id="license_number" name="professional_details[license_number]" value="<?php echo htmlspecialchars($professional_details["license_number"] ?? ""); ?>">
                    </div>
                    <div class="form-group">
                        <label for="bio">Bio/Summary</label>
                        <textarea class="form-control" id="bio" name="professional_details[bio]" rows="3"><?php echo htmlspecialchars($professional_details["bio"] ?? ""); ?></textarea>
                    </div>
                </div>
            </div>
        <?php elseif ($user_type === "facility"): ?>
            <div class="card mb-4">
                <div class="card-header">Facility Details</div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="facility_name">Facility Name</label>
                        <input type="text" class="form-control" id="facility_name" name="facility_details[facility_name]" value="<?php echo htmlspecialchars($facility_details["facility_name"] ?? ""); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="facility_type_id">Facility Type</label>
                        <select class="form-control" id="facility_type_id" name="facility_details[facility_type_id]" required>
                            <option value="">Select Type...</option>
                            <!-- Options should be populated from DB -->
                            <option value="1" <?php echo (isset($facility_details["facility_type_id"]) && $facility_details["facility_type_id"] == 1) ? "selected" : ""; ?>>Hospital</option>
                            <option value="2" <?php echo (isset($facility_details["facility_type_id"]) && $facility_details["facility_type_id"] == 2) ? "selected" : ""; ?>>Clinic</option>
                            <option value="3" <?php echo (isset($facility_details["facility_type_id"]) && $facility_details["facility_type_id"] == 3) ? "selected" : ""; ?>>Nursing Home</option>
                            <option value="4" <?php echo (isset($facility_details["facility_type_id"]) && $facility_details["facility_type_id"] == 4) ? "selected" : ""; ?>>Assisted Living</option>
                            <option value="5" <?php echo (isset($facility_details["facility_type_id"]) && $facility_details["facility_type_id"] == 5) ? "selected" : ""; ?>>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="contact_person">Contact Person</label>
                        <input type="text" class="form-control" id="contact_person" name="facility_details[contact_person]" value="<?php echo htmlspecialchars($facility_details["contact_person"] ?? ""); ?>">
                    </div>
                    <div class="form-group">
                        <label for="facility_phone_number">Facility Phone Number</label>
                        <input type="tel" class="form-control" id="facility_phone_number" name="facility_details[phone_number]" value="<?php echo htmlspecialchars($facility_details["phone_number"] ?? ""); ?>">
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea class="form-control" id="address" name="facility_details[address]" rows="3"><?php echo htmlspecialchars($facility_details["address"] ?? ""); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="facility_description">Facility Description</label>
                        <textarea class="form-control" id="facility_description" name="facility_details[description]" rows="3"><?php echo htmlspecialchars($facility_details["description"] ?? ""); ?></textarea>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary btn-lg">Save Changes</button>
        <a href="<?php echo base_url("userprofile/view/" . $profile_data["id"]); ?>" class="btn btn-secondary btn-lg">Cancel</a>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const editProfileForm = document.getElementById("editProfileForm");
    const newPasswordInput = document.getElementById("new_password");
    const confirmNewPasswordInput = document.getElementById("confirm_new_password");

    if (editProfileForm) {
        editProfileForm.addEventListener("submit", async function(event) {
            event.preventDefault();
            event.stopPropagation();

            if (newPasswordInput.value !== confirmNewPasswordInput.value) {
                confirmNewPasswordInput.classList.add("is-invalid");
                confirmNewPasswordInput.nextElementSibling.textContent = "New passwords do not match.";
                displayMessage("danger", "New passwords do not match. Please check and try again.", "messageContainerGlobal");
                return;
            }
            confirmNewPasswordInput.classList.remove("is-invalid");

            if (!editProfileForm.checkValidity()) {
                editProfileForm.classList.add("was-validated");
                displayMessage("danger", "Please fill in all required fields correctly.", "messageContainerGlobal");
                return;
            }

            const formData = new FormData(editProfileForm);
            const data = {};
            // Basic user data
            data.user_id = formData.get("user_id");
            data.csrf_token = formData.get("csrf_token");
            data.username = formData.get("username");
            data.email = formData.get("email");
            if (formData.get("new_password")) {
                data.new_password = formData.get("new_password");
                data.current_password = formData.get("current_password"); // Required if changing password
            }
            
            // User type specific details
            const userType = formData.get("user_type");
            data.user_type = userType;
            if (userType === "professional") {
                data.professional_details = {};
                for (const key of formData.keys()) {
                    if (key.startsWith("professional_details[")) {
                        const detailKey = key.match(/\[(.*?)\]/)[1];
                        data.professional_details[detailKey] = formData.get(key);
                    }
                }
            } else if (userType === "facility") {
                data.facility_details = {};
                 for (const key of formData.keys()) {
                    if (key.startsWith("facility_details[")) {
                        const detailKey = key.match(/\[(.*?)\]/)[1];
                        data.facility_details[detailKey] = formData.get(key);
                    }
                }
            }

            const submitButton = editProfileForm.querySelector("button[type=\"submit\"]");
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = 
            submitButton.disabled = true;

            try {
                const response = await fetchData("<?php echo base_url("userprofile/update"); ?>", {
                    method: "POST",
                    body: JSON.stringify(data)
                });

                if (response.status === "success") {
                    displayMessage("success", response.message || "Profile updated successfully!", "messageContainerGlobal");
                    // Optionally redirect or just show success
                    setTimeout(() => {
                         // window.location.href = "<?php echo base_url("userprofile/view/" . $profile_data["id"]); ?>";
                    }, 1500);
                } else {
                    displayMessage("danger", response.message || "Failed to update profile. Please try again.", "messageContainerGlobal");
                }
            } catch (error) {
                displayMessage("danger", error.message || "An unexpected error occurred.", "messageContainerGlobal");
            } finally {
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
            }
        });
    }

    // Real-time new password confirmation validation
    if (newPasswordInput && confirmNewPasswordInput) {
        confirmNewPasswordInput.addEventListener("input", function() {
            if (newPasswordInput.value !== confirmNewPasswordInput.value) {
                confirmNewPasswordInput.setCustomValidity("New passwords do not match.");
                confirmNewPasswordInput.classList.add("is-invalid");
                 confirmNewPasswordInput.nextElementSibling.textContent = "New passwords do not match.";
            } else {
                confirmNewPasswordInput.setCustomValidity("");
                confirmNewPasswordInput.classList.remove("is-invalid");
            }
        });
        newPasswordInput.addEventListener("input", function() {
            if (confirmNewPasswordInput.value !== "") {
                 if (newPasswordInput.value !== confirmNewPasswordInput.value) {
                    confirmNewPasswordInput.setCustomValidity("New passwords do not match.");
                    confirmNewPasswordInput.classList.add("is-invalid");
                    confirmNewPasswordInput.nextElementSibling.textContent = "New passwords do not match.";
                } else {
                    confirmNewPasswordInput.setCustomValidity("");
                    confirmNewPasswordInput.classList.remove("is-invalid");
                }
            }
        });
    }
});
</script>

<?php
require_once APP_ROOT . "/templates/footer.php";
?>
