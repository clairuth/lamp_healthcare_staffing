// assets/js/skill_assessment.js

document.addEventListener("DOMContentLoaded", function() {
    // For my_skills.php - to display skills and assessment results
    const mySkillsContainer = document.getElementById("mySkillsContainer");
    if (mySkillsContainer) {
        loadMySkillsAndAssessments();
    }

    // For take_assessment.php - handle assessment form submission
    const takeAssessmentForm = document.getElementById("takeAssessmentForm");
    if (takeAssessmentForm) {
        // Dynamically load assessment questions if not server-rendered
        const assessmentId = takeAssessmentForm.dataset.assessmentId;
        if (assessmentId) {
            loadAssessmentQuestions(assessmentId);
        }

        takeAssessmentForm.addEventListener("submit", async function(event) {
            event.preventDefault();
            event.stopPropagation();

            if (!takeAssessmentForm.checkValidity()) {
                // HTML5 validation will show messages for required radio buttons
                displayMessage("danger", "Please answer all questions before submitting.", "messageContainerAssessment");
                return;
            }

            const formData = new FormData(takeAssessmentForm);
            const submitButton = takeAssessmentForm.querySelector("button[type=\"submit\"]");
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...`;
            submitButton.disabled = true;

            try {
                const response = await fetchData(base_url("assessment/submit/") + assessmentId, {
                    method: "POST",
                    body: formData
                });

                if (response.status === "success") {
                    let resultMessage = `<h3>Assessment Submitted!</h3>`;
                    resultMessage += `<p>Your score: <strong>${response.data.score_achieved}%</strong></p>`;
                    if (response.data.passed) {
                        resultMessage += `<p class="text-success">Congratulations! You passed this assessment.</p>`;
                    } else {
                        resultMessage += `<p class="text-danger">Unfortunately, you did not pass this assessment. Required: ${response.data.passing_score}%</p>`;
                    }
                    if(response.data.skill_name && response.data.new_rank){
                        resultMessage += `<p>Your rank for skill '${sanitizeHTML(response.data.skill_name)}' is now: <strong>${sanitizeHTML(response.data.new_rank)}</strong></p>`;
                    }
                    resultMessage += `<a href="${base_url("skills/my")}" class="btn btn-primary mt-2">View My Skills</a>`;
                    document.getElementById("assessmentQuestionsContainer").innerHTML = resultMessage;
                    takeAssessmentForm.style.display = "none"; // Hide form
                    displayMessage("success", "Assessment submitted successfully.", "messageContainerAssessment");
                } else {
                    displayMessage("danger", response.message || "Failed to submit assessment.", "messageContainerAssessment");
                }
            } catch (error) {
                displayMessage("danger", error.message || "An unexpected error occurred.", "messageContainerAssessment");
            } finally {
                submitButton.innerHTML = originalButtonText;
                // Keep disabled if successful, or re-enable if error and retry is desired
                if(takeAssessmentForm.style.display !== "none") {
                    submitButton.disabled = false;
                }
            }
        });
    }
});

async function loadAssessmentQuestions(assessmentId) {
    const container = document.getElementById("assessmentQuestionsContainer");
    if (!container) return;
    container.innerHTML = `<div class="loader-container text-center"><div class="loader"></div><p>Loading assessment questions...</p></div>`;

    try {
        const response = await fetchData(base_url("assessment/getQuestions/") + assessmentId);
        container.innerHTML = ""; // Clear loader

        if (response.status === "success" && response.data && response.data.questions.length > 0) {
            response.data.questions.forEach((question, index) => {
                const questionDiv = document.createElement("div");
                questionDiv.className = "card mb-3 question-item";
                let optionsHtml = "<ul class=\"list-unstyled\">";
                question.options.forEach((option, optIndex) => {
                    optionsHtml += `
                        <li class="form-check">
                            <input class="form-check-input" type="radio" name="answers[${question.id}]" id="q${question.id}_opt${optIndex}" value="${option.id}" required>
                            <label class="form-check-label" for="q${question.id}_opt${optIndex}">
                                ${sanitizeHTML(option.option_text)}
                            </label>
                        </li>`;
                });
                optionsHtml += "</ul>";
                questionDiv.innerHTML = `
                    <div class="card-body">
                        <h5 class="card-title">Question ${index + 1}:</h5>
                        <p class="card-text">${sanitizeHTML(question.question_text)}</p>
                        ${optionsHtml}
                    </div>
                `;
                container.appendChild(questionDiv);
            });
            document.getElementById("takeAssessmentForm").querySelector("button[type=\"submit\"]").style.display = "block";
        } else if (response.status === "success" && (!response.data || response.data.questions.length === 0)) {
            container.innerHTML = "<p>No questions found for this assessment.</p>";
        } else {
            container.innerHTML = `<p class="text-danger">Could not load assessment questions. ${response.message || "Please try again."}</p>`;
        }
    } catch (error) {
        console.error("Error fetching assessment questions:", error);
        container.innerHTML = "<p class=\"text-danger\">Could not load assessment questions. Please try again later.</p>";
    }
}

async function loadMySkillsAndAssessments() {
    const container = document.getElementById("mySkillsContainer");
    if (!container) return;
    container.innerHTML = `<div class="loader-container text-center"><div class="loader"></div><p>Loading your skills and assessment history...</p></div>`;

    try {
        const response = await fetchData(base_url("skills/myDetails")); // Endpoint to get user skills and assessment attempts
        container.innerHTML = ""; // Clear loader

        if (response.status === "success" && response.data) {
            let html = "";
            // Display Acquired Skills
            if (response.data.skills && response.data.skills.length > 0) {
                html += "<h3>My Skills & Ranks</h3><ul class=\"list-group mb-4\">";
                response.data.skills.forEach(skill => {
                    html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                                ${sanitizeHTML(skill.skill_name)}
                                <span class="badge bg-primary rounded-pill">${sanitizeHTML(skill.rank || "Not Ranked")}</span>
                             </li>`;
                });
                html += "</ul>";
            } else {
                html += "<p>You have not acquired or been ranked for any skills yet. Take an assessment to get started!</p>";
            }

            // Display Available Assessments to Take
            if (response.data.available_assessments && response.data.available_assessments.length > 0) {
                html += "<h3 class=\"mt-4\">Available Skill Assessments</h3><div class=\"list-group mb-4\">";
                response.data.available_assessments.forEach(assessment => {
                    html += `<a href="${base_url("assessment/take/")}${assessment.id}" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">${sanitizeHTML(assessment.title)}</h5>
                                    <small>Skill: ${sanitizeHTML(assessment.skill_name)}</small>
                                </div>
                                <p class="mb-1">${sanitizeHTML(assessment.description || "Click to start assessment.")}</p>
                                <small>${assessment.question_count} questions. Passing Score: ${assessment.passing_score}%</small>
                             </a>`;
                });
                html += "</div>";
            } else {
                html += "<p class=\"mt-4\">No new skill assessments currently available for you.</p>";
            }
            
            // Display Assessment History
            if (response.data.assessment_attempts && response.data.assessment_attempts.length > 0) {
                html += "<h3 class=\"mt-4\">My Assessment History</h3><ul class=\"list-group\">";
                response.data.assessment_attempts.forEach(attempt => {
                    html += `<li class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">${sanitizeHTML(attempt.assessment_title)}</h5>
                                    <small>Attempted: ${formatDateTime(attempt.attempt_date)}</small>
                                </div>
                                <p class="mb-1">Score: ${attempt.score_achieved}% (${attempt.passed ? "Passed" : "Failed"})</p>
                                <small>Skill: ${sanitizeHTML(attempt.skill_name)}</small>
                             </li>`;
                });
                html += "</ul>";
            } else {
                html += "<p class=\"mt-4\">You have not attempted any assessments yet.</p>";
            }

            container.innerHTML = html;
        } else {
            container.innerHTML = `<p class="text-danger">Could not load your skills and assessment data. ${response.message || "Please try again."}</p>`;
        }
    } catch (error) {
        console.error("Error fetching skills/assessments:", error);
        container.innerHTML = "<p class=\"text-danger\">Could not load your skills and assessment data. Please try again later.</p>";
    }
}

// Ensure displayMessage, sanitizeHTML, formatDateTime are available (e.g., from main.js)
// const base_url = function(path = "") { return document.documentElement.dataset.baseUrl + path; };

