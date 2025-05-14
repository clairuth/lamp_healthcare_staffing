<?php
// app/controllers/HomeController.php

class HomeController {

    public function __construct() {
        // Constructor can be used for dependency injection if needed
        // For now, it might not need much for a simple homepage handler
    }

    public function index() {
        // This method would typically prepare data for the homepage
        // and then load a view.
        // For an API-first approach with a plain JS frontend,
        // this might return some basic site information or status.

        // For now, let's return a simple JSON response to confirm it's working.
        // If a view is needed for a server-rendered part or a very basic landing page:
        // load_view("home/index", ["page_title" => "Welcome!"]); 
        // return;

        // Defaulting to JSON as per router setup
        // header("Content-Type: application/json"); // This is now set in index.php
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Welcome to the Healthcare Staffing Platform API!",
            "documentation_url" => "/api/docs" // Example link to API docs
        ]);
    }
}
?>
