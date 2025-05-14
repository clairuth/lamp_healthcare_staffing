<?php
// templates/shifts/view_shift.php

$page_title = "View Shift - StaffingPlus";
require_once APP_ROOT . "/templates/header.php";

// The shift ID should be passed as a parameter in the URL, e.g., /shift/view/123
// This is handled by the router in public/index.php, which calls the ShiftController::view($shift_id)
// The controller should fetch shift_details and make it available to this template.

if (!isset($shift_details) || empty($shift_details)) {
    echo "<div class=\"container mt-4\"><div class=\"alert alert-danger\">Shift details not found or you do not have permission to view this shift.</div></div>";
    require_once APP_ROOT . "/templates/footer.php";
    exit;
}

$is_professional = is_logged_in() && get_current_user_type() === "professional";
$has_applied = false; // This should be determined by the controller if the professional has already applied
if ($is_professional && isset($professional_applications)) {
    foreach ($professional_applications as $app) {
        if ($app["shift_id"] == $shift_details["id"]) {
            $has_applied = true;
            break;
        }
    }
}

?>

<div class="container mt-4">
    <div id="messageContainerGlobal"></div>

    <div class="card shift-detail-card">
        <div class="card-header">
            <h1 class="shift-title mb-0"><?php echo htmlspecialchars($shift_details["shift_title"]); ?></h1>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-7">
                    <h4 class="mb-3">Shift Details</h4>
                    <p><strong>Facility:</strong> <?php echo htmlspecialchars($shift_details["facility_name"] ?? "N/A"); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($shift_details["location"] ?? "N/A"); ?></p>
                    <p><strong>Department:</strong> <?php echo htmlspecialchars($shift_details["department"] ?? "N/A"); ?></p>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars(date("D, M j, Y", strtotime($shift_details["shift_date"]))); ?></p>
                    <p><strong>Time:</strong> <?php echo htmlspecialchars(date("g:i A", strtotime($shift_details["start_time"]))) . " - " . htmlspecialchars(date("g:i A", strtotime($shift_details["end_time"]))); ?></p>
                    <p><strong>Rate:</strong> $<?php echo htmlspecialchars(number_format($shift_details["hourly_rate"], 2)); ?>/hr</p>
                    <p><strong>Required Specialty:</strong> <?php echo htmlspecialchars($shift_details["specialty_name"] ?? "N/A"); ?></p>
                    
                    <h5 class="mt-4">Description</h5>
                    <p><?php echo nl2br(htmlspecialchars($shift_details["description"] ?? "No detailed description provided.")); ?></p>
                    
                    <h5 class="mt-4">Requirements</h5>
                    <p><?php echo nl2br(htmlspecialchars($shift_details["requirements"] ?? "No specific requirements listed.")); ?></p>
                </div>
                <div class="col-md-5">
                    <div class="apply-section p-3 bg-light rounded">
                        <h4 class="mb-3">Actions</h4>
                        <?php if ($is_professional): ?>
                            <?php if ($shift_details["is_filled"]): ?>
                                <p class="alert alert-info">This shift has already been filled.</p>
                            <?php elseif ($has_applied): ?>
                                <p class="alert alert-success">You have already applied for this shift.</p>
                                <a href="<?php echo base_url("shiftapplication/listByProfessional"); ?>" class="btn btn-info">View My Applications</a>
                            <?php else: ?>
                                <form id="applyShiftForm">
                                    <input type="hidden" name="shift_id" value="<?php echo $shift_details["id"]; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                                    <div class="form-group">
                                        <label for="cover_letter">Brief Cover Letter / Note (Optional)</label>
                                        <textarea class="form-control" id="cover_letter" name="cover_letter" rows="3"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-full-width">Apply for this Shift</button>
                                </form>
                            <?php endif; ?>
                        <?php elseif (is_logged_in() && get_current_user_type() === "facility" && get_current_user_id() == $shift_details["facility_user_id"] ): ?>
                            <p>This is one of your posted shifts.</p>
                            <a href="<?php echo base_url("shift/edit/" . $shift_details["id"]); ?>" class="btn btn-secondary btn-block mb-2">Edit Shift</a>
                            <a href="<?php echo base_url("shiftapplication/listByShift/" . $shift_details["id"]); ?>" class="btn btn-info btn-block">View Applicants (<?php echo $shift_details["application_count"] ?? 0; ?>)</a>
                        <?php elseif (!is_logged_in()): ?>
                            <p>Please <a href="<?php echo base_url("auth/login?redirect=shift/view/" . $shift_details["id"]); ?>">login</a> or <a href="<?php echo base_url("auth/register?role=professional"); ?>">register as a professional</a> to apply.</p>
                        <?php else: ?>
                             <p class="text-muted">Shift details.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const applyShiftForm = document.getElementById("applyShiftForm");

    if (applyShiftForm) {
        applyShiftForm.addEventListener("submit", async function(event) {
            event.preventDefault();
            event.stopPropagation();

            const formData = new FormData(applyShiftForm);
            const data = Object.fromEntries(formData.entries());

            const submitButton = applyShiftForm.querySelector("button[type=\"submit\"]");
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = 
            submitButton.disabled = true;

            try {
                const response = await fetchData("<?php echo base_url("shiftapplication/apply"); ?>", {
                    method: "POST",
                    body: JSON.stringify(data)
                });

                if (response.status === "success") {
                    displayMessage("success", response.message || "Successfully applied for the shift!", "messageContainerGlobal");
                    // Disable form or change UI to reflect application
                    submitButton.textContent = "Applied Successfully";
                    submitButton.classList.remove("btn-primary");
                    submitButton.classList.add("btn-success");
                    applyShiftForm.querySelector("textarea").disabled = true;
                    // Optionally redirect or update other parts of the page
                } else {
                    displayMessage("danger", response.message || "Failed to apply for the shift.", "messageContainerGlobal");
                    submitButton.innerHTML = originalButtonText;
                    submitButton.disabled = false;
                }
            } catch (error) {
                displayMessage("danger", error.message || "An unexpected error occurred.", "messageContainerGlobal");
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
