    </main>

    <footer class="footer mt-auto py-3 bg-dark text-white">
        <div class="container text-center">
            <span class="text-muted">&copy; <?php echo date("Y"); ?> Healthcare Staffing Platform. All Rights Reserved.</span>
            <p class="mb-0"><a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></p>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Core Custom JavaScript -->
    <script src="<?php echo ASSETS_URL; ?>/js/main.js"></script>

    <!-- Page-specific JavaScript - Load based on CURRENT_PAGE or specific needs -->
    <?php 
    // Example of loading page-specific JS. 
    // You might have a more sophisticated asset management system in a larger app.
    // This simple check assumes CURRENT_PAGE is defined in your router or before including header.
    if (isset($current_page_scripts) && is_array($current_page_scripts)) {
        foreach ($current_page_scripts as $script_name) {
            echo '<script src="' . ASSETS_URL . '/js/' . sanitize_html($script_name) . '.js"></script>\n';
        }
    } else {
        // Fallback or common scripts if $current_page_scripts is not set
        // These are the scripts for dynamic content on various pages.
        // It might be better to load them conditionally based on the page.
        // For simplicity in this example, we load them if not specific scripts are defined.
        // In a real app, you would load these only on pages that need them.
        echo '<script src="' . ASSETS_URL . '/js/profile_management.js"></script>\n';
        echo '<script src="' . ASSETS_URL . '/js/shift_management.js"></script>\n';
        echo '<script src="' . ASSETS_URL . '/js/credential_management.js"></script>\n';
        echo '<script src="' . ASSETS_URL . '/js/skill_assessment.js"></script>\n';
        echo '<script src="' . ASSETS_URL . '/js/payment_management.js"></script>\n';
        echo '<script src="' . ASSETS_URL . '/js/admin_management.js"></script>\n';
    }
    ?>

</body>
</html>

