<?php
// templates/credentials/manage_credentials.php

$page_title = "Manage My Credentials - StaffingPlus";
require_once APP_ROOT . "/templates/header.php";

if (!is_logged_in() || get_current_user_type() !== "professional") {
    display_error_message("Access Denied: You must be logged in as a professional to manage credentials.");
    require_once APP_ROOT . "/templates/footer.php";
    exit;
}

$professional_user_id = get_current_user_id();

// The CredentialController should make $credentials available to this template
// For now, we will fetch them via JavaScript

?>

<div class="container mt-4">
    <h1 class="mb-4">My Credentials</h1>

    <div id="messageContainerGlobal"></div>

    <div class="card mb-4">
        <div class="card-header">Upload New Credential</div>
        <div class="card-body">
            <form id="uploadCredentialForm" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                <input type="hidden" name="professional_user_id" value="<?php echo $professional_user_id; ?>">

                <div class="form-group">
                    <label for="credential_type_id">Credential Type</label>
                    <select class="form-control" id="credential_type_id" name="credential_type_id" required>
                        <option value="">Select Type...</option>
                        <!-- These should ideally come from the database or a config file -->
                        <option value="1">RN License</option>
                        <option value="2">LPN License</option>
                        <option value="3">CNA Certification</option>
                        <option value="4">BLS Certification</option>
                        <option value="5">ACLS Certification</option>
                        <option value="6">PALS Certification</option>
                        <option value="7">Driver_s License</option>
                        <option value="8">TB Test Result</option>
                        <option value="9">COVID-19 Vaccination Card</option>
                        <option value="10">Flu Shot Record</option>
                        <option value="11">Other</option>
                    </select>
                    <div class="invalid-feedback">Please select a credential type.</div>
                </div>

                <div class="form-group">
                    <label for="credential_number">License/Certification Number (if applicable)</label>
                    <input type="text" class="form-control" id="credential_number" name="credential_number">
                </div>

                <div class="form-group">
                    <label for="issuing_organization">Issuing Organization/State Board</label>
                    <input type="text" class="form-control" id="issuing_organization" name="issuing_organization">
                </div>

                <div class="form-group">
                    <label for="issue_date">Issue Date</label>
                    <input type="date" class="form-control" id="issue_date" name="issue_date">
                </div>

                <div class="form-group">
                    <label for="expiration_date">Expiration Date</label>
                    <input type="date" class="form-control" id="expiration_date" name="expiration_date" required>
                    <div class="invalid-feedback">Please provide an expiration date.</div>
                </div>

                <div class="form-group">
                    <label for="credential_file">Upload File (PDF, JPG, PNG - Max 2MB)</label>
                    <div class="file-upload-wrapper" onclick="document.getElementById("credential_file").click();">
                        <input type="file" class="form-control" id="credential_file" name="credential_file" accept=".pdf,.jpg,.jpeg,.png" required>
                        <span class="file-upload-text"><span class="icon">⬆️</span> Click or drag file here</span>
                    </div>
                    <small id="fileNameDisplay" class="form-text text-muted"></small>
                    <div class="invalid-feedback">Please select a file to upload.</div>
                </div>

                <button type="submit" class="btn btn-primary">Upload Credential</button>
            </form>
        </div>
    </div>

    <h2 class="mt-5 mb-3">My Uploaded Credentials</h2>
    <div id="credentialsListContainer">
        <div class="loader-container text-center">
            <div class="loader"></div>
            <p>Loading your credentials...</p>
        </div>
        <!-- Credentials will be loaded here by JavaScript -->
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const uploadCredentialForm = document.getElementById("uploadCredentialForm");
    const credentialsListContainer = document.getElementById("credentialsListContainer");
    const credentialFileInput = document.getElementById("credential_file");
    const fileNameDisplay = document.getElementById("fileNameDisplay");

    if (credentialFileInput && fileNameDisplay) {
        credentialFileInput.addEventListener("change", function() {
            if (this.files && this.files.length > 0) {
                fileNameDisplay.textContent = `Selected file: ${this.files[0].name}`;
            } else {
                fileNameDisplay.textContent = "";
            }
        });
    }

    async function loadCredentials() {
        credentialsListContainer.innerHTML = `<div class="loader-container text-center"><div class="loader"></div><p>Loading your credentials...</p></div>`;
        try {
            const response = await fetchData("<?php echo base_url("credential/listByUser"); ?>"); // Endpoint needs to be user-specific
            credentialsListContainer.innerHTML = ""; // Clear loader

            if (response.status === "success" && response.data && response.data.length > 0) {
                const table = document.createElement("table");
                table.className = "table table-striped table-hover";
                table.innerHTML = `
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Number</th>
                            <th>Expires</th>
                            <th>Status</th>
                            <th>File</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                `;
                const tbody = table.querySelector("tbody");
                response.data.forEach(cred => {
                    const row = tbody.insertRow();
                    row.innerHTML = `
                        <td>${sanitizeHTML(cred.credential_type_name || "N/A")}</td>
                        <td>${sanitizeHTML(cred.credential_number || "N/A")}</td>
                        <td>${cred.expiration_date ? new Date(cred.expiration_date).toLocaleDateString() : "N/A"}</td>
                        <td><span class="badge bg-${getVerificationStatusClass(cred.verification_status)}">${sanitizeHTML(cred.verification_status || "Pending")}</span></td>
                        <td><a href="<?php echo base_url(); ?>${sanitizeHTML(cred.file_path)}" target="_blank" class="btn btn-sm btn-outline-info">View</a></td>
                        <td>
                            <button class="btn btn-sm btn-danger delete-credential-btn" data-id="${cred.id}">Delete</button>
                        </td>
                    `;
                });
                credentialsListContainer.appendChild(table);
                addDeleteEventListeners();
            } else {
                credentialsListContainer.innerHTML = "<p>You have not uploaded any credentials yet.</p>";
            }
        } catch (error) {
            console.error("Error fetching credentials:", error);
            credentialsListContainer.innerHTML = "<p class=\"text-danger\">Could not load your credentials. Please try again later.</p>";
        }
    }

    if (uploadCredentialForm) {
        uploadCredentialForm.addEventListener("submit", async function(event) {
            event.preventDefault();
            event.stopPropagation();

            if (!uploadCredentialForm.checkValidity()) {
                uploadCredentialForm.classList.add("was-validated");
                displayMessage("danger", "Please fill in all required fields and select a file.", "messageContainerGlobal");
                return;
            }

            const formData = new FormData(uploadCredentialForm);
            // Note: For file uploads, we don"t stringify to JSON. We send FormData directly.

            const submitButton = uploadCredentialForm.querySelector("button[type=\"submit\"]");
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = 
            submitButton.disabled = true;

            try {
                // Special handling for file upload with fetch
                const response = await fetch("<?php echo base_url("credential/upload"); ?>", {
                    method: "POST",
                    body: formData, // Send FormData directly
                    headers: {
                        // "Content-Type": "multipart/form-data" is set automatically by browser with FormData
                        "Accept": "application/json",
                        // Add CSRF token if your backend expects it in headers for AJAX file uploads
                    }
                });
                
                const result = await response.json();

                if (response.ok && result.status === "success") {
                    displayMessage("success", result.message || "Credential uploaded successfully!", "messageContainerGlobal");
                    uploadCredentialForm.reset();
                    fileNameDisplay.textContent = "";
                    uploadCredentialForm.classList.remove("was-validated");
                    loadCredentials(); // Refresh the list
                } else {
                    displayMessage("danger", result.message || "Upload failed. Please try again.", "messageContainerGlobal");
                }
            } catch (error) {
                displayMessage("danger", error.message || "An unexpected error occurred during upload.", "messageContainerGlobal");
            } finally {
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
            }
        });
    }

    function addDeleteEventListeners() {
        document.querySelectorAll(".delete-credential-btn").forEach(button => {
            button.addEventListener("click", async function() {
                const credId = this.dataset.id;
                if (confirm("Are you sure you want to delete this credential?")) {
                    try {
                        const response = await fetchData("<?php echo base_url("credential/delete/"); ?>" + credId, {
                            method: "POST", // Or DELETE, if your router supports it and CSRF is handled
                            body: JSON.stringify({ csrf_token: "<?php echo htmlspecialchars(generate_csrf_token()); ?>" }) // Send CSRF if using POST for delete
                        });
                        if (response.status === "success") {
                            displayMessage("success", response.message || "Credential deleted.", "messageContainerGlobal");
                            loadCredentials(); // Refresh list
                        } else {
                            displayMessage("danger", response.message || "Failed to delete credential.", "messageContainerGlobal");
                        }
                    } catch (error) {
                        displayMessage("danger", error.message || "Error deleting credential.", "messageContainerGlobal");
                    }
                }
            });
        });
    }

    function getVerificationStatusClass(status) {
        if (!status) return "secondary";
        switch (status.toLowerCase()) {
            case "verified": return "success";
            case "pending": return "warning";
            case "rejected": return "danger";
            default: return "secondary";
        }
    }
    
    function sanitizeHTML(str) {
        if (!str) return "";
        const temp = document.createElement("div");
        temp.textContent = str;
        return temp.innerHTML;
    }

    // Initial load of credentials
    loadCredentials();
});
</script>

<?php
require_once APP_ROOT . "/templates/footer.php";
?>
