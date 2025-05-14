<?php
// templates/admin/manage_users.php

$page_title = "Manage Users - Admin - StaffingPlus";
require_once APP_ROOT . "/templates/header.php";

if (!is_logged_in() || get_current_user_type() !== "admin") {
    display_error_message("Access Denied: You must be logged in as an administrator.");
    require_once APP_ROOT . "/templates/footer.php";
    exit;
}

// User type filter from URL, e.g., /admin/users/list/professional
$user_type_filter = $params[0] ?? "all"; // Default to all if no specific type

?>

<div class="container-fluid mt-4 admin-management-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Users <?php 
            if ($user_type_filter !== "all") { 
                echo "(" . htmlspecialchars(ucfirst($user_type_filter)) . "s)"; 
            } 
        ?></h1>
        <!-- Add New User button could go here if needed -->
    </div>

    <div id="messageContainerGlobal"></div>

    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-4">
                    <select id="userTypeFilterSelect" class="form-control">
                        <option value="all" <?php echo ($user_type_filter === "all") ? "selected" : ""; ?>>All User Types</option>
                        <option value="professional" <?php echo ($user_type_filter === "professional") ? "selected" : ""; ?>>Healthcare Professionals</option>
                        <option value="facility" <?php echo ($user_type_filter === "facility") ? "selected" : ""; ?>>Facilities</option>
                        <option value="admin" <?php echo ($user_type_filter === "admin") ? "selected" : ""; ?>>Administrators</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <input type="text" id="userSearchInput" class="form-control" placeholder="Search users by name, email, or ID...">
                </div>
            </div>
        </div>
        <div class="card-body">
            <div id="usersTableContainer" class="table-responsive">
                <div class="loader-container text-center">
                    <div class="loader"></div>
                    <p>Loading users...</p>
                </div>
                <!-- Users table will be loaded here by JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const usersTableContainer = document.getElementById("usersTableContainer");
    const userTypeFilterSelect = document.getElementById("userTypeFilterSelect");
    const userSearchInput = document.getElementById("userSearchInput");
    let currentFilter = "<?php echo $user_type_filter; ?>";
    let searchTimeout = null;

    async function loadUsers(filterType = "all", searchTerm = "") {
        usersTableContainer.innerHTML = `<div class="loader-container text-center"><div class="loader"></div><p>Loading users...</p></div>`;
        try {
            let url = `<?php echo base_url("admin/listUsersAPI"); ?>/${filterType}`;
            if (searchTerm) {
                url += `?search=${encodeURIComponent(searchTerm)}`;
            }
            const response = await fetchData(url);
            usersTableContainer.innerHTML = ""; // Clear loader

            if (response.status === "success" && response.data && response.data.length > 0) {
                const table = document.createElement("table");
                table.className = "table table-hover table-striped admin-table";
                table.innerHTML = `
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>User Type</th>
                            <th>Status</th>
                            <th>Registered On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                `;
                const tbody = table.querySelector("tbody");
                response.data.forEach(user => {
                    const tr = document.createElement("tr");
                    tr.innerHTML = `
                        <td>${user.id}</td>
                        <td>${sanitizeHTML(user.username)}</td>
                        <td>${sanitizeHTML(user.email)}</td>
                        <td><span class="badge bg-info">${sanitizeHTML(user.user_type)}</span></td>
                        <td><span class="badge bg-${user.is_active ? "success" : "danger"}">${user.is_active ? "Active" : "Inactive"}</span></td>
                        <td>${formatDateTime(user.created_at, false)}</td>
                        <td>
                            <a href="<?php echo base_url("userprofile/view/"); ?>${user.id}" class="btn btn-sm btn-outline-primary" title="View Profile"><i class="fas fa-eye"></i></a>
                            <a href="<?php echo base_url("admin/users/edit/"); ?>${user.id}" class="btn btn-sm btn-outline-secondary ml-1" title="Edit User"><i class="fas fa-edit"></i></a>
                            <button class="btn btn-sm btn-outline-${user.is_active ? "warning" : "success"} ml-1 toggle-active-btn" data-id="${user.id}" data-current-status="${user.is_active}" title="${user.is_active ? "Deactivate" : "Activate"} User">
                                <i class="fas fa-${user.is_active ? "user-slash" : "user-check"}"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger ml-1 delete-user-btn" data-id="${user.id}" title="Delete User"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
                usersTableContainer.appendChild(table);
                addEventListenersForUserActions();
            } else if (response.status === "success" && response.data && response.data.length === 0) {
                 usersTableContainer.innerHTML = "<p>No users found matching your criteria.</p>";
            } else {
                usersTableContainer.innerHTML = `<p class="text-danger">Could not load users. ${response.message || "Please try again later."}</p>`;
            }
        } catch (error) {
            console.error("Error fetching users:", error);
            usersTableContainer.innerHTML = "<p class=\"text-danger\">Could not load users. Please try again later.</p>";
        }
    }

    function addEventListenersForUserActions() {
        document.querySelectorAll(".toggle-active-btn").forEach(button => {
            button.addEventListener("click", async function() {
                const userId = this.dataset.id;
                const currentStatus = this.dataset.currentStatus == "1"; // Convert to boolean
                const action = currentStatus ? "deactivate" : "activate";
                if (confirm(`Are you sure you want to ${action} this user?`)) {
                    try {
                        const response = await fetchData(`<?php echo base_url("admin/users/toggleActive/"); ?>${userId}`, {
                            method: "POST",
                            body: JSON.stringify({ csrf_token: "<?php echo htmlspecialchars(generate_csrf_token()); ?>" })
                        });
                        if (response.status === "success") {
                            displayMessage("success", response.message || `User ${action}d successfully.`, "messageContainerGlobal");
                            loadUsers(currentFilter, userSearchInput.value.trim()); // Refresh list
                        } else {
                            displayMessage("danger", response.message || `Failed to ${action} user.`, "messageContainerGlobal");
                        }
                    } catch (error) {
                        displayMessage("danger", error.message || `Error ${action}ing user.`, "messageContainerGlobal");
                    }
                }
            });
        });

        document.querySelectorAll(".delete-user-btn").forEach(button => {
            button.addEventListener("click", async function() {
                const userId = this.dataset.id;
                if (confirm("Are you sure you want to PERMANENTLY DELETE this user? This action cannot be undone.")) {
                    try {
                        const response = await fetchData(`<?php echo base_url("admin/users/delete/"); ?>${userId}`, {
                            method: "POST",
                            body: JSON.stringify({ csrf_token: "<?php echo htmlspecialchars(generate_csrf_token()); ?>" })
                        });
                        if (response.status === "success") {
                            displayMessage("success", response.message || "User deleted successfully.", "messageContainerGlobal");
                            loadUsers(currentFilter, userSearchInput.value.trim()); // Refresh list
                        } else {
                            displayMessage("danger", response.message || "Failed to delete user.", "messageContainerGlobal");
                        }
                    } catch (error) {
                        displayMessage("danger", error.message || "Error deleting user.", "messageContainerGlobal");
                    }
                }
            });
        });
    }

    if (userTypeFilterSelect) {
        userTypeFilterSelect.addEventListener("change", function() {
            currentFilter = this.value;
            // Update URL for bookmarking/sharing if desired, or just reload data
            // window.history.pushState({}, "", `<?php echo base_url("admin/users/list/"); ?>${currentFilter}`);
            loadUsers(currentFilter, userSearchInput.value.trim());
        });
    }

    if (userSearchInput) {
        userSearchInput.addEventListener("keyup", function() {
            clearTimeout(searchTimeout);
            const searchTerm = this.value.trim();
            searchTimeout = setTimeout(() => {
                loadUsers(currentFilter, searchTerm);
            }, 500); // Debounce search input
        });
    }

    // Initial load
    loadUsers(currentFilter);
});
</script>

<?php
require_once APP_ROOT . "/templates/footer.php";
?>
