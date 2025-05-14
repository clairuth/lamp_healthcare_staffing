<?php
// templates/admin/manage_assessments.php

$page_title = "Manage Skill Assessments - Admin - StaffingPlus";
require_once APP_ROOT . "/templates/header.php";

if (!is_logged_in() || get_current_user_type() !== "admin") {
    display_error_message("Access Denied: You must be logged in as an administrator.");
    require_once APP_ROOT . "/templates/footer.php";
    exit;
}

// Fetch skills for the dropdown - this should be handled by the controller or a helper
// For now, let's assume $all_skills is populated by the controller AdminController::manageAssessments()
if (!isset($all_skills)) {
    // $all_skills = []; // Simulate or fetch if necessary (not ideal for template)
}

?>

<div class="container-fluid mt-4 admin-management-page">
    <h1 class="mb-4">Manage Skill Assessments</h1>

    <div id="messageContainerGlobal"></div>

    <!-- Add New Assessment Form -->
    <div class="card mb-4">
        <div class="card-header">Create New Skill Assessment</div>
        <div class="card-body">
            <form id="addAssessmentForm" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                <div class="form-group">
                    <label for="assessment_title">Assessment Title</label>
                    <input type="text" class="form-control" id="assessment_title" name="title" placeholder="e.g., Basic Phlebotomy Proficiency Test" required>
                    <div class="invalid-feedback">Please provide a title for the assessment.</div>
                </div>
                <div class="form-group">
                    <label for="assessment_skill_id">Associated Skill</label>
                    <select class="form-control" id="assessment_skill_id" name="skill_id" required>
                        <option value="">Select a Skill...</option>
                        <?php 
                        if (isset($all_skills) && !empty($all_skills)):
                            foreach ($all_skills as $skill):
                        ?>
                            <option value="<?php echo htmlspecialchars($skill["id"]); ?>"><?php echo htmlspecialchars($skill["skill_name"]); ?></option>
                        <?php 
                            endforeach;
                        else:
                        ?>
                            <option value="" disabled>No skills available. Add skills first.</option>
                        <?php endif; ?>
                    </select>
                    <div class="invalid-feedback">Please select an associated skill.</div>
                </div>
                <div class="form-group">
                    <label for="assessment_description">Description (Optional)</label>
                    <textarea class="form-control" id="assessment_description" name="description" rows="2" placeholder="Brief overview of the assessment"></textarea>
                </div>
                <div class="form-group">
                    <label for="passing_score">Passing Score (%)</label>
                    <input type="number" class="form-control" id="passing_score" name="passing_score" min="1" max="100" placeholder="e.g., 75" required>
                    <div class="invalid-feedback">Please set a passing score between 1 and 100.</div>
                </div>
                
                <hr>
                <h5>Assessment Questions</h5>
                <div id="questionsContainer">
                    <!-- Question 1 (template) -->
                    <div class="question-block card mb-3 p-3">
                        <div class="form-group">
                            <label>Question 1 Text</label>
                            <textarea class="form-control question-text" name="questions[0][text]" rows="2" placeholder="Enter question text" required></textarea>
                            <div class="invalid-feedback">Question text cannot be empty.</div>
                        </div>
                        <div class="options-container ml-3">
                            <label>Options (Mark correct answer)</label>
                            <div class="input-group mb-2 option-item">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <input type="radio" name="questions[0][correct_option]" value="0" required>
                                    </div>
                                </div>
                                <input type="text" class="form-control option-text" name="questions[0][options][0]" placeholder="Option A" required>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-option-btn">Remove</button>
                                </div>
                                <div class="invalid-feedback">Option text cannot be empty.</div>
                            </div>
                            <!-- Add more options here -->
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary add-option-btn mt-2">Add Option</button>
                        <button type="button" class="btn btn-sm btn-danger remove-question-btn mt-2 float-right">Remove Question</button>
                    </div>
                </div>
                <button type="button" id="addQuestionBtn" class="btn btn-info mt-2 mb-3">Add Another Question</button>
                <hr>

                <button class="btn btn-primary" type="submit">Create Assessment</button>
            </form>
        </div>
    </div>

    <!-- Existing Assessments List -->
    <div class="card">
        <div class="card-header">Existing Skill Assessments</div>
        <div class="card-body">
            <div id="assessmentsTableContainer" class="table-responsive">
                <div class="loader-container text-center">
                    <div class="loader"></div>
                    <p>Loading assessments...</p>
                </div>
                <!-- Assessments table will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Edit Assessment Modal (Simplified for brevity - full edit would be complex) -->
