// assets/js/admin_management.js

document.addEventListener("DOMContentLoaded", function() {
    // Common admin functionalities can be initialized here
    console.log("Admin Management JS Loaded");

    // Example: User Management Page (admin/manage_users.php)
    const usersTableContainer = document.getElementById("usersTableContainer");
    if (usersTableContainer) {
        loadAdminUsersList();
    }

    // Example: Skills Management Page (admin/manage_skills.php)
    const skillsTableContainer = document.getElementById("skillsTableContainer");
    const addSkillForm = document.getElementById("addSkillForm");
    if (skillsTableContainer) {
        loadAdminSkillsList();
    }
    if (addSkillForm) {
        addSkillForm.addEventListener("submit", handleAddSkillFormSubmit);
    }

    // Example: Assessments Management Page (admin/manage_assessments.php)
    const assessmentsTableContainer = document.getElementById("assessmentsTableContainer");
    const addAssessmentForm = document.getElementById("addAssessmentForm");
    if (assessmentsTableContainer) {
        loadAdminAssessmentsList();
    }
    if (addAssessmentForm) {
        // Populate skills dropdown for assessment creation
        populateSkillsDropdown(document.getElementById("assessment_skill_id"));
        addAssessmentForm.addEventListener("submit", handleAddAssessmentFormSubmit);
    }
    
    // Example: Platform Settings Page (admin/platform_settings.php)
    const platformSettingsForm = document.getElementById("platformSettingsForm");
    if(platformSettingsForm){
        loadPlatformSettings();
        platformSettingsForm.addEventListener("submit", handlePlatformSettingsSubmit);
    }
});

// --- User Management Functions ---
async function loadAdminUsersList() {
    const container = document.getElementById("usersTableContainer");
    container.innerHTML = `<div class="loader-container text-center"><div class="loader"></div><p>Loading users...</p></div>`;
    try {
        const response = await fetchData(base_url("admin/users/list"));
        container.innerHTML = "";
        if (response.status === "success" && response.data) {
            const table = createDynamicTable(response.data, 
                ["id", "username", "email", "role", "full_name", "is_active", "created_at"],
                {
                    "is_active": (val) => val == 1 ? 
                        `<span class="badge bg-success">Active</span>` : 
                        `<span class="badge bg-danger">Inactive</span>`,
                    "created_at": (val) => formatDate(val),
                    "actions": (row) => 
                        `<button class="btn btn-sm btn-primary me-1 edit-user-btn" data-id="${row.id}">Edit</button>
                         <button class="btn btn-sm btn-danger delete-user-btn" data-id="${row.id}">${row.is_active == 1 ? "Deactivate" : "Activate"}</button>`
                }
            );
            container.appendChild(table);
            addAdminUserActionListeners();
        } else {
            container.innerHTML = `<p class="text-danger">${response.message || "Could not load users."}</p>`;
        }
    } catch (error) {
        container.innerHTML = `<p class="text-danger">Error loading users: ${error.message}</p>`;
    }
}

function addAdminUserActionListeners() {
    document.querySelectorAll(".edit-user-btn").forEach(btn => {
        btn.addEventListener("click", function() { /* TODO: Implement edit user modal/form */ console.log("Edit user", this.dataset.id); });
    });
    document.querySelectorAll(".delete-user-btn").forEach(btn => {
        btn.addEventListener("click", async function() { 
            const userId = this.dataset.id;
            const action = this.textContent.toLowerCase(); // activate or deactivate
            if(confirm(`Are you sure you want to ${action} this user?`)){
                try {
                    const response = await fetchData(base_url(`admin/users/${action}/` + userId), { method: "POST", body: JSON.stringify({csrf_token: getCsrfToken()}) });
                    if(response.status === "success"){
                        displayMessage("success", response.message, "adminMessageContainer");
                        loadAdminUsersList();
                    } else {
                        displayMessage("danger", response.message, "adminMessageContainer");
                    }
                } catch(e){
                    displayMessage("danger", e.message, "adminMessageContainer");
                }
            }
        });
    });
}

// --- Skills Management Functions ---
async function loadAdminSkillsList() {
    const container = document.getElementById("skillsTableContainer");
    container.innerHTML = `<div class="loader-container text-center"><div class="loader"></div><p>Loading skills...</p></div>`;
    try {
        const response = await fetchData(base_url("admin/skills/list"));
        container.innerHTML = "";
        if (response.status === "success" && response.data) {
            const table = createDynamicTable(response.data, 
                ["id", "skill_name", "description", "category"],
                {
                    "actions": (row) => 
                        `<button class="btn btn-sm btn-primary me-1 edit-skill-btn" data-id="${row.id}">Edit</button>
                         <button class="btn btn-sm btn-danger delete-skill-btn" data-id="${row.id}">Delete</button>`
                }
            );
            container.appendChild(table);
            addAdminSkillActionListeners();
        } else {
            container.innerHTML = `<p class="text-danger">${response.message || "Could not load skills."}</p>`;
        }
    } catch (error) {
        container.innerHTML = `<p class="text-danger">Error loading skills: ${error.message}</p>`;
    }
}

