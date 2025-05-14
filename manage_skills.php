<?php
// templates/admin/manage_skills.php

$page_title = "Manage Skills - Admin - StaffingPlus";
require_once APP_ROOT . "/templates/header.php";

if (!is_logged_in() || get_current_user_type() !== "admin") {
    display_error_message("Access Denied: You must be logged in as an administrator.");
    require_once APP_ROOT . "/templates/footer.php";
    exit;
}

?>

<div class="container-fluid mt-4 admin-management-page">
    <h1 class="mb-4">Manage Healthcare Skills</h1>

    <div id="messageContainerGlobal"></div>

    <!-- Add New Skill Form -->
    <div class="card mb-4">
        <div class="card-header">Add New Skill</div>
        <div class="card-body">
            <form id="addSkillForm" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                <div class="form-row">
                    <div class="col-md-5 mb-3">
                        <label for="skill_name">Skill Name</label>
                        <input type="text" class="form-control" id="skill_name" name="skill_name" placeholder="e.g., Phlebotomy, ACLS Certified" required>
                        <div class="invalid-feedback">Please provide a skill name.</div>
                    </div>
                    <div class="col-md-5 mb-3">
                        <label for="skill_category">Category (Optional)</label>
                        <input type="text" class="form-control" id="skill_category" name="skill_category" placeholder="e.g., Clinical, Technical, Certification">
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button class="btn btn-primary btn-block" type="submit">Add Skill</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Skills List Table -->
    <div class="card">
        <div class="card-header">
            Existing Skills
        </div>
        <div class="card-body">
            <div id="skillsTableContainer" class="table-responsive">
                <div class="loader-container text-center">
                    <div class="loader"></div>
                    <p>Loading skills...</p>
                </div>
                <!-- Skills table will be loaded here by JavaScript -->
            </div>
        </div>
    </div>
</div>

