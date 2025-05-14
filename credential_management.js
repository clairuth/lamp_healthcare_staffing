// assets/js/credential_management.js

document.addEventListener("DOMContentLoaded", function() {
    const credentialsContainer = document.getElementById("credentialsContainer");
    const addCredentialForm = document.getElementById("addCredentialForm");
    const texasLicenseVerificationBtn = document.getElementById("verifyTexasLicenseBtn");

    if (credentialsContainer) {
        loadUserCredentials();
    }

    if (addCredentialForm) {
        addCredentialForm.addEventListener("submit", async function(event) {
            event.preventDefault();
            event.stopPropagation();

            if (!addCredentialForm.checkValidity()) {
                addCredentialForm.classList.add("was-validated");
                displayMessage("danger", "Please correct the errors in the form.", "messageContainerCredentials");
                return;
            }

            const formData = new FormData(addCredentialForm);
            const submitButton = addCredentialForm.querySelector("button[type=\"submit\"]");
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Uploading...`;
            submitButton.disabled = true;

            try {
                const response = await fetchData(base_url("credential/upload"), {
                    method: "POST",
                    body: formData
                });

                if (response.status === "success") {
                    displayMessage("success", response.message || "Credential uploaded successfully!", "messageContainerCredentials");
                    addCredentialForm.reset();
                    addCredentialForm.classList.remove("was-validated");
                    loadUserCredentials(); // Refresh the list
                } else {
                    displayMessage("danger", response.message || "Failed to upload credential.", "messageContainerCredentials");
                }
            } catch (error) {
                displayMessage("danger", error.message || "An unexpected error occurred.", "messageContainerCredentials");
            } finally {
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
            }
        });
    }

    if (texasLicenseVerificationBtn) {
        texasLicenseVerificationBtn.addEventListener("click", async function() {
            const licenseNumberInput = document.getElementById("texas_license_number");
            const licenseNumber = licenseNumberInput ? licenseNumberInput.value.trim() : null;
            const verificationResultDiv = document.getElementById("texasLicenseVerificationResult");

            if (!licenseNumber) {
                displayMessage("warning", "Please enter a Texas license number to verify.", "messageContainerCredentials", verificationResultDiv);
                return;
            }

            this.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verifying...`;
            this.disabled = true;
            verificationResultDiv.innerHTML = ""; // Clear previous results

            try {
                const response = await fetchData(base_url("credential/verifyTexasLicense"), {
                    method: "POST",
                    body: JSON.stringify({ 
                        license_number: licenseNumber,
                        csrf_token: document.querySelector("input[name=\"csrf_token\"]") ? document.querySelector("input[name=\"csrf_token\"]").value : ""
                     })
                });

                if (response.status === "success") {
                    let resultMessage = `<p class="text-success">Verification Result: ${sanitizeHTML(response.message)}</p>`;
                    if(response.data){
                        resultMessage += `<ul class="list-unstyled">`;
                        for(const key in response.data){
                            resultMessage += `<li><strong>${sanitizeHTML(key.replace(/_/g, " ").replace(/\b\w/g, l => l.toUpperCase()))}:</strong> ${sanitizeHTML(response.data[key])}</li>`;
                        }
                        resultMessage += `</ul>`;
                        // If verification is successful and data is returned, you might want to auto-fill parts of the credential form
                        // or mark the license as verified in the UI.
                    }
                    displayMessage("success", resultMessage, "messageContainerCredentials", verificationResultDiv, false);
                } else {
                    displayMessage("danger", `Verification Failed: ${sanitizeHTML(response.message || "Could not verify license.")}`, "messageContainerCredentials", verificationResultDiv);
                }
            } catch (error) {
                displayMessage("danger", `Error: ${sanitizeHTML(error.message || "Verification service unavailable.")}`, "messageContainerCredentials", verificationResultDiv);
            } finally {
                this.innerHTML = "Verify Texas License";
                this.disabled = false;
            }
        });
    }
});

async function loadUserCredentials() {
    const container = document.getElementById("credentialsContainer");
    if (!container) return;

    container.innerHTML = `<div class="loader-container text-center"><div class="loader"></div><p>Loading credentials...</p></div>`;
    try {
        const response = await fetchData(base_url("credential/list")); // Assumes current user context
        container.innerHTML = ""; // Clear loader

        if (response.status === "success" && response.data && response.data.length > 0) {
            const ul = document.createElement("ul");
            ul.className = "list-group credentials-list";
            response.data.forEach(cred => {
                const li = document.createElement("li");
                li.className = "list-group-item mb-2 shadow-sm";
                let verificationStatus = "Not Verified";
                let statusClass = "secondary";
                if (cred.is_verified == 1) {
                    verificationStatus = "Verified";
                    statusClass = "success";
                } else if (cred.verification_status === "pending"){
                    verificationStatus = "Pending Verification";
                    statusClass = "warning";
                } else if (cred.verification_status === "failed"){
                    verificationStatus = "Verification Failed";
                    statusClass = "danger";
                }

                li.innerHTML = `
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">${sanitizeHTML(cred.credential_type)} - ${sanitizeHTML(cred.credential_name || cred.license_number)}</h5>
                        <small>Expires: ${cred.expiry_date ? formatDate(cred.expiry_date) : "N/A"}</small>
                    </div>
                    <p class="mb-1">Issued by: ${sanitizeHTML(cred.issuing_organization || "N/A")}</p>
                    <p class="mb-1">Status: <span class="badge bg-${statusClass}">${verificationStatus}</span></p>
                    ${cred.file_path ? `<a href="${base_url("uploads/credentials/")}${sanitizeHTML(cred.file_path)}" target="_blank" class="btn btn-sm btn-outline-info mr-2">View Document</a>` : ""}
                    <button class="btn btn-sm btn-outline-danger delete-credential-btn" data-id="${cred.id}">Delete</button>
                `;
                ul.appendChild(li);
            });
            container.appendChild(ul);
            addDeleteCredentialListeners();
        } else if (response.status === "success" && (!response.data || response.data.length === 0)) {
            container.innerHTML = "<p>You have not uploaded any credentials yet.</p>";
        } else {
            container.innerHTML = `<p class="text-danger">Could not load credentials. ${response.message || "Please try again."}</p>`;
        }
    } catch (error) {
        console.error("Error fetching credentials:", error);
        container.innerHTML = "<p class=\"text-danger\">Could not load credentials. Please try again later.</p>";
    }
}

function addDeleteCredentialListeners() {
    document.querySelectorAll(".delete-credential-btn").forEach(button => {
        button.addEventListener("click", async function() {
            const credentialId = this.dataset.id;
            if (confirm("Are you sure you want to delete this credential?")) {
                try {
                    const response = await fetchData(base_url("credential/delete/") + credentialId, {
                        method: "POST", // Or DELETE, ensure backend supports it
                        body: JSON.stringify({ csrf_token: document.querySelector("input[name=\"csrf_token\"]") ? document.querySelector("input[name=\"csrf_token\"]").value : "" })
                    });
                    if (response.status === "success") {
                        displayMessage("success", response.message || "Credential deleted successfully.", "messageContainerCredentials");
                        loadUserCredentials(); // Refresh list
                    } else {
                        displayMessage("danger", response.message || "Failed to delete credential.", "messageContainerCredentials");
                    }
                } catch (error) {
                    displayMessage("danger", error.message || "Error deleting credential.", "messageContainerCredentials");
                }
            }
        });
    });
}

// Ensure displayMessage, sanitizeHTML, formatDate are available (e.g., from main.js)
// const base_url = function(path = "") { return document.documentElement.dataset.baseUrl + path; };

