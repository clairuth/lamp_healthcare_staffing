<?php
// templates/shifts/facility_shifts.php

$page_title = "My Posted Shifts - StaffingPlus";
require_once APP_ROOT . "/templates/header.php";

if (!is_logged_in() || get_current_user_type() !== "facility") {
    display_error_message("Access Denied: You must be logged in as a facility to view your shifts.");
    require_once APP_ROOT . "/templates/footer.php";
    exit;
}

$facility_id = get_current_user_id(); 

?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>My Posted Shifts</h1>
        <a href="<?php echo base_url("shift/create"); ?>" class="btn btn-primary">Post New Shift</a>
    </div>

    <div id="messageContainerGlobal"></div>

    <div id="facilityShiftsContainer">
        <div class="loader-container text-center">
            <div class="loader"></div>
            <p>Loading your shifts...</p>
        </div>
        <!-- Shifts will be loaded here by JavaScript -->
    </div>
</div>

<!-- Modal for Viewing Applicants -->
<div class="modal fade" id="viewApplicantsModal" tabindex="-1" aria-labelledby="viewApplicantsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewApplicantsModalLabel">Applicants for Shift: <span id="modalShiftTitle"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="applicantsListContainer">
                <!-- Applicants list will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const facilityShiftsContainer = document.getElementById("facilityShiftsContainer");
    const applicantsListContainer = document.getElementById("applicantsListContainer");
    const modalShiftTitle = document.getElementById("modalShiftTitle");

    async function loadFacilityShifts() {
        facilityShiftsContainer.innerHTML = `<div class="loader-container text-center"><div class="loader"></div><p>Loading your shifts...</p></div>`;
        try {
            const response = await fetchData("<?php echo base_url("shift/listByFacility/". $facility_id); ?>");
            facilityShiftsContainer.innerHTML = ""; // Clear loader

            if (response.status === "success" && response.data && response.data.length > 0) {
                response.data.forEach(shift => {
                    const shiftCard = document.createElement("div");
                    shiftCard.className = "card shift-card mb-3";
                    shiftCard.innerHTML = `
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">${sanitizeHTML(shift.title)}</h5>
                            <span class="badge bg-${getShiftStatusClass(shift.status)}">${sanitizeHTML(shift.status)}</span>
                        </div>
                        <div class="card-body">
                            <p class="card-text"><strong>Dates:</strong> ${formatDateTime(shift.start_time)} to ${formatDateTime(shift.end_time)}</p>
                            <p class="card-text"><strong>Pay Rate:</strong> ${sanitizeHTML(shift.pay_rate)}</p>
                            <p class="card-text"><strong>Description:</strong> ${sanitizeHTML(shift.description.substring(0,150))}${shift.description.length > 150 ? "..." : ""}</p>
                            <p class="card-text"><small class="text-muted">Posted on: ${formatDateTime(shift.created_at, false)}</small></p>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="<?php echo base_url("shift/view/"); ?>${shift.id}" class="btn btn-sm btn-info mr-2">View Details</a>
                            <a href="<?php echo base_url("shift/edit/"); ?>${shift.id}" class="btn btn-sm btn-secondary mr-2">Edit Shift</a>
                            <button class="btn btn-sm btn-primary view-applicants-btn" data-shift-id="${shift.id}" data-shift-title="${sanitizeHTML(shift.title)}" data-toggle="modal" data-target="#viewApplicantsModal">
                                View Applicants (${shift.applicant_count || 0})
                            </button>
                            ${shift.status === "open" || shift.status === "draft" ? `<button class="btn btn-sm btn-danger delete-shift-btn ml-2" data-id="${shift.id}">Delete Shift</button>` : ""}
                        </div>
                    `;
                    facilityShiftsContainer.appendChild(shiftCard);
                });
                addEventListenersForShiftActions();
            } else {
                facilityShiftsContainer.innerHTML = "<p>You have not posted any shifts yet. <a href='<?php echo base_url("shift/create"); ?>'>Post one now!</a></p>";
            }
        } catch (error) {
            console.error("Error fetching facility shifts:", error);
            facilityShiftsContainer.innerHTML = "<p class=\"text-danger\">Could not load your shifts. Please try again later.</p>";
        }
    }

    function addEventListenersForShiftActions() {
        document.querySelectorAll(".view-applicants-btn").forEach(button => {
            button.addEventListener("click", function() {
                const shiftId = this.dataset.shiftId;
                const shiftTitle = this.dataset.shiftTitle;
                modalShiftTitle.textContent = shiftTitle;
                loadShiftApplicants(shiftId);
            });
        });

        document.querySelectorAll(".delete-shift-btn").forEach(button => {
            button.addEventListener("click", async function() {
                const shiftId = this.dataset.id;
                if (confirm("Are you sure you want to delete this shift? This action cannot be undone.")) {
                    try {
                        const response = await fetchData("<?php echo base_url("shift/delete/"); ?>" + shiftId, {
                            method: "POST",
                            body: JSON.stringify({ csrf_token: "<?php echo htmlspecialchars(generate_csrf_token()); ?>" })
                        });
                        if (response.status === "success") {
                            displayMessage("success", response.message || "Shift deleted successfully.", "messageContainerGlobal");
                            loadFacilityShifts(); // Refresh the list
                        } else {
                            displayMessage("danger", response.message || "Failed to delete shift.", "messageContainerGlobal");
                        }
                    } catch (error) {
                        displayMessage("danger", error.message || "Error deleting shift.", "messageContainerGlobal");
                    }
                }
            });
        });
    }

    async function loadShiftApplicants(shiftId) {
        applicantsListContainer.innerHTML = `<div class="loader-container text-center"><div class="loader"></div><p>Loading applicants...</p></div>`;
        try {
            const response = await fetchData("<?php echo base_url("shiftapplication/listByShift/"); ?>" + shiftId);
            applicantsListContainer.innerHTML = ""; // Clear loader

            if (response.status === "success" && response.data && response.data.length > 0) {
                const table = document.createElement("table");
                table.className = "table table-hover";
                table.innerHTML = `
                    <thead>
                        <tr>
                            <th>Applicant</th>
                            <th>Applied On</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                `;
                const tbody = table.querySelector("tbody");
                response.data.forEach(app => {
                    const tr = document.createElement("tr");
                    tr.innerHTML = `
                        <td><a href="<?php echo base_url("userprofile/view/"); ?>${app.professional_user_id}">${sanitizeHTML(app.professional_name || "View Profile")}</a></td>
                        <td>${formatDateTime(app.applied_at, false)}</td>
                        <td><span class="badge bg-${getApplicationStatusClass(app.status)}">${sanitizeHTML(app.status)}</span></td>
                        <td>
                            ${app.status === "pending" ? 
                                `<button class="btn btn-sm btn-success accept-applicant-btn" data-application-id="${app.id}" data-shift-id="${shiftId}">Accept</button>
                                 <button class="btn btn-sm btn-warning reject-applicant-btn ml-1" data-application-id="${app.id}" data-shift-id="${shiftId}">Reject</button>` : 
                                (app.status === "accepted" ? "Accepted" : (app.status === "rejected" ? "Rejected" : ""))
                            }
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
                applicantsListContainer.appendChild(table);
                addEventListenersForApplicantActions(shiftId);
            } else {
                applicantsListContainer.innerHTML = "<p>No applicants for this shift yet.</p>";
            }
        } catch (error) {
            console.error("Error fetching applicants:", error);
            applicantsListContainer.innerHTML = "<p class=\"text-danger\">Could not load applicants. Please try again later.</p>";
        }
    }

    function addEventListenersForApplicantActions(currentShiftId) {
        document.querySelectorAll(".accept-applicant-btn, .reject-applicant-btn").forEach(button => {
            button.addEventListener("click", async function() {
                const applicationId = this.dataset.applicationId;
                const action = this.classList.contains("accept-applicant-btn") ? "accept" : "reject";
                const shiftIdForReload = this.dataset.shiftId; // Use the shiftId stored on the button

                if (confirm(`Are you sure you want to ${action} this applicant?`)) {
                    try {
                        const response = await fetchData("<?php echo base_url("shiftapplication/"); ?>" + action + "/" + applicationId, {
                            method: "POST",
                            body: JSON.stringify({ csrf_token: "<?php echo htmlspecialchars(generate_csrf_token()); ?>" })
                        });
                        if (response.status === "success") {
                            displayMessage("success", response.message || `Applicant ${action}ed successfully.`, "messageContainerGlobal");
                            loadShiftApplicants(shiftIdForReload); // Reload applicants for the specific shift
                            loadFacilityShifts(); // Also refresh the main shifts list to update applicant counts
                        } else {
                            displayMessage("danger", response.message || `Failed to ${action} applicant.`, "messageContainerGlobal");
                        }
                    } catch (error) {
                        displayMessage("danger", error.message || `Error ${action}ing applicant.`, "messageContainerGlobal");
                    }
                }
            });
        });
    }

    function getShiftStatusClass(status) {
        switch (status.toLowerCase()) {
            case "open": return "success";
            case "filled": return "primary";
            case "completed": return "info";
            case "cancelled": return "danger";
            case "draft": return "secondary";
            default: return "light";
        }
    }

    function getApplicationStatusClass(status) {
        switch (status.toLowerCase()) {
            case "pending": return "warning";
            case "accepted": return "success";
            case "rejected": return "danger";
            case "withdrawn": return "secondary";
            default: return "light";
        }
    }

    // Initial load
    loadFacilityShifts();
});
</script>

<?php
require_once APP_ROOT . "/templates/footer.php";
?>
