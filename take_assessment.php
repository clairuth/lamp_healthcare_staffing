<?php
// templates/skills/take_assessment.php

$page_title = "Take Skill Assessment - StaffingPlus";
require_once APP_ROOT . "/templates/header.php";

if (!is_logged_in() || get_current_user_type() !== "professional") {
    display_error_message("Access Denied: You must be logged in as a professional to take assessments.");
    require_once APP_ROOT . "/templates/footer.php";
    exit;
}

// The assessment_id should be passed as a parameter in the URL, e.g., /skillassessment/take/5
// This is handled by the router in public/index.php, which calls the SkillAssessmentController::take($assessment_id)
// The controller should fetch assessment_details (title, skill_name) and questions (without answers) and make them available.

if (!isset($assessment_details) || empty($assessment_details) || !isset($assessment_questions) || empty($assessment_questions)) {
    echo "<div class=\"container mt-4\"><div class=\"alert alert-danger\">Assessment details or questions not found. It might be invalid or you may have already completed it.</div></div>";
    require_once APP_ROOT . "/templates/footer.php";
    exit;
}

$professional_user_id = get_current_user_id();

?>

<div class="container mt-4">
    <h1 class="mb-1"><?php echo htmlspecialchars($assessment_details["assessment_title"]); ?></h1>
    <p class="lead mb-4">Skill: <?php echo htmlspecialchars($assessment_details["skill_name"]); ?></p>

    <div id="messageContainerGlobal"></div>

    <form id="skillAssessmentForm">
        <input type="hidden" name="assessment_id" value="<?php echo htmlspecialchars($assessment_details["id"]); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">

        <?php foreach ($assessment_questions as $index => $question): ?>
            <div class="card assessment-question-card mb-4">
                <div class="card-header">
                    Question <?php echo $index + 1; ?>
                </div>
                <div class="card-body">
                    <p class="card-text question-text"><?php echo htmlspecialchars($question["question_text"]); ?></p>
                    <div class="options-group">
                        <?php 
                        // Options should be an array. If it"s a JSON string, decode it.
                        $options = is_string($question["options"]) ? json_decode($question["options"], true) : $question["options"];
                        if (is_array($options)): 
                            foreach ($options as $opt_key => $opt_value): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="answers[<?php echo $question["id"]; ?>]" id="q<?php echo $question["id"]; ?>_opt<?php echo $opt_key; ?>" value="<?php echo htmlspecialchars($opt_key); ?>" required>
                                    <label class="form-check-label" for="q<?php echo $question["id"]; ?>_opt<?php echo $opt_key; ?>">
                                        <?php echo htmlspecialchars($opt_value); ?>
                                    </label>
                                </div>
                        <?php 
                            endforeach; 
                        else: ?>
                            <p class=\"text-danger\">Error: Options not available for this question.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-primary btn-lg btn-full-width mt-3">Submit Assessment</button>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const skillAssessmentForm = document.getElementById("skillAssessmentForm");

    if (skillAssessmentForm) {
        skillAssessmentForm.addEventListener("submit", async function(event) {
            event.preventDefault();
            event.stopPropagation();

            // Basic validation: ensure all questions are answered
            const questionsCount = <?php echo count($assessment_questions); ?>;
            const answeredQuestions = new Set();
            const radioButtons = skillAssessmentForm.querySelectorAll("input[type=radio]:checked");
            radioButtons.forEach(rb => answeredQuestions.add(rb.name));

            if (answeredQuestions.size < questionsCount) {
                displayMessage("danger", "Please answer all questions before submitting.", "messageContainerGlobal");
                return;
            }

            const formData = new FormData(skillAssessmentForm);
            const data = {};
            data.assessment_id = formData.get("assessment_id");
            data.csrf_token = formData.get("csrf_token");
            data.answers = {};
            formData.forEach((value, key) => {
                if (key.startsWith("answers[")) {
                    const questionId = key.match(/\[(\d+)\]/)[1];
                    data.answers[questionId] = value;
                }
            });

            const submitButton = skillAssessmentForm.querySelector("button[type=\"submit\"]");
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = 
            submitButton.disabled = true;

            try {
                const response = await fetchData("<?php echo base_url("skillassessment/submit"); ?>", {
                    method: "POST",
                    body: JSON.stringify(data)
                });

                if (response.status === "success" && response.data) {
                    const resultData = response.data;
                    let resultMessage = `Assessment Submitted! Your score: ${resultData.score_percentage}% (${resultData.correct_answers}/${resultData.total_questions}). Status: ${resultData.passed ? "Passed" : "Failed"}.`;
                    if (resultData.message) resultMessage += " " + resultData.message;
                    
                    displayMessage(resultData.passed ? "success" : "warning", resultMessage, "messageContainerGlobal");
                    
                    // Optionally, disable the form or redirect
                    skillAssessmentForm.innerHTML = `<div class="alert alert-${resultData.passed ? "success" : "warning"}">${resultMessage}</div><p class="text-center mt-3"><a href="<?php echo base_url("skill/mySkills"); ?>" class="btn btn-info">Back to My Skills</a></p>`;
                } else {
                    displayMessage("danger", response.message || "Failed to submit assessment. Please try again.", "messageContainerGlobal");
                    submitButton.innerHTML = originalButtonText;
                    submitButton.disabled = false;
                }
            } catch (error) {
                displayMessage("danger", error.message || "An unexpected error occurred during submission.", "messageContainerGlobal");
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
