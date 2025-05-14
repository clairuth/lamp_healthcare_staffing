<?php
// app/controllers/SkillController.php

class SkillController {
    private $db;
    private $skill_model;
    private $professional_skill_model;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
        $this->skill_model = new Skill($this->db);
        $this->professional_skill_model = new ProfessionalSkill($this->db);
    }

    // Admin: Create a new skill (for the system)
    public function adminCreateSkill() {
        // TODO: Add admin role check
        // if (!is_logged_in() || get_current_user_type() !== "admin") { ... }

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            http_response_code(405); echo json_encode(["status" => "error", "message" => "POST method required."]); return;
        }

        $data = json_decode(file_get_contents("php://input"));
        if (!$data && isset($_POST)) $data = (object)$_POST;

        if (empty($data->skill_name)) {
            http_response_code(400); echo json_encode(["status" => "error", "message" => "Skill name is required."]); return;
        }

        // Check if skill name already exists
        if ($this->skill_model->findByName($data->skill_name)) {
            http_response_code(409); // Conflict
            echo json_encode(["status" => "error", "message" => "A skill with this name already exists."]); return;
        }

        $this->skill_model->skill_name = $data->skill_name;
        $this->skill_model->skill_category = $data->skill_category ?? "General";
        $this->skill_model->description = $data->description ?? "";

        if ($this->skill_model->create()) {
            http_response_code(201);
            echo json_encode(["status" => "success", "message" => "Skill created successfully.", "skill_id" => $this->skill_model->id]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to create skill."]);
        }
    }

    // Admin: List all skills in the system
    public function adminListSkills() {
        // TODO: Add admin role check
        $limit = $_GET["limit"] ?? 100;
        $offset = $_GET["offset"] ?? 0;
        $skills = $this->skill_model->getAll((int)$limit, (int)$offset);
        $total = $this->skill_model->countAll();
        http_response_code(200);
        echo json_encode(["status" => "success", "data" => $skills, "pagination" => ["total" => $total, "limit" => (int)$limit, "offset" => (int)$offset]]);
    }
    
    // Public: List all skills (e.g., for professionals to browse and add to profile)
    public function listAllSkills() {
        $limit = $_GET["limit"] ?? 100;
        $offset = $_GET["offset"] ?? 0;
        $skills = $this->skill_model->getAll((int)$limit, (int)$offset);
        $total = $this->skill_model->countAll();
        http_response_code(200);
        echo json_encode(["status" => "success", "data" => $skills, "pagination" => ["total" => $total, "limit" => (int)$limit, "offset" => (int)$offset]]);
    }

    // Professional: Add a skill to their profile
    public function addProfessionalSkill() {
        if (!is_logged_in() || get_current_user_type() !== "professional") {
            http_response_code(401); echo json_encode(["status" => "error", "message" => "Authentication required as a professional."]); return;
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            http_response_code(405); echo json_encode(["status" => "error", "message" => "POST method required."]); return;
        }

        $professional_user_id = get_current_user_id();
        $data = json_decode(file_get_contents("php://input"));
        if (!$data && isset($_POST)) $data = (object)$_POST;

        if (empty($data->skill_id)) {
            http_response_code(400); echo json_encode(["status" => "error", "message" => "Skill ID is required."]); return;
        }

        // Check if skill exists in the system
        $skill = $this->skill_model->findById($data->skill_id);
        if (!$skill) {
            http_response_code(404); echo json_encode(["status" => "error", "message" => "Skill not found in system."]); return;
        }

        $this->professional_skill_model->professional_user_id = $professional_user_id;
        $this->professional_skill_model->skill_id = $data->skill_id;
        $this->professional_skill_model->skill_level = $data->skill_level ?? "Beginner";
        $this->professional_skill_model->years_experience = $data->years_experience ?? null;
        $this->professional_skill_model->is_verified = false; // Default to not verified

        if ($this->professional_skill_model->create()) {
            http_response_code(201);
            echo json_encode(["status" => "success", "message" => "Skill added to your profile.", "professional_skill_id" => $this->professional_skill_model->id]);
        } else {
            http_response_code(500); // Could be 409 if skill already exists for user
            echo json_encode(["status" => "error", "message" => "Failed to add skill to profile. It might already be added."]);
        }
    }

    // Professional: List their own skills
    public function listProfessionalSkills() {
        if (!is_logged_in() || get_current_user_type() !== "professional") {
            http_response_code(401); echo json_encode(["status" => "error", "message" => "Authentication required as a professional."]); return;
        }
        $professional_user_id = get_current_user_id();
        $skills = $this->professional_skill_model->findByProfessionalUserId($professional_user_id);
        http_response_code(200);
        echo json_encode(["status" => "success", "data" => $skills]);
    }

    // Professional: Update a skill on their profile
    public function updateProfessionalSkill($prof_skill_id = null) {
        if (!is_logged_in() || get_current_user_type() !== "professional") {
            http_response_code(401); echo json_encode(["status" => "error", "message" => "Authentication required as a professional."]); return;
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") { // Or PUT
            http_response_code(405); echo json_encode(["status" => "error", "message" => "POST (or PUT) method required."]); return;
        }

        $professional_user_id = get_current_user_id();
        $data = json_decode(file_get_contents("php://input"));
        if (!$data && isset($_POST)) $data = (object)$_POST;
        if ($prof_skill_id === null) $prof_skill_id = $data->professional_skill_id ?? null;

        if (empty($prof_skill_id)) {
            http_response_code(400); echo json_encode(["status" => "error", "message" => "Professional Skill ID is required."]); return;
        }

        $existing_prof_skill = $this->professional_skill_model->findById($prof_skill_id);
        if (!$existing_prof_skill || $existing_prof_skill->professional_user_id != $professional_user_id) {
            http_response_code(404); echo json_encode(["status" => "error", "message" => "Skill not found on your profile or permission denied."]); return;
        }

        $this->professional_skill_model->id = $prof_skill_id;
        $this->professional_skill_model->professional_user_id = $professional_user_id;
        $this->professional_skill_model->skill_level = $data->skill_level ?? $existing_prof_skill->skill_level;
        $this->professional_skill_model->years_experience = $data->years_experience ?? $existing_prof_skill->years_experience;
        // is_verified and verified_at are usually handled by assessment or admin
        $this->professional_skill_model->is_verified = $existing_prof_skill->is_verified; 
        $this->professional_skill_model->verified_at = $existing_prof_skill->verified_at;

        if ($this->professional_skill_model->update()) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Skill on your profile updated."]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to update skill on profile."]);
        }
    }

    // Professional: Remove a skill from their profile
    public function removeProfessionalSkill($prof_skill_id = null) {
        if (!is_logged_in() || get_current_user_type() !== "professional") {
            http_response_code(401); echo json_encode(["status" => "error", "message" => "Authentication required as a professional."]); return;
        }
        if ($_SERVER["REQUEST_METHOD"] !== "POST") { // Or DELETE
            http_response_code(405); echo json_encode(["status" => "error", "message" => "POST (or DELETE) method required."]); return;
        }

        $professional_user_id = get_current_user_id();
        $data = json_decode(file_get_contents("php://input"));
        if (!$data && isset($_POST)) $data = (object)$_POST;
        if ($prof_skill_id === null) $prof_skill_id = $data->professional_skill_id ?? null;

        if (empty($prof_skill_id)) {
            http_response_code(400); echo json_encode(["status" => "error", "message" => "Professional Skill ID is required."]); return;
        }

        $existing_prof_skill = $this->professional_skill_model->findById($prof_skill_id);
        if (!$existing_prof_skill || $existing_prof_skill->professional_user_id != $professional_user_id) {
            http_response_code(404); echo json_encode(["status" => "error", "message" => "Skill not found on your profile or permission denied."]); return;
        }
        
        $this->professional_skill_model->id = $prof_skill_id;
        $this->professional_skill_model->professional_user_id = $professional_user_id;

        if ($this->professional_skill_model->delete()) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Skill removed from your profile."]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to remove skill from profile."]);
        }
    }
}
?>
