<?php
// templates/admin/dashboard.php

$page_title = "Admin Dashboard - StaffingPlus";
require_once APP_ROOT . "/templates/header.php";

if (!is_logged_in() || get_current_user_type() !== "admin") {
    display_error_message("Access Denied: You must be logged in as an administrator.");
    require_once APP_ROOT . "/templates/footer.php";
    exit;
}

// Data for the dashboard will be loaded via JavaScript/AJAX or passed by the controller
// For example, $stats = AdminController::getDashboardStats();

?>

<div class="container-fluid mt-4 admin-dashboard">
    <h1 class="mb-4">Administrator Dashboard</h1>

    <div id="messageContainerGlobal"></div>

    <!-- Stats Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">Total Users</div>
                <div class="card-body">
                    <h4 class="card-title" id="statsTotalUsers">Loading...</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">Active Shifts</div>
                <div class="card-body">
                    <h4 class="card-title" id="statsActiveShifts">Loading...</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info mb-3">
                <div class="card-header">Pending Verifications</div>
                <div class="card-body">
                    <h4 class="card-title" id="statsPendingVerifications">Loading...</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-header">Open Support Tickets</div>
                <div class="card-body">
                    <h4 class="card-title" id="statsOpenTickets">N/A</h4> <!-- Placeholder -->
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Management Links -->
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">User Management</div>
                <div class="card-body">
                    <p><a href="<?php echo base_url("admin/users/list"); ?>" class="btn btn-outline-primary btn-block">Manage All Users</a></p>
                    <p><a href="<?php echo base_url("admin/users/list/professional"); ?>" class="btn btn-outline-secondary btn-block">View Healthcare Professionals</a></p>
                    <p><a href="<?php echo base_url("admin/users/list/facility"); ?>" class="btn btn-outline-secondary btn-block">View Facilities</a></p>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header">Content Management</div>
                <div class="card-body">
                     <p><a href="<?php echo base_url("admin/skills/manage"); ?>" class="btn btn-outline-primary btn-block">Manage Skills List</a></p>
                     <p><a href="<?php echo base_url("admin/assessments/manage"); ?>" class="btn btn-outline-primary btn-block">Manage Skill Assessments</a></p>
                     <!-- Add links for managing site content, FAQs, etc. -->
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Platform Operations</div>
                <div class="card-body">
                    <p><a href="<?php echo base_url("admin/shifts/listAll"); ?>" class="btn btn-outline-primary btn-block">View All Shifts</a></p>
                    <p><a href="<?php echo base_url("admin/credentials/pending"); ?>" class="btn btn-outline-primary btn-block">Review Pending Credentials</a></p>
                    <p><a href="<?php echo base_url("admin/payments/overview"); ?>" class="btn btn-outline-primary btn-block">Payment Transactions Overview</a></p>
                </div>
            </div>
             <div class="card mb-4">
                <div class="card-header">System Settings</div>
                <div class="card-body">
                    <p><a href="<?php echo base_url("admin/settings"); ?>" class="btn btn-outline-danger btn-block">Platform Configuration</a></p>
                    <!-- Add links for logs, maintenance modes etc. -->
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    async function loadDashboardStats() {
        try {
            const response = await fetchData("<?php echo base_url("admin/dashboardStats"); ?>");
            if (response.status === "success" && response.data) {
                document.getElementById("statsTotalUsers").textContent = response.data.total_users || "0";
                document.getElementById("statsActiveShifts").textContent = response.data.active_shifts || "0";
                document.getElementById("statsPendingVerifications").textContent = response.data.pending_verifications || "0";
                // document.getElementById("statsOpenTickets").textContent = response.data.open_tickets || "0";
            } else {
                console.warn("Could not load dashboard stats:", response.message);
                setDefaultStats();
            }
        } catch (error) {
            console.error("Error fetching dashboard stats:", error);
            setDefaultStats();
        }
    }

    function setDefaultStats() {
        document.getElementById("statsTotalUsers").textContent = "N/A";
        document.getElementById("statsActiveShifts").textContent = "N/A";
        document.getElementById("statsPendingVerifications").textContent = "N/A";
    }

    loadDashboardStats();
});
</script>

<?php
require_once APP_ROOT . "/templates/footer.php";
?>
