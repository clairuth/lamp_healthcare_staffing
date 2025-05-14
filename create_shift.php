<?php
// templates/shifts/create_shift.php

$page_title = "Post New Shift - StaffingPlus";
require_once APP_ROOT . "/templates/header.php";

if (!is_logged_in() || get_current_user_type() !== "facility") {
    display_error_message("Access Denied: You must be logged in as a facility to post shifts.");
    require_once APP_ROOT . "/templates/footer.php";
    exit;
}

$facility_id = get_current_user_id(); // Assuming facility user ID is the facility_id for shifts

// Fetch required skills list for the dropdown - this should be handled by the controller or a helper
// For now, let's assume $all_skills is populated by the controller ShiftController::create()
if (!isset($all_skills)) {
    // Fallback or error if skills aren't loaded - in a real app, controller handles this
    // For the template, we can simulate or show a message.
    // $all_skills = []; // Simulate empty or fetch here if absolutely necessary (not ideal)
    // For this example, we will assume the controller provides it.
    // If not, the form might not render correctly.
}

?>

<div class="container mt-4">
    <h1>Post a New Shift</h1>

    <div id="messageContainerGlobal"></div>

    <form id="createShiftForm" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
        <input type="hidden" name="facility_id" value="<?php echo htmlspecialchars($facility_id); ?>">

        <div class="card mb-4">
            <div class="card-header">Shift Details</div>
            <div class="card-body">
                <div class="form-group">
                    <label for="title">Shift Title / Position</label>
                    <input type="text" class="form-control" id="title" name="title" placeholder="e.g., RN - ER Night Shift" required>
                    <div class="invalid-feedback">Please provide a title for the shift.</div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" placeholder="Detailed description of duties, patient ratios, etc." required></textarea>
                    <div class="invalid-feedback">Please provide a description.</div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="start_time">Start Date & Time</label>
                        <input type="datetime-local" class="form-control" id="start_time" name="start_time" required>
                        <div class="invalid-feedback">Please specify the start date and time.</div>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="end_time">End Date & Time</label>
                        <input type="datetime-local" class="form-control" id="end_time" name="end_time" required>
                        <div class="invalid-feedback">Please specify the end date and time.</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="pay_rate">Pay Rate (e.g., per hour, per shift)</label>
                    <input type="text" class="form-control" id="pay_rate" name="pay_rate" placeholder="e.g., $55/hour or $600/shift" required>
                    <div class="invalid-feedback">Please specify the pay rate.</div>
                </div>

                <div class="form-group">
                    <label for="required_skills">Required Skills (select multiple if needed)</label>
                    <select multiple class="form-control" id="required_skills" name="required_skills[]" size="5">
                        <?php 
                        if (isset($all_skills) && !empty($all_skills)):
                            foreach ($all_skills as $skill):
                        ?>
                            <option value="<?php echo htmlspecialchars($skill["id"]); ?>"><?php echo htmlspecialchars($skill["skill_name"]); ?></option>
                        <?php 
                            endforeach;
                        else:
                        ?>
                            <option value="" disabled>No skills available to select. Please contact admin.</option>
                        <?php endif; ?>
                    </select>
                    <small class="form-text text-muted">Hold Ctrl (or Cmd on Mac) to select multiple skills.</small>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="open" selected>Open (Visible to Professionals)</option>
                        <option value="draft">Draft (Save for later, not visible)</option>
                    </select>
                </div>

            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg">Post Shift</button>
        <a href="<?php echo base_url("shift/facilityShifts"); ?>" class="btn btn-secondary btn-lg">Cancel</a>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const createShiftForm = document.getElementById("createShiftForm");

    // Set min attribute for datetime-local inputs to prevent past dates
    const now = new Date();
    const year = now.getFullYear();
    const month = (now.getMonth() + 1).toString().padStart(2, "0");
    const day = now.getDate().toString().padStart(2, "0");
    const hours = now.getHours().toString().padStart(2, "0");
    const minutes = now.getMinutes().toString().padStart(2, "0");
    const currentDateTimeLocal = `${year}-${month}-${day}T${hours}:${minutes}`;

    const startTimeInput = document.getElementById("start_time");
    const endTimeInput = document.getElementById("end_time");

    if (startTimeInput) {
        startTimeInput.min = currentDateTimeLocal;
        startTimeInput.addEventListener("change", function() {
            if (endTimeInput.value && endTimeInput.value < startTimeInput.value) {
                endTimeInput.value = startTimeInput.value;
            }
            endTimeInput.min = startTimeInput.value;
        });
    }
    if (endTimeInput) {
        endTimeInput.min = currentDateTimeLocal;
    }

    if (createShiftForm) {
        createShiftForm.addEventListener("submit", async function(event) {
            event.preventDefault();
            event.stopPropagation();

            if (!createShiftForm.checkValidity()) {
                createShiftForm.classList.add("was-validated");
                displayMessage("danger", "Please fill in all required fields correctly.", "messageContainerGlobal");
                return;
            }
            
            // Validate start and end times logically
            if (startTimeInput.value >= endTimeInput.value) {
                displayMessage("danger", "End time must be after start time.", "messageContainerGlobal");
                endTimeInput.classList.add("is-invalid");
                return;
            }
            endTimeInput.classList.remove("is-invalid");

            const formData = new FormData(createShiftForm);
            const data = {};
            formData.forEach((value, key) => {
                if (key.endsWith("[]")) { // Handle multi-select for skills
                    if (!data[key.slice(0, -2)]) {
                        data[key.slice(0, -2)] = [];
                    }
                    data[key.slice(0, -2)].push(value);
                } else {
                    data[key] = value;
                }
            });

            const submitButton = createShiftForm.querySelector("button[type=\"submit\"]");
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = 
            submitButton.disabled = true;

            try {
                const response = await fetchData("<?php echo base_url("shift/store"); ?>", {
                    method: "POST",
                    body: JSON.stringify(data)
                });

                if (response.status === "success") {
                    displayMessage("success", response.message || "Shift posted successfully!", "messageContainerGlobal");
                    createShiftForm.reset();
                    createShiftForm.classList.remove("was-validated");
                    // Optionally redirect to facility shifts page
                    setTimeout(() => {
                        window.location.href = "<?php echo base_url("shift/facilityShifts"); ?>";
                    }, 1500);
                } else {
                    displayMessage("danger", response.message || "Failed to post shift. Please try again.", "messageContainerGlobal");
                }
            } catch (error) {
                displayMessage("danger", error.message || "An unexpected error occurred.", "messageContainerGlobal");
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
