// assets/js/profile_management.js

document.addEventListener("DOMContentLoaded", function() {
    // For view_profile.php - dynamically load profile data if not fully server-rendered
    const profileDataContainer = document.getElementById("profileDataContainer");
    if (profileDataContainer && profileDataContainer.dataset.userId) {
        loadUserProfile(profileDataContainer.dataset.userId);
    }

    // For edit_profile.php - handle form submission
    const editProfileForm = document.getElementById("editProfileForm");
    if (editProfileForm) {
        editProfileForm.addEventListener("submit", async function(event) {
            event.preventDefault();
            event.stopPropagation();

            if (!editProfileForm.checkValidity()) {
                editProfileForm.classList.add("was-validated");
                displayMessage("danger", "Please correct the errors in the form.", "messageContainerEditProfile");
                return;
            }

            const formData = new FormData(editProfileForm);
            const userId = formData.get("user_id"); // Assuming user_id is part of the form or known
            const submitButton = editProfileForm.querySelector("button[type=\"submit\"]");
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...`;
            submitButton.disabled = true;

            try {
                // Adjust endpoint as per your UserProfileController route for updates
                const response = await fetchData(base_url("userprofile/update/") + userId, {
                    method: "POST",
                    body: formData // Using FormData directly if backend handles it, or convert to JSON
                });

                if (response.status === "success") {
                    displayMessage("success", response.message || "Profile updated successfully!", "messageContainerEditProfile");
                    editProfileForm.classList.remove("was-validated");
                    // Optionally, redirect to view profile page or refresh data
                    setTimeout(() => {
                        // window.location.href = base_url("userprofile/view/") + userId;
                    }, 1500);
                } else {
                    displayMessage("danger", response.message || "Failed to update profile. Please try again.", "messageContainerEditProfile");
                }
            } catch (error) {
                displayMessage("danger", error.message || "An unexpected error occurred.", "messageContainerEditProfile");
            } finally {
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
            }
        });
    }
});

async function loadUserProfile(userId) {
    const profileDataContainer = document.getElementById("profileDataContainer");
    profileDataContainer.innerHTML = `<div class="loader-container text-center"><div class="loader"></div><p>Loading profile...</p></div>`;
    try {
        // Adjust endpoint as per your UserProfileController route for fetching profile data
        const response = await fetchData(base_url("userprofile/get/") + userId);
        profileDataContainer.innerHTML = ""; // Clear loader

        if (response.status === "success" && response.data) {
            const profile = response.data;
            // Populate the profileDataContainer with formatted HTML
            // This is a simplified example; you would build more structured HTML here
            let html = `<dl class="row">`;
            html += `<dt class="col-sm-3">Username:</dt><dd class="col-sm-9">${sanitizeHTML(profile.username)}</dd>`;
            html += `<dt class="col-sm-3">Email:</dt><dd class="col-sm-9">${sanitizeHTML(profile.email)}</dd>`;
            html += `<dt class="col-sm-3">Full Name:</dt><dd class="col-sm-9">${sanitizeHTML(profile.full_name || "N/A")}</dd>`;
            html += `<dt class="col-sm-3">Phone:</dt><dd class="col-sm-9">${sanitizeHTML(profile.phone_number || "N/A")}</dd>`;
            // Add more fields as per your HealthcareProfessional or Facility models
            if (profile.bio) {
                 html += `<dt class="col-sm-3">Bio:</dt><dd class="col-sm-9">${sanitizeHTML(profile.bio)}</dd>`;
            }
            if (profile.specialty) { // Example for professional
                 html += `<dt class="col-sm-3">Specialty:</dt><dd class="col-sm-9">${sanitizeHTML(profile.specialty)}</dd>`;
            }
            if (profile.facility_name) { // Example for facility
                 html += `<dt class="col-sm-3">Facility Name:</dt><dd class="col-sm-9">${sanitizeHTML(profile.facility_name)}</dd>`;
            }
            html += `</dl>`;
            profileDataContainer.innerHTML = html;
        } else {
            profileDataContainer.innerHTML = `<p class="text-danger">Could not load profile data. ${response.message || "Please try again."}</p>`;
        }
    } catch (error) {
        console.error("Error fetching profile:", error);
        profileDataContainer.innerHTML = "<p class=\"text-danger\">Could not load profile data. Please try again later.</p>";
    }
}

// Ensure displayMessage and sanitizeHTML are available (e.g., from main.js or included directly)
// function displayMessage(type, message, containerId) { ... }
// function sanitizeHTML(str) { ... }