<!-- Edit Skill Modal -->
<div class="modal fade" id="editSkillModal" tabindex="-1" aria-labelledby="editSkillModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSkillModalLabel">Edit Skill</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editSkillFormModal" class="needs-validation" novalidate>
                    <input type="hidden" id="edit_skill_id" name="skill_id">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                    <div class="form-group">
                        <label for="edit_skill_name">Skill Name</label>
                        <input type="text" class="form-control" id="edit_skill_name" name="skill_name" required>
                        <div class="invalid-feedback">Please provide a skill name.</div>
                    </div>
                    <div class="form-group">
                        <label for="edit_skill_category">Category (Optional)</label>
                        <input type="text" class="form-control" id="edit_skill_category" name="skill_category">
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const skillsTableContainer = document.getElementById("skillsTableContainer");
    const addSkillForm = document.getElementById("addSkillForm");
    const editSkillFormModal = document.getElementById("editSkillFormModal");
    const editSkillModal = new bootstrap.Modal(document.getElementById("editSkillModal")); // Bootstrap 5 modal instance

    async function loadSkills() {
        skillsTableContainer.innerHTML = `<div class="loader-container text-center"><div class="loader"></div><p>Loading skills...</p></div>`;
        try {
            const response = await fetchData("<?php echo base_url("admin/skills/listAPI"); ?>");
            skillsTableContainer.innerHTML = ""; // Clear loader

            if (response.status === "success" && response.data && response.data.length > 0) {
                const table = document.createElement("table");
                table.className = "table table-hover table-striped admin-table";
                table.innerHTML = `
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Skill Name</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                `;
                const tbody = table.querySelector("tbody");
                response.data.forEach(skill => {
                    const tr = document.createElement("tr");
                    tr.innerHTML = `
                        <td>${skill.id}</td>
                        <td>${sanitizeHTML(skill.skill_name)}</td>
                        <td>${sanitizeHTML(skill.category || "N/A")}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-secondary edit-skill-btn" data-id="${skill.id}" data-name="${sanitizeHTML(skill.skill_name)}" data-category="${sanitizeHTML(skill.category || "")}" title="Edit Skill"><i class="fas fa-edit"></i> Edit</button>
                            <button class="btn btn-sm btn-outline-danger delete-skill-btn ml-1" data-id="${skill.id}" title="Delete Skill"><i class="fas fa-trash-alt"></i> Delete</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
                skillsTableContainer.appendChild(table);
                addEventListenersForSkillActions();
            } else if (response.status === "success" && response.data && response.data.length === 0) {
                 skillsTableContainer.innerHTML = "<p>No skills found. Add some using the form above.</p>";
            } else {
                skillsTableContainer.innerHTML = `<p class="text-danger">Could not load skills. ${response.message || "Please try again later."}</p>`;
            }
        } catch (error) {
            console.error("Error fetching skills:", error);
            skillsTableContainer.innerHTML = "<p class=\"text-danger\">Could not load skills. Please try again later.</p>";
        }
    }

    function addEventListenersForSkillActions() {
        document.querySelectorAll(".edit-skill-btn").forEach(button => {
            button.addEventListener("click", function() {
                document.getElementById("edit_skill_id").value = this.dataset.id;
                document.getElementById("edit_skill_name").value = this.dataset.name;
                document.getElementById("edit_skill_category").value = this.dataset.category;
                editSkillModal.show();
            });
        });

        document.querySelectorAll(".delete-skill-btn").forEach(button => {
            button.addEventListener("click", async function() {
                const skillId = this.dataset.id;
                if (confirm("Are you sure you want to delete this skill? This might affect existing assessments or professional profiles.")) {
                    try {
                        const response = await fetchData(`<?php echo base_url("admin/skills/delete/"); ?>${skillId}`, {
                            method: "POST",
                            body: JSON.stringify({ csrf_token: "<?php echo htmlspecialchars(generate_csrf_token()); ?>" })
                        });
                        if (response.status === "success") {
                            displayMessage("success", response.message || "Skill deleted successfully.", "messageContainerGlobal");
                            loadSkills(); // Refresh list
                        } else {
                            displayMessage("danger", response.message || "Failed to delete skill.", "messageContainerGlobal");
                        }
                    } catch (error) {
                        displayMessage("danger", error.message || "Error deleting skill.", "messageContainerGlobal");
                    }
                }
            });
        });
    }

    if (addSkillForm) {
        addSkillForm.addEventListener("submit", async function(event) {
            event.preventDefault();
            event.stopPropagation();
            if (!addSkillForm.checkValidity()) {
                addSkillForm.classList.add("was-validated");
                return;
            }
            const formData = new FormData(addSkillForm);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetchData("<?php echo base_url("admin/skills/add"); ?>", {
                    method: "POST",
                    body: JSON.stringify(data)
                });
                if (response.status === "success") {
                    displayMessage("success", response.message || "Skill added successfully!", "messageContainerGlobal");
                    addSkillForm.reset();
                    addSkillForm.classList.remove("was-validated");
                    loadSkills(); // Refresh list
                } else {
                    displayMessage("danger", response.message || "Failed to add skill.", "messageContainerGlobal");
                }
            } catch (error) {
                displayMessage("danger", error.message || "Error adding skill.", "messageContainerGlobal");
            }
        });
    }

    if (editSkillFormModal) {
        editSkillFormModal.addEventListener("submit", async function(event) {
            event.preventDefault();
            event.stopPropagation();
            if (!editSkillFormModal.checkValidity()) {
                editSkillFormModal.classList.add("was-validated");
                return;
            }
            const formData = new FormData(editSkillFormModal);
            const data = Object.fromEntries(formData.entries());
            const skillId = data.skill_id;

            try {
                const response = await fetchData(`<?php echo base_url("admin/skills/update/"); ?>${skillId}`, {
                    method: "POST",
                    body: JSON.stringify(data)
                });
                if (response.status === "success") {
                    displayMessage("success", response.message || "Skill updated successfully!", "messageContainerGlobal");
                    editSkillModal.hide();
                    loadSkills(); // Refresh list
                } else {
                    // Display error within modal or globally
                    displayMessage("danger", response.message || "Failed to update skill.", "messageContainerGlobal"); 
                }
            } catch (error) {
                displayMessage("danger", error.message || "Error updating skill.", "messageContainerGlobal");
            }
        });
    }

    // Initial load
    loadSkills();
});
</script>

<?php
require_once APP_ROOT . "/templates/footer.php";
?>
