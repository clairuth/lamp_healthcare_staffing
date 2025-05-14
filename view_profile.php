<?php
// templates/userprofile/view_profile.php

$page_title = "View Profile - StaffingPlus";
require_once APP_ROOT . "/templates/header.php";

// The user_id to view is passed as a parameter in the URL, e.g., /userprofile/view/123
// This is handled by the router in public/index.php, which calls UserProfileController::view($profile_user_id)
// The controller should fetch $profile_data and make it available to this template.

if (!isset($profile_data) || empty($profile_data)) {
    display_error_message("User profile not found or you do not have permission to view this profile.");
    require_once APP_ROOT . "/templates/footer.php";
    exit;
}

$current_user_id = get_current_user_id();
$is_own_profile = (is_logged_in() && $current_user_id == $profile_data["id"]);

?>

<div class="container mt-4">
    <div class="profile-header mb-4">
        <h1><?php echo htmlspecialchars($profile_data["username"]); ?></h1>
        <p class="text-muted">Member Since: <?php echo htmlspecialchars(date("M j, Y", strtotime($profile_data["created_at"]))); ?></p>
        <?php if ($is_own_profile): ?>
            <a href="<?php echo base_url("userprofile/edit/" . $profile_data["id"]); ?>" class="btn btn-secondary">Edit Profile</a>
        <?php endif; ?>
    </div>

    <div id="messageContainerGlobal"></div>

    <div class="card profile-details-card">
        <div class="card-header">
            <h4>Profile Information</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($profile_data["username"]); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($profile_data["email"]); ?></p>
                    <p><strong>Role:</strong> <?php echo htmlspecialchars(ucfirst($profile_data["user_type"])); ?></p>
                </div>
                <div class="col-md-6">
                    <?php if ($profile_data["user_type"] === "professional" && isset($professional_details)): ?>
                        <p><strong>First Name:</strong> <?php echo htmlspecialchars($professional_details["first_name"] ?? "N/A"); ?></p>
                        <p><strong>Last Name:</strong> <?php echo htmlspecialchars($professional_details["last_name"] ?? "N/A"); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($professional_details["phone_number"] ?? "N/A"); ?></p>
                        <p><strong>License Number:</strong> <?php echo htmlspecialchars($professional_details["license_number"] ?? "N/A"); ?></p>
                        <p><strong>Bio:</strong> <?php echo nl2br(htmlspecialchars($professional_details["bio"] ?? "N/A")); ?></p>
                    <?php elseif ($profile_data["user_type"] === "facility" && isset($facility_details)): ?>
                        <p><strong>Facility Name:</strong> <?php echo htmlspecialchars($facility_details["facility_name"] ?? "N/A"); ?></p>
                        <p><strong>Facility Type:</strong> <?php echo htmlspecialchars($facility_details["facility_type_name"] ?? "N/A"); ?></p> <!-- Assuming type name is joined -->
                        <p><strong>Contact Person:</strong> <?php echo htmlspecialchars($facility_details["contact_person"] ?? "N/A"); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($facility_details["phone_number"] ?? "N/A"); ?></p>
                        <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($facility_details["address"] ?? "N/A")); ?></p>
                        <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($facility_details["description"] ?? "N/A")); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($profile_data["user_type"] === "professional" && $is_own_profile): ?>
        <div class="mt-4">
            <h4>My Credentials</h4>
            <p><a href="<?php echo base_url("credential/listByUser"); ?>" class="btn btn-info">Manage My Credentials</a></p>
        </div>
        <div class="mt-4">
            <h4>My Skills & Assessments</h4>
            <p><a href="<?php echo base_url("skill/mySkills"); ?>" class="btn btn-info">View My Skills & Assessments</a></p>
        </div>
    <?php endif; ?>

</div>

<?php
require_once APP_ROOT . "/templates/footer.php";
?>