<div class="modal fade" id="editAssessmentModal" tabindex="-1" aria-labelledby="editAssessmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAssessmentModalLabel">Edit Assessment Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="editAssessmentFormModal" class="needs-validation" novalidate>
                    <input type="hidden" id="edit_assessment_id" name="assessment_id">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                    <div class="form-group">
                        <label for="edit_assessment_title">Assessment Title</label>
                        <input type="text" class="form-control" id="edit_assessment_title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_assessment_skill_id">Associated Skill</label>
                        <select class="form-control" id="edit_assessment_skill_id" name="skill_id" required>
                            <!-- Options populated by JS or passed from controller -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_assessment_description">Description</label>
                        <textarea class="form-control" id="edit_assessment_description" name="description" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_passing_score">Passing Score (%)</label>
                        <input type="number" class="form-control" id="edit_passing_score" name="passing_score" min="1" max="100" required>
                    </div>
                    <p class="text-muted"><small>Note: Editing questions and options for existing assessments is typically handled in a more detailed interface. This modal is for basic details.</small></p>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const assessmentsTableContainer = document.getElementById("assessmentsTableContainer");
    const addAssessmentForm = document.getElementById("addAssessmentForm");
    const questionsContainer = document.getElementById("questionsContainer");
    const addQuestionBtn = document.getElementById("addQuestionBtn");
    let questionCounter = 1;

    const editAssessmentFormModal = document.getElementById("editAssessmentFormModal");
    const editAssessmentModal = new bootstrap.Modal(document.getElementById("editAssessmentModal"));
    const editAssessmentSkillIdSelect = document.getElementById("edit_assessment_skill_id");

    // Populate skills for edit modal (assuming $all_skills is available globally or fetched)
    function populateEditSkillsDropdown(skills) {
        editAssessmentSkillIdSelect.innerHTML = 
        if (skills && skills.length > 0) {
            skills.forEach(skill => {
                const option = document.createElement("option");
                option.value = skill.id;
                option.textContent = sanitizeHTML(skill.skill_name);
                editAssessmentSkillIdSelect.appendChild(option);
            });
        } else {
            editAssessmentSkillIdSelect.innerHTML = ";
        }
    }
    // Call this if $all_skills is available from PHP, or fetch them via API
    <?php if(isset($all_skills) && !empty($all_skills)): ?>
    populateEditSkillsDropdown(<?php echo json_encode($all_skills); ?>);
    <?php else: ?>
    // fetchData(base_url("admin/skills/listAPI")).then(res => { if(res.status === "success") populateEditSkillsDropdown(res.data); });
    <?php endif; ?>

    function createQuestionBlock(index) {
        const questionBlock = document.createElement("div");
        questionBlock.className = "question-block card mb-3 p-3";
        questionBlock.innerHTML = `
            <div class="form-group">
                <label>Question ${index + 1} Text</label>
                <textarea class="form-control question-text" name="questions[${index}][text]" rows="2" placeholder="Enter question text" required></textarea>
                <div class="invalid-feedback">Question text cannot be empty.</div>
            </div>
            <div class="options-container ml-3">
                <label>Options (Mark correct answer)</label>
                <!-- Initial Option -->
                <div class="input-group mb-2 option-item">
                    <div class="input-group-prepend"><div class="input-group-text"><input type="radio" name="questions[${index}][correct_option]" value="0" required></div></div>
                    <input type="text" class="form-control option-text" name="questions[${index}][options][0]" placeholder="Option A" required>
                    <div class="input-group-append"><button type="button" class="btn btn-sm btn-outline-danger remove-option-btn">Remove</button></div>
                    <div class="invalid-feedback">Option text cannot be empty.</div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary add-option-btn mt-2">Add Option</button>
            <button type="button" class="btn btn-sm btn-danger remove-question-btn mt-2 float-right">Remove Question</button>
        `;
        return questionBlock;
    }

    function addOptionToQuestion(optionsContainer, questionIndex) {
        const optionIndex = optionsContainer.querySelectorAll(".option-item").length;
        const optionItem = document.createElement("div");
        optionItem.className = "input-group mb-2 option-item";
        optionItem.innerHTML = `
            <div class="input-group-prepend"><div class="input-group-text"><input type="radio" name="questions[${questionIndex}][correct_option]" value="${optionIndex}" required></div></div>
            <input type="text" class="form-control option-text" name="questions[${questionIndex}][options][${optionIndex}]" placeholder="Option ${String.fromCharCode(65 + optionIndex)}" required>
            <div class="input-group-append"><button type="button" class="btn btn-sm btn-outline-danger remove-option-btn">Remove</button></div>
            <div class="invalid-feedback">Option text cannot be empty.</div>
        `;
        optionsContainer.appendChild(optionItem);
        optionItem.querySelector(".remove-option-btn").addEventListener("click", function() { this.closest(".option-item").remove(); });
    }

    if (addQuestionBtn) {
        addQuestionBtn.addEventListener("click", function() {
            const newBlock = createQuestionBlock(questionCounter);
            questionsContainer.appendChild(newBlock);
            // Add event listeners for new block
            newBlock.querySelector(".add-option-btn").addEventListener("click", function() {
                addOptionToQuestion(this.previousElementSibling, questionCounter -1); // This logic might need refinement for correct index
            });
            newBlock.querySelector(".remove-question-btn").addEventListener("click", function() { this.closest(".question-block").remove(); });
            newBlock.querySelectorAll(".remove-option-btn").forEach(btn => btn.addEventListener("click", function() { this.closest(".option-item").remove(); }));
            questionCounter++;
        });
    }

    // Initial question block event listeners
    questionsContainer.querySelectorAll(".question-block").forEach((block, qIndex) => {
        block.querySelector(".add-option-btn").addEventListener("click", function() {
            addOptionToQuestion(this.previousElementSibling, qIndex);
        });
        block.querySelector(".remove-question-btn").addEventListener("click", function() { this.closest(".question-block").remove(); });
        block.querySelectorAll(".remove-option-btn").forEach(btn => btn.addEventListener("click", function() { this.closest(".option-item").remove(); }));
    });

    async function loadAssessments() {
        assessmentsTableContainer.innerHTML = `<div class="loader-container text-center"><div class="loader"></div><p>Loading assessments...</p></div>`;
        try {
            const response = await fetchData("<?php echo base_url("admin/assessments/listAPI"); ?>");
            assessmentsTableContainer.innerHTML = ""; // Clear loader

            if (response.status === "success" && response.data && response.data.length > 0) {
                const table = document.createElement("table");
                table.className = "table table-hover table-striped admin-table";
                table.innerHTML = `
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Skill</th>
                            <th>Questions</th>
                            <th>Passing Score</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                `;
                const tbody = table.querySelector("tbody");
                response.data.forEach(assessment => {
                    const tr = document.createElement("tr"
(Content truncated due to size limit. Use line ranges to read in chunks)