async function handleAddSkillFormSubmit(event) {
    event.preventDefault();
    const form = event.target;
    if (!form.checkValidity()) {
        form.classList.add("was-validated");
        return;
    }
    const formData = new FormData(form);
    const submitButton = form.querySelector("button[type=\"submit\"]");
    const originalButtonText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Adding...`;
    try {
        const response = await fetchData(base_url("admin/skills/add"), { method: "POST", body: formData });
        if (response.status === "success") {
            displayMessage("success", response.message, "adminMessageContainer");
            form.reset();
            form.classList.remove("was-validated");
            loadAdminSkillsList();
        } else {
            displayMessage("danger", response.message, "adminMessageContainer");
        }
    } catch (e) {
        displayMessage("danger", e.message, "adminMessageContainer");
    } finally {
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
    }
}
function addAdminSkillActionListeners() { /* TODO: Implement edit/delete skill actions */ }

// --- Assessments Management Functions ---
async function loadAdminAssessmentsList() {
    const container = document.getElementById("assessmentsTableContainer");
    container.innerHTML = `<div class="loader-container text-center"><div class="loader"></div><p>Loading assessments...</p></div>`;
    try {
        const response = await fetchData(base_url("admin/assessments/list"));
        container.innerHTML = "";
        if (response.status === "success" && response.data) {
            const table = createDynamicTable(response.data, 
                ["id", "title", "skill_name", "description", "question_count", "passing_score"],
                {
                    "passing_score": (val) => `${val}%`,
                    "actions": (row) => 
                        `<a href="${base_url("admin/assessments/edit/")}${row.id}" class="btn btn-sm btn-primary me-1">Edit/View Questions</a>
                         <button class="btn btn-sm btn-danger delete-assessment-btn" data-id="${row.id}">Delete</button>`
                }
            );
            container.appendChild(table);
            addAdminAssessmentActionListeners();
        } else {
            container.innerHTML = `<p class="text-danger">${response.message || "Could not load assessments."}</p>`;
        }
    } catch (error) {
        container.innerHTML = `<p class="text-danger">Error loading assessments: ${error.message}</p>`;
    }
}

async function handleAddAssessmentFormSubmit(event) {
    event.preventDefault();
    const form = event.target;
    if (!form.checkValidity()) {
        form.classList.add("was-validated");
        return;
    }
    const formData = new FormData(form);
    const submitButton = form.querySelector("button[type=\"submit\"]");
    const originalButtonText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Adding...`;
    try {
        const response = await fetchData(base_url("admin/assessments/add"), { method: "POST", body: formData });
        if (response.status === "success") {
            displayMessage("success", response.message, "adminMessageContainer");
            form.reset();
            form.classList.remove("was-validated");
            loadAdminAssessmentsList();
            // Potentially redirect to edit questions page: window.location.href = base_url("admin/assessments/edit/") + response.data.assessment_id;
        } else {
            displayMessage("danger", response.message, "adminMessageContainer");
        }
    } catch (e) {
        displayMessage("danger", e.message, "adminMessageContainer");
    } finally {
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
    }
}
function addAdminAssessmentActionListeners() { /* TODO: Implement delete assessment actions */ }

async function populateSkillsDropdown(selectElement) {
    if (!selectElement) return;
    try {
        const response = await fetchData(base_url("admin/skills/listActive")); // Endpoint to get only active skills for dropdown
        if (response.status === "success" && response.data) {
            selectElement.innerHTML = "<option value=\"\">Select a Skill</option>"; // Default option
            response.data.forEach(skill => {
                const option = document.createElement("option");
                option.value = skill.id;
                option.textContent = sanitizeHTML(skill.skill_name);
                selectElement.appendChild(option);
            });
        }
    } catch (error) {
        console.error("Failed to populate skills dropdown:", error);
    }
}

// --- Platform Settings Functions ---
async function loadPlatformSettings(){
    const form = document.getElementById("platformSettingsForm");
    try {
        const response = await fetchData(base_url("admin/settings/get"));
        if(response.status === "success" && response.data){
            for(const key in response.data){
                if(form.elements[key]){
                    form.elements[key].value = response.data[key];
                }
            }
        } else {
            displayMessage("warning", response.message || "Could not load settings.", "adminMessageContainer");
        }
    } catch(e){
        displayMessage("danger", e.message, "adminMessageContainer");
    }
}

async function handlePlatformSettingsSubmit(event){
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const submitButton = form.querySelector("button[type=\"submit\"]");
    const originalButtonText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Saving...`;
    try {
        const response = await fetchData(base_url("admin/settings/update"), { method: "POST", body: formData });
        if (response.status === "success") {
            displayMessage("success", response.message, "adminMessageContainer");
        } else {
            displayMessage("danger", response.message, "adminMessageContainer");
        }
    } catch (e) {
        displayMessage("danger", e.message, "adminMessageContainer");
    } finally {
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
    }
}

// --- Helper: Create Dynamic Table ---
function createDynamicTable(data, columns, formatters = {}) {
    const table = document.createElement("table");
    table.className = "table table-striped table-hover table-responsive-md admin-table";
    const thead = table.createTHead();
    const tbody = table.createTBody();
    const headerRow = thead.insertRow();
    
    // Create headers
    columns.forEach(colKey => {
        const th = document.createElement("th");
        th.textContent = colKey.replace(/_/g, " ").replace(/\b\w/g, l => l.toUpperCase());
        headerRow.appendChild(th);
    });
    if (formatters["actions"]) {
        const th = document.createElement("th");
        th.textContent = "Actions";
        headerRow.appendChild(th);
    }

    // Create rows
    data.forEach(rowData => {
        const row = tbody.insertRow();
        columns.forEach(colKey => {
            const cell = row.insertCell();
            const cellValue = rowData[colKey];
            cell.innerHTML = formatters[colKey] ? formatters[colKey](cellValue, rowData) : sanitizeHTML(cellValue !== null && cellValue !== undefined ? cellValue : "N/A");
        });
        if (formatters["actions"]) {
            const cell = row.insertCell();
            cell.innerHTML = formatters["actions"](rowData);
        }
    });
    return table;
}

// Ensure fetchData, displayMessage, sanitizeHTML, formatDate are available from main.js

