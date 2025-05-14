<?php
// templates/auth/register.php

$page_title = "Register - StaffingPlus";
require_once APP_ROOT . "/templates/header.php";

$user_role = $_GET["role"] ?? "professional"; // Default to professional, can be professional or facility

?>

<div class="auth-container">
    <h2 class="text-center mb-4">Create Your Account</h2>
    <p class="text-center mb-3">Register as a 
        <strong><?php echo htmlspecialchars(ucfirst($user_role)); ?></strong>
    </p>

    <div id="messageContainer"></div>

    <form id="registerForm" class="needs-validation" novalidate>
        <input type="hidden" name="user_type" value="<?php echo htmlspecialchars($user_role); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" name="username" required>
            <div class="invalid-feedback">Please choose a username.</div>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" class="form-control" id="email" name="email" required>
            <div class="invalid-feedback">Please enter a valid email address.</div>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" required minlength="8">
            <div class="invalid-feedback">Password must be at least 8 characters.</div>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            <div class="invalid-feedback">Passwords do not match.</div>
        </div>

        <?php if ($user_role === "professional"): ?>
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="license_number">License Number (e.g., RN, CNA)</label>
                <input type="text" class="form-control" id="license_number" name="license_number">
            </div>
             <div class="form-group">
                <label for="phone_number">Phone Number</label>
                <input type="tel" class="form-control" id="phone_number" name="phone_number">
            </div>
        <?php elseif ($user_role === "facility"): ?>
            <div class="form-group">
                <label for="facility_name">Facility Name</label>
                <input type="text" class="form-control" id="facility_name" name="facility_name" required>
            </div>
            <div class="form-group">
                <label for="facility_type">Facility Type</label>
                <select class="form-control" id="facility_type" name="facility_type_id" required>
                    <option value="">Select Type...</option>
                    <!-- Options will be populated by JS or hardcoded if few -->
                    <option value="1">Hospital</option>
                    <option value="2">Clinic</option>
                    <option value="3">Nursing Home</option>
                    <option value="4">Assisted Living</option>
                    <option value="5">Other</option>
                </select>
            </div>
             <div class="form-group">
                <label for="contact_person">Contact Person</label>
                <input type="text" class="form-control" id="contact_person" name="contact_person">
            </div>
             <div class="form-group">
                <label for="facility_phone_number">Facility Phone Number</label>
                <input type="tel" class="form-control" id="facility_phone_number" name="facility_phone_number">
            </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary btn-full-width mt-3">Register</button>
    </form>

    <p class="text-center mt-3">
        Already have an account? <a href="<?php echo base_url("auth/login"); ?>">Login here</a>
    </p>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const registerForm = document.getElementById("registerForm");
    const passwordInput = document.getElementById("password");
    const confirmPasswordInput = document.getElementById("confirm_password");

    if (registerForm) {
        registerForm.addEventListener("submit", async function(event) {
            event.preventDefault();
            event.stopPropagation();

            if (passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.classList.add("is-invalid");
                confirmPasswordInput.nextElementSibling.textContent = "Passwords do not match.";
                displayMessage("danger", "Passwords do not match. Please check and try again.");
                return;
            }
            confirmPasswordInput.classList.remove("is-invalid");

            if (!registerForm.checkValidity()) {
                registerForm.classList.add("was-validated");
                displayMessage("danger", "Please fill in all required fields correctly.");
                return;
            }

            const formData = new FormData(registerForm);
            const data = Object.fromEntries(formData.entries());

            // Add a loading indicator
            const submitButton = registerForm.querySelector("button[type=\"submit\"]");
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = 
            submitButton.disabled = true;

            try {
                const response = await fetchData("<?php echo base_url("auth/register"); ?>", {
                    method: "POST",
                    body: JSON.stringify(data)
                });

                if (response.status === "success") {
                    displayMessage("success", response.message + " Redirecting to login...");
                    setTimeout(() => {
                        window.location.href = "<?php echo base_url("auth/login"); ?>";
                    }, 2000);
                } else {
                    displayMessage("danger", response.message || "Registration failed. Please try again.");
                }
            } catch (error) {
                displayMessage("danger", error.message || "An unexpected error occurred during registration.");
            } finally {
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
            }
        });
    }

    // Real-time password confirmation validation
    if (passwordInput && confirmPasswordInput) {
        confirmPasswordInput.addEventListener("input", function() {
            if (passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.setCustomValidity("Passwords do not match.");
                confirmPasswordInput.classList.add("is-invalid");
                confirmPasswordInput.nextElementSibling.textContent = "Passwords do not match.";
            } else {
                confirmPasswordInput.setCustomValidity("");
                confirmPasswordInput.classList.remove("is-invalid");
            }
        });
        passwordInput.addEventListener("input", function() {
             // Re-validate confirm_password if password changes
            if (confirmPasswordInput.value !== "") {
                 if (passwordInput.value !== confirmPasswordInput.value) {
                    confirmPasswordInput.setCustomValidity("Passwords do not match.");
                    confirmPasswordInput.classList.add("is-invalid");
                    confirmPasswordInput.nextElementSibling.textContent = "Passwords do not match.";
                } else {
                    confirmPasswordInput.setCustomValidity("");
                    confirmPasswordInput.classList.remove("is-invalid");
                }
            }
        });
    }
});
</script>

<?php
require_once APP_ROOT . "/templates/footer.php";
?>
