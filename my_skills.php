<?php
// templates/skills/my_skills.php

$page_title = "My Skills & Assessments - StaffingPlus";
require_once APP_ROOT . "/templates/header.php";

if (!is_logged_in() || get_current_user_type() !== "professional") {
    display_error_message("Access Denied: You must be logged in as a professional.");
    require_once APP_ROOT . "/templates/footer.php";
    exit;
}

$professional_user_id = get_current_user_id();

?>

<div class="container mt-4">
    <h1 class="mb-4">My Skills & Available Assessments</h1>

    <div id="messageContainerGlobal"></div>

    <div class="row">
        <div class="col-md-6">
            <h3 class="mb-3">My Current Skills</h3>
            <div id="mySkillsContainer">
                <div class="loader-container text-center">
                    <div class="loader"></div>
                    <p>Loading your skills...</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <h3 class="mb-3">Available Skill Assessments</h3>
            <div id="availableAssessmentsContainer">
                <div class="loader-container text-center">
                    <div class="loader"></div>
                    <p>Loading available assessments...</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <a href="<?php echo base_url("skillassessment/listProfessionalAttempts"); ?>" class="btn btn-info">View My Assessment History</a>
    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const mySkillsContainer = document.getElementById("mySkillsContainer");
    const availableAssessmentsContainer = document.getElementById("availableAssessmentsContainer");

    async function loadMySkills() {
        mySkillsContainer.innerHTML = `<div class="loader-container text-center"><div class="loader"></div><p>Loading your skills...</p></div>`;
        try {
            const response = await fetchData("<?php echo base_url("skill/listProfessionalSkills"); ?>");
            mySkillsContainer.innerHTML = ""; // Clear loader

            if (response.status === "success" && response.data && response.data.length > 0) {
                const listGroup = document.createElement("ul");
                listGroup.className = "list-group";
                response.data.forEach(skill => {
                    const listItem = document.createElement("li");
                    listItem.className = "list-group-item d-flex justify-content-between align-items-center";
                    listItem.innerHTML = `
                        ${sanitizeHTML(skill.skill_name)}
                        <span class="badge bg-${getSkillLevelClass(skill.skill_level)} rounded-pill">${sanitizeHTML(skill.skill_level || "Not Assessed")}</span>
                    `;
                    listGroup.appendChild(listItem);
                });
                mySkillsContainer.appendChild(listGroup);
            } else {
                mySkillsContainer.innerHTML = "<p>You have not added or been assessed for any skills yet.</p>";
            }
        } catch (error) {
            console.error("Error fetching your skills:", error);
            mySkillsContainer.innerHTML = "<p class=\"text-danger\">Could not load your skills. Please try again later.</p>";
        }
    }

    async function loadAvailableAssessments() {
        availableAssessmentsContainer.innerHTML = `<div class="loader-container text-center"><div class="loader"></div><p>Loading available assessments...</p></div>`;
        try {
            // This endpoint should list assessments the user hasn"t passed or has an expired pass for.
            const response = await fetchData("<?php echo base_url("skillassessment/listAvailableForProfessional"); ?>");
            availableAssessmentsContainer.innerHTML = ""; // Clear loader

            if (response.status === "success" && response.data && response.data.length > 0) {
                const listGroup = document.createElement("ul");
                listGroup.className = "list-group";
                response.data.forEach(assessment => {
                    const listItem = document.createElement("li");
                    listItem.className = "list-group-item d-flex justify-content-between align-items-center";
                    listItem.innerHTML = `
                        <span>
                            <strong>${sanitizeHTML(assessment.assessment_title)}</strong><br>
                            <small class=\"text-muted\">Related Skill: ${sanitizeHTML(assessment.skill_name)}</small>
                        </span>
                        <a href="<?php echo base_url("skillassessment/take/"); ?>${assessment.id}" class="btn btn-sm btn-success">Take Assessment</a>
                    `;
                    listGroup.appendChild(listItem);
                });
                availableAssessmentsContainer.appendChild(listGroup);
            } else {
                availableAssessmentsContainer.innerHTML = "<p>No new skill assessments currently available for you.</p>";
            }
        } catch (error) {
            console.error("Error fetching available assessments:", error);
            availableAssessmentsContainer.innerHTML = "<p class=\"text-danger\">Could not load available assessments. Please try again later.</p>";
        }
    }
    
    function getSkillLevelClass(level) {
        if (!level) return "secondary";
        switch (level.toLowerCase()) {
            case "expert": return "primary";
            case "advanced": return "success";
            case "intermediate": return "info";
            case "beginner": return "warning";
            default: return "secondary";
        }
    }

    function sanitizeHTML(str) {
        if (!str) return "";
        const temp = document.createElement("div");
        temp.textContent = str;
        return temp.innerHTML;
    }

    // Initial loads
    loadMySkills();
    loadAvailableAssessments();
});
</script>

<?php
require_once APP_ROOT . "/templates/footer.php";
?>
