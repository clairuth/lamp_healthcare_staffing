<?php
// templates/auth/login.php

$page_title = "Login - StaffingPlus";
require_once APP_ROOT . "/templates/header.php";

?>

<div class="auth-container">
    <h2 class="text-center mb-4">Login to Your Account</h2>

    <div id="messageContainer"></div>

    <form id="loginForm" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">

        <div class="form-group">
            <label for="email_or_username">Email or Username</label>
            <input type="text" class="form-control" id="email_or_username" name="email_or_username" required>
            <div class="invalid-feedback">Please enter your email or username.</div>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
            <div class="invalid-feedback">Please enter your password.</div>
        </div>

        <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
            <label class="form-check-label" for="remember_me">Remember me</label>
        </div>

        <button type="submit" class="btn btn-primary btn-full-width mt-3">Login</button>
    </form>

    <p class="text-center mt-3">
        <a href="<?php echo base_url("auth/forgotPassword"); ?>">Forgot your password?</a>
    </p>
    <p class="text-center mt-2">
        Don't have an account? <a href="<?php echo base_url("auth/register"); ?>">Register here</a>
    </p>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const loginForm = document.getElementById("loginForm");

    if (loginForm) {
        loginForm.addEventListener("submit", async function(event) {
            event.preventDefault();
            event.stopPropagation();

            if (!loginForm.checkValidity()) {
                loginForm.classList.add("was-validated");
                displayMessage("danger", "Please fill in all required fields correctly.");
                return;
            }

            const formData = new FormData(loginForm);
            const data = Object.fromEntries(formData.entries());

            const submitButton = loginForm.querySelector("button[type=\"submit\"]");
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = 
            submitButton.disabled = true;

            try {
                const response = await fetchData("<?php echo base_url("auth/login"); ?>", {
                    method: "POST",
                    body: JSON.stringify(data)
                });

                if (response.status === "success") {
                    displayMessage("success", response.message + " Redirecting to dashboard...");
                    // Redirect based on user type or to a generic dashboard
                    let redirectUrl = "<?php echo base_url("home/dashboard"); ?>";
                    if (response.data && response.data.user_type) {
                        // Potentially redirect to specific dashboards if needed
                        // switch(response.data.user_type) {
                        //     case "professional": redirectUrl = "<?php echo base_url("professional/dashboard"); ?>"; break;
                        //     case "facility": redirectUrl = "<?php echo base_url("facility/dashboard"); ?>"; break;
                        //     case "admin": redirectUrl = "<?php echo base_url("admin/dashboard"); ?>"; break;
                        // }
                    }
                    setTimeout(() => {
                        window.location.href = redirectUrl;
                    }, 1500);
                } else {
                    displayMessage("danger", response.message || "Login failed. Please check your credentials and try again.");
                }
            } catch (error) {
                displayMessage("danger", error.message || "An unexpected error occurred during login.");
            } finally {
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
