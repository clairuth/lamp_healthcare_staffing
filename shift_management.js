// assets/js/shift_management.js

document.addEventListener("DOMContentLoaded", function() {
    // For list_open_shifts.php or facility_shifts.php - dynamically load shifts
    const shiftsContainer = document.getElementById("shiftsContainer");
    if (shiftsContainer) {
        const listType = shiftsContainer.dataset.listType || "open"; // 'open', 'facility', 'applied'
        const facilityId = shiftsContainer.dataset.facilityId || null;
        const professionalId = shiftsContainer.dataset.professionalId || null;
        loadShifts(shiftsContainer, listType, facilityId, professionalId);
    }

    // For create_shift.php - handle form submission
    const createShiftForm = document.getElementById("createShiftForm");
    if (createShiftForm) {
        createShiftForm.addEventListener("submit", async function(event) {
            event.preventDefault();
            event.stopPropagation();

            if (!createShiftForm.checkValidity()) {
                createShiftForm.classList.add("was-validated");
                displayMessage("danger", "Please correct the errors in the form.", "messageContainerCreateShift");
                return;
            }

            const formData = new FormData(createShiftForm);
            const submitButton = createShiftForm.querySelector("button[type=\"submit\"]");
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Creating Shift...`;
            submitButton.disabled = true;

            try {
                const response = await fetchData(base_url("shift/create"), {
                    method: "POST",
                    body: formData
                });

                if (response.status === "success") {
                    displayMessage("success", response.message || "Shift created successfully!", "messageContainerCreateShift");
                    createShiftForm.reset();
                    createShiftForm.classList.remove("was-validated");
                    // Optionally, redirect to facility shifts page or refresh list if on the same page
                    // if (shiftsContainer && shiftsContainer.dataset.listType === "facility") {
                    //    loadShifts(shiftsContainer, "facility", facilityId);
                    // }
                } else {
                    displayMessage("danger", response.message || "Failed to create shift. Please try again.", "messageContainerCreateShift");
                }
            } catch (error) {
                displayMessage("danger", error.message || "An unexpected error occurred.", "messageContainerCreateShift");
            } finally {
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
            }
        });
    }

    // For view_shift.php - handle shift application
    const applyShiftButton = document.getElementById("applyShiftButton");
    if (applyShiftButton) {
        applyShiftButton.addEventListener("click", async function() {
            const shiftId = this.dataset.shiftId;
            const professionalId = this.dataset.professionalId; // Make sure this is available

            if (!professionalId) {
                displayMessage("warning", "You must be logged in as a professional to apply.", "messageContainerViewShift");
                return;
            }

            if (confirm("Are you sure you want to apply for this shift?")) {
                this.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Applying...`;
                this.disabled = true;
                try {
                    const response = await fetchData(base_url("shift/apply"), {
                        method: "POST",
                        body: JSON.stringify({ 
                            shift_id: shiftId, 
                            professional_id: professionalId, 
                            csrf_token: document.querySelector("input[name=\"csrf_token\"]") ? document.querySelector("input[name=\"csrf_token\"]").value : "" // Get CSRF token if available
                        })
                    });
                    if (response.status === "success") {
                        displayMessage("success", response.message || "Successfully applied for the shift!", "messageContainerViewShift");
                        this.textContent = "Applied";
                        // Optionally disable further clicks or change UI
                    } else {
                        displayMessage("danger", response.message || "Failed to apply for the shift.", "messageContainerViewShift");
                        this.innerHTML = "Apply for Shift";
                        this.disabled = false;
                    }
                } catch (error) {
                    displayMessage("danger", error.message || "Error applying for shift.", "messageContainerViewShift");
                    this.innerHTML = "Apply for Shift";
                    this.disabled = false;
                }
            }
        });
    }
});

async function loadShifts(container, listType = "open", facilityId = null, professionalId = null) {
    container.innerHTML = `<div class="loader-container text-center"><div class="loader"></div><p>Loading shifts...</p></div>`;
    let url = base_url("shift/list/");

    if (listType === "facility" && facilityId) {
        url = base_url("shift/listByFacility/") + facilityId;
    } else if (listType === "applied" && professionalId) {
        url = base_url("shift/listAppliedByProfessional/") + professionalId;
    } else {
        url = base_url("shift/listOpen"); // Default to open shifts
    }

    try {
        const response = await fetchData(url);
        container.innerHTML = ""; // Clear loader

        if (response.status === "success" && response.data && response.data.length > 0) {
            const ul = document.createElement("ul");
            ul.className = "list-group shift-list";
            response.data.forEach(shift => {
                const li = document.createElement("li");
                li.className = "list-group-item d-flex justify-content-between align-items-center mb-2 shadow-sm";
                
                let shiftStatusBadge = "";
                if(shift.status) {
                    let badgeClass = "secondary";
                    if(shift.status.toLowerCase() === "open") badgeClass = "success";
                    if(shift.status.toLowerCase() === "filled") badgeClass = "primary";
                    if(shift.status.toLowerCase() === "completed") badgeClass = "info";
                    if(shift.status.toLowerCase() === "cancelled") badgeClass = "danger";
                    shiftStatusBadge = `<span class="badge bg-${badgeClass} ml-2">${sanitizeHTML(shift.status)}</span>`;
                }

                li.innerHTML = `
                    <div>
                        <h5><a href="${base_url("shift/view/")}${shift.id}">${sanitizeHTML(shift.title || "Shift Details")}</a> ${shiftStatusBadge}</h5>
                        <p class="mb-1"><strong>Facility:</strong> ${sanitizeHTML(shift.facility_name || "N/A")}</p>
                        <p class="mb-1"><strong>Date:</strong> ${formatDate(shift.shift_date)}</p>
                        <p class="mb-1"><strong>Time:</strong> ${formatTime(shift.start_time)} - ${formatTime(shift.end_time)}</p>
                        <p class="mb-0"><strong>Rate:</strong> $${parseFloat(shift.pay_rate || 0).toFixed(2)}/hr</p>
                    </div>
                    <a href="${base_url("shift/view/")}${shift.id}" class="btn btn-sm btn-outline-primary">View Details</a>
                `;
                ul.appendChild(li);
            });
            container.appendChild(ul);
        } else if (response.status === "success" && (!response.data || response.data.length === 0)) {
            container.innerHTML = "<p>No shifts found matching your criteria.</p>";
        } else {
            container.innerHTML = `<p class="text-danger">Could not load shifts. ${response.message || "Please try again."}</p>`;
        }
    } catch (error) {
        console.error("Error fetching shifts:", error);
        container.innerHTML = "<p class=\"text-danger\">Could not load shifts. Please try again later.</p>";
    }
}

// Helper functions (ensure these are defined, e.g., in main.js or included)
// function formatDate(dateString) { ... }
// function formatTime(timeString) { ... }
// function sanitizeHTML(str) { ... }
// function displayMessage(type, message, containerId) { ... }
// const base_url = function(path = "") { return document.documentElement.dataset.baseUrl + path; }; // Assuming base_url is set on <html> data-base-url attribute

