<?php
// templates/home.php

// Set the page title (can be overridden by controller)
$page_title = "Welcome to StaffingPlus - Your Healthcare Staffing Solution";

// Include the header
require_once APP_ROOT . "/templates/header.php";

?>

<div class="hero-section text-center">
    <div class="container">
        <h1>Find Your Next Healthcare Opportunity</h1>
        <p class="lead">Connecting qualified professionals with leading healthcare facilities. Flexible shifts, reliable staffing.</p>
        <div class="cta-buttons mt-3">
            <a href="<?php echo base_url("shift/listOpen"); ?>" class="btn btn-primary btn-lg">Find Shifts</a>
            <a href="<?php echo base_url("auth/register?role=facility"); ?>" class="btn btn-secondary btn-lg">Post a Job</a>
        </div>
    </div>
</div>

<section class="how-it-works-section pt-5 pb-5">
    <div class="container">
        <h2 class="text-center mb-4">How It Works</h2>
        <div class="row">
            <div class="col-md-6">
                <div class="card feature-card">
                    <div class="card-body text-center">
                        <div class="icon mb-3"><i class="fas fa-user-md fa-3x text-primary"></i></div> <!-- Placeholder icon -->
                        <h3>For Healthcare Professionals</h3>
                        <p>Sign up, complete your profile, upload your credentials, and start applying for shifts that fit your schedule and expertise.</p>
                        <a href="<?php echo base_url("auth/register?role=professional"); ?>" class="btn btn-outline-primary">Register as Professional</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card feature-card">
                    <div class="card-body text-center">
                        <div class="icon mb-3"><i class="fas fa-hospital fa-3x text-primary"></i></div> <!-- Placeholder icon -->
                        <h3>For Healthcare Facilities</h3>
                        <p>Register your facility, post available shifts, and connect with a pool of verified and skilled healthcare professionals quickly.</p>
                        <a href="<?php echo base_url("auth/register?role=facility"); ?>" class="btn btn-outline-primary">Register as Facility</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="featured-shifts-section pt-5 pb-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4">Featured Shifts</h2>
        <div id="featuredShiftsContainer" class="dashboard-grid">
            <!-- Shifts will be loaded here by JavaScript or PHP -->
            <p class="text-center">Loading featured shifts...</p>
        </div>
        <div class="text-center mt-4">
            <a href="<?php echo base_url("shift/listOpen"); ?>" class="btn btn-primary">View All Open Shifts</a>
        </div>
    </div>
</section>

<section class="testimonials-section pt-5 pb-5">
    <div class="container">
        <h2 class="text-center mb-4">What Our Users Say</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="card testimonial-card">
                    <div class="card-body">
                        <p class="testimonial-text">"This platform made it so easy to find per diem shifts that fit my schedule. Highly recommended!"</p>
                        <p class="testimonial-author">- Sarah M., RN</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card testimonial-card">
                    <div class="card-body">
                        <p class="testimonial-text">"We filled several critical shifts within hours. The quality of professionals is excellent."</p>
                        <p class="testimonial-author">- John B., Facility Manager</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card testimonial-card">
                    <div class="card-body">
                        <p class="testimonial-text">"The credential verification process is straightforward and gives me peace of mind."</p>
                        <p class="testimonial-author">- David K., CNA</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include the footer
require_once APP_ROOT . "/templates/footer.php";
?>

<script>
// Basic script to load featured shifts (example)
document.addEventListener("DOMContentLoaded", function() {
    const featuredShiftsContainer = document.getElementById("featuredShiftsContainer");
    if (featuredShiftsContainer) {
        fetchData("<?php echo base_url("shift/listOpen?limit=3&featured=true"); ?>") // Assuming an endpoint for featured shifts
            .then(response => {
                if (response.status === "success" && response.data.length > 0) {
                    featuredShiftsContainer.innerHTML = ""; // Clear loading text
                    response.data.forEach(shift => {
                        const shiftCard = `
                            <div class="card shift-list-item">
                                <div class="card-body">
                                    <h5 class="shift-title">${sanitizeHTML(shift.shift_title)}</h5>
                                    <p class="shift-meta"><strong>Facility:</strong> ${sanitizeHTML(shift.facility_name)}</p>
                                    <p class="shift-meta"><strong>Date:</strong> ${new Date(shift.shift_date).toLocaleDateString()}</p>
                                    <p class="shift-meta"><strong>Time:</strong> ${sanitizeHTML(shift.start_time)} - ${sanitizeHTML(shift.end_time)}</p>
                                    <a href="<?php echo base_url("shift/view/"); ?>${shift.id}" class="btn btn-sm btn-outline-primary">View Details</a>
                                </div>
                            </div>
                        `;
                        featuredShiftsContainer.innerHTML += shiftCard;
                    });
                } else {
                    featuredShiftsContainer.innerHTML = "<p class=\"text-center\">No featured shifts available at the moment.</p>";
                }
            })
            .catch(error => {
                console.error("Error fetching featured shifts:", error);
                featuredShiftsContainer.innerHTML = "<p class=\"text-center text-danger\">Could not load featured shifts.</p>";
            });
    }
});

function sanitizeHTML(str) {
    if (!str) return "";
    const temp = document.createElement("div");
    temp.textContent = str;
    return temp.innerHTML;
}
</script>

<style>
.hero-section {
    background: #e9ecef; /* Light grey background */
    padding: 4rem 0;
    margin-bottom: 2rem;
}
.hero-section h1 {
    font-size: 2.8rem;
    font-weight: 700;
    color: #343a40;
}
.hero-section .lead {
    font-size: 1.25rem;
    color: #6c757d;
    margin-bottom: 1.5rem;
}
.cta-buttons .btn {
    margin: 0 0.5rem;
    padding: 0.8rem 1.8rem;
    font-size: 1.1rem;
}
.feature-card .icon i {
    font-size: 3rem; /* Ensure FontAwesome is linked or use SVG/images */
}
.testimonial-card {
    background-color: #f8f9fa;
}
.testimonial-text {
    font-style: italic;
    margin-bottom: 1rem;
}
.testimonial-author {
    font-weight: bold;
    text-align: right;
    color: #555;
}
.bg-light {
    background-color: #f8f9fa !important;
}
</style>

