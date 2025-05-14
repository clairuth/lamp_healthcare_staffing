<?php
// app/controllers/SkillAssessmentController.php

class SkillAssessmentController {
    private $db;
    private $skill_assessment_model;
    private $professional_assessment_attempt_model;
    private $professional_skill_model;
    private $skill_model;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
        $this->skill_assessment_model = new SkillAssessment($this->db);
        $this->professional_assessment_attempt_model = new ProfessionalAssessmentAttempt($this->db);
        $this->professional_skill_model = new ProfessionalSkill($this->db);
        $this->skill_model = new Skill($this->db);
    }

    // Admin: Create a new skill assessment
    public function adminCreateAssessment() {
        // TODO: Add admin role check
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            http_response_code(405); echo json_encode(["status" => "error", "message" => "POST method required."]); return;
        }

        $data = json_decode(file_get_contents("php://input"));
        if (!$data && isset($_POST)) $data = (object)$_POST;

        if (empty($data->skill_id) || empty($data->assessment_name) || empty($data->questions_data)) {
            http_response_code(400); echo json_encode(["status" => "error", "message" => "Skill ID, assessment name, and questions data are required."]); return;
        }
        // Validate questions_data structure (basic check)
        $questions = json_decode($data->questions_data);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($questions)) {
            http_response_code(400); echo json_encode(["status" => "error", "message" => "Invalid questions_data format. Must be a valid JSON array of questions."]); return;
        }

        $this->skill_assessment_model->skill_id = $data->skill_id;
        $this->skill_assessment_model->assessment_name = $data->assessment_name;
        $this->skill_assessment_model->assessment_type = $data->assessment_type ?? "multiple_choice";
        $this->skill_assessment_model->passing_score = $data->passing_score ?? 70; // Default passing score
        $this->skill_assessment_model->total_questions = count($questions); // Calculate from questions_data
        $this->skill_assessment_model->time_limit_minutes = $data->time_limit_minutes ?? 30;
        $this->skill_assessment_model->instructions = $data->instructions ?? "";
        $this->skill_assessment_model->questions_data = $data->questions_data; // Store as JSON string
        $this->skill_assessment_model->created_by_user_id = get_current_user_id(); // Assuming admin is logged in

        if ($this->skill_assessment_model->create()) {
            http_response_code(201);
            echo json_encode(["status" => "success", "message" => "Skill assessment created successfully.", "assessment_id" => $this->skill_assessment_model->id]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to create skill assessment."]);
        }
    }

    // Admin: List all assessments
    public function adminListAssessments() {
        // TODO: Add admin role check
        $limit = $_GET["limit"] ?? 100;
        $offset = $_GET["offset"] ?? 0;
        $assessments = $this->skill_assessment_model->getAll((int)$limit, (int)$offset);
        // Optionally count total for pagination
        http_response_code(200);
        echo json_encode(["status" => "success", "data" => $assessments]);
    }

    // Professional: List available assessments for a skill they have
    public function listAssessmentsForProfessionalSkill($professional_skill_id = null) {
        if (!is_logged_in() || get_current_user_type() !== "professional") {
            http_response_code(401); echo json_encode(["status" => "error", "message" => "Authentication required as a professional."]); return;
        }
        if (empty($professional_skill_id)) {
            http_response_code(400); echo json_encode(["status" => "error", "message" => "Professional Skill ID is required."]); return;
        }

        $prof_skill = $this->professional_skill_model->findById($professional_skill_id);
        if (!$prof_skill || $prof_skill->professional_user_id != get_current_user_id()) {
            http_response_code(404); echo json_encode(["status" => "error", "message" => "Professional skill not found or permission denied."]); return;
        }

        $assessments = $this->skill_assessment_model->findBySkillId($prof_skill->skill_id);
        http_response_code(200);
        echo json_encode(["status" => "success", "data" => $assessments]);
    }

    // Professional: View a specific assessment (get questions, instructions)
    public function viewAssessment($assessment_id = null) {
        if (!is_logged_in() || get_current_user_type() !== "professional") {
            http_response_code(401); echo json_encode(["status" => "error", "message" => "Authentication required as a professional."]); return;
        }
        if (empty($assessment_id)) {
            http_response_code(400); echo json_encode(["status" => "error", "message" => "Assessment ID is required."]); return;
        }

        $assessment = $this->skill_assessment_model->findById($assessment_id);
        if (!$assessment) {
            http_response_code(404); echo json_encode(["status" => "error", "message" => "Assessment not found."]); return;
        }
        
        // Decode questions_data for frontend display (remove correct answers before sending)
        $questions_for_display = [];
        if (!empty($assessment->questions_data)) {
            $parsed_questions = json_decode($assessment->questions_data, true);
            if (is_array($parsed_questions)) {
                foreach ($parsed_questions as $q) {
                    unset($q["correct_answer"]); // Do not send correct answer to client before attempt
                    $questions_for_display[] = $q;
                }
            }
        }
        $assessment->questions_for_display = $questions_for_display;
        unset($assessment->questions_data); // Don't send the raw data with answers

        http_response_code(200);
        echo json_encode(["status" => "success", "data" => $assessment]);
    }

    // Professional: Submit an assessment attempt
    public function submitAssessmentAttempt($assessment_id = null) {
        if (!is_logged_in() || get_current_user_type() !== "professional") {
            http_response_code(401); echo json_encode(["status" => "error", "message" => "Authentication required as a professional."]); return;
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            http_response_code(405); echo json_encode(["status" => "error", "message" => "POST method required."]); return;
        }

        $professional_user_id = get_current_user_id();
        $data = json_decode(file_get_contents("php://input"));
        if (!$data && isset($_POST)) $data = (object)$_POST;

        if ($assessment_id === null) $assessment_id = $data->assessment_id ?? null;
        $submitted_answers = $data->answers_data ?? null; // Expecting JSON: [{question_index: 0, answer: "A"}, ...]
        $time_taken_seconds = $data->time_taken_seconds ?? null;

        if (empty($assessment_id) || empty($submitted_answers)) {
            http_response_code(400); echo json_encode(["status" => "error", "message" => "Assessment ID and answers data are required."]); return;
        }

        $assessment = $this->skill_assessment_model->findById($assessment_id);
        if (!$assessment) {
            http_response_code(404); echo json_encode(["status" => "error", "message" => "Assessment not found."]); return;
        }

        // --- Scoring Logic --- 
        $score = 0;
        $total_possible_score = 0;
        $correct_answers_count = 0;
        $assessment_questions = json_decode($assessment->questions_data, true);
        $parsed_submitted_answers = json_decode($submitted_answers, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($parsed_submitted_answers)) {
            http_response_code(400); echo json_encode(["status" => "error", "message" => "Invalid answers_data format."]); return;
        }

        // Create a map of submitted answers for quick lookup
        $submitted_map = [];
        foreach ($parsed_submitted_answers as $sub_ans) {
            if (isset($sub_ans["question_index"])) { // Assuming frontend sends question_index
                 $submitted_map[$sub_ans["question_index"]] = $sub_ans["answer"];
            }
        }

        foreach ($assessment_questions as $index => $q_data) {
            $points = $q_data["points"] ?? 1;
            $total_possible_score += $points;
            if (isset($submitted_map[$index]) && strtoupper(trim($submitted_map[$index])) === strtoupper(trim($q_data["correct_answer"]))) {
                $score += $points;
                $correct_answers_count++;
            }
        }
        $percentage_score = ($total_possible_score > 0) ? ($score / $total_possible_score) * 100 : 0;
        $is_passed = $percentage_score >= ($assessment->passing_score ?? 70);
        // --- End Scoring Logic ---

        $this->professional_assessment_attempt_model->professional_user_id = $professional_user_id;
        $this->professional_assessment_attempt_model->skill_assessment_id = $assessment_id;
        $this->professional_assessment_attempt_model->skill_id = $assessment->skill_id;
        $this->professional_assessment_attempt_model->score_achieved = $percentage_score;
        $this->professional_assessment_attempt_model->is_passed = $is_passed;
        $this->professional_assessment_attempt_model->answers_data = $submitted_answers; // Store what user submitted
        $this->professional_assessment_attempt_model->time_taken_seconds = $time_taken_seconds;

        if ($this->professional_assessment_attempt_model->create()) {
            // If passed, update the corresponding professional_skill to verified
            if ($is_passed) {
                $prof_skill = $this->professional_skill_model->findByProfessionalUserId($professional_user_id);
                // This needs refinement: find the specific professional_skill entry for $assessment->skill_id
                // For now, assuming a method to find a specific professional_skill by user_id and skill_id
                $target_prof_skill_id = null;
                $all_prof_skills = $this->professional_skill_model->findByProfessionalUserId($professional_user_id);
                foreach($all_prof_skills as $ps) {
                    if ($ps["skill_id"] == $assessment->skill_id) {
                        $target_prof_skill_id = $ps["id"];
                        break;
                    }
                }
                if ($target_prof_skill_id) {
                    $ps_to_update = $this->professional_skill_model->findById($target_prof_skill_id);
                    if ($ps_to_update) {
                        $ps_to_update->is_verified = true;
                        $ps_to_update->verified_at = date("Y-m-d H:i:s");
                        $ps_to_update->update(); // Update the professional skill record
                    }
                }
            }

            http_response_code(201);
            echo json_encode([
                "status" => "success", 
                "message" => "Assessment attempt submitted successfully.", 
                "attempt_id" => $this->professional_assessment_attempt_model->id,
                "score" => $percentage_score,
                "is_passed" => $is_passed
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to submit assessment attempt."]);
        }
    }

    // Professional: List their assessment attempts
    public function listProfessionalAttempts() {
        if (!is_logged_in() || get_current_user_type() !== "professional") {
            http_response_code(401); echo json_encode(["status" => "error", "message" => "Authentication required as a professional."]); return;
        }
        $professional_user_id = get_current_user_id();
        $attempts = $this->professional_assessment_attempt_model->findByProfessionalUserId($professional_user_id);
        http_response_code(200);
        echo json_encode(["status" => "success", "data" => $attempts]);
    }
}
?>
