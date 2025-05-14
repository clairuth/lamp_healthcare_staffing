<!DOCTYPE html>
<html lang="en" data-base-url="<?php echo rtrim(BASE_URL, '/') . '/'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? sanitize_html($page_title) . " - " : ""; ?>Healthcare Staffing Platform</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    <script>
        // Define ASSETS_URL for JavaScript if needed, or use base_url() more extensively
        const ASSETS_URL = "<?php echo ASSETS_URL; ?>";
    </script>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="<?php echo BASE_URL; ?>"><i class="fas fa-hospital-user me-2"></i>StaffingPro</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link <?php echo (CURRENT_PAGE === 'home') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>"><i class="fas fa-home me-1"></i>Home</a>
                        </li>
                        <?php if (is_logged_in()): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo (CURRENT_PAGE === 'dashboard') ? 'active' : ''; ?>" href="<?php echo BASE_URL . 'dashboard'; ?>"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownProfile" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user-circle me-1"></i><?php echo sanitize_html($_SESSION['username']); ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownProfile">
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL . 'userprofile/view/' . $_SESSION['user_id']; ?>"><i class="fas fa-user-edit me-1"></i>My Profile</a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL . 'credentials/manage'; ?>"><i class="fas fa-id-card me-1"></i>My Credentials</a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL . 'skills/my'; ?>"><i class="fas fa-cogs me-1"></i>My Skills</a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL . 'payment/methods'; ?>"><i class="fas fa-credit-card me-1"></i>Payment Methods</a></li>
                                    <?php if (is_admin()): ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="<?php echo BASE_URL . 'admin/dashboard'; ?>"><i class="fas fa-user-shield me-1"></i>Admin Panel</a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL . 'auth/logout'; ?>"><i class="fas fa-sign-out-alt me-1"></i>Logout</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo (CURRENT_PAGE === 'login') ? 'active' : ''; ?>" href="<?php echo BASE_URL . 'auth/login'; ?>"><i class="fas fa-sign-in-alt me-1"></i>Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo (CURRENT_PAGE === 'register') ? 'active' : ''; ?>" href="<?php echo BASE_URL . 'auth/register'; ?>"><i class="fas fa-user-plus me-1"></i>Register</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (CURRENT_PAGE === 'shifts_open') ? 'active' : ''; ?>" href="<?php echo BASE_URL . 'shift/listOpen'; ?>"><i class="fas fa-briefcase me-1"></i>Open Shifts</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main class="container mt-5 pt-5 mb-5">
        <!-- Message area for global messages if any -->
        <div id="globalMessageContainer"></div>

