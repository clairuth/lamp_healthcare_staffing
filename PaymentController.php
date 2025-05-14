<?php
// app/controllers/PaymentController.php

require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/PaymentMethod.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Shift.php';
require_once __DIR__ . '/../includes/security_functions.php';

class PaymentController {
    private $db;
    private $paymentModel;
    private $paymentMethodModel;
    private $userModel;
    private $shiftModel;

    public function __construct($db) {
        $this->db = $db;
        $this->paymentModel = new Payment($db);
        $this->paymentMethodModel = new PaymentMethod($db);
        $this->userModel = new User($db);
        $this->shiftModel = new Shift($db);
    }

    // ---- Payment Method Management ----

    public function manageMethods() {
        if (!is_logged_in()) {
            redirect(BASE_URL . 'auth/login');
        }
        $user_id = $_SESSION['user_id'];
        $data['payment_methods'] = $this->paymentMethodModel->getByUserId($user_id);
        $data['page_title'] = "Manage Payment Methods";
        $data['current_page_scripts'] = ['payment_management'];
        load_view('payments/manage_payment_methods', $data);
    }

    public function addMethod() {
        if (!is_logged_in()) {
            json_response(["status" => "error", "message" => "User not logged in."], 401);
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize and validate input
            $user_id = $_SESSION['user_id'];
            $method_name = sanitize_input($_POST['method_name'] ?? '');
            $provider = sanitize_input($_POST['provider'] ?? '');
            $account_details = sanitize_input($_POST['account_details'] ?? ''); // E.g., PayPal email, CashApp $cashtag, Coinbase email, Zelle phone/email
            $is_default = isset($_POST['is_default']) ? 1 : 0;

            if (empty($method_name) || empty($provider) || empty($account_details)) {
                json_response(["status" => "error", "message" => "All fields are required."], 400);
                return;
            }
            
            // Specific validation for account details based on provider
            if ($provider === 'PayPal' && !filter_var($account_details, FILTER_VALIDATE_EMAIL)) {
                 json_response(["status" => "error", "message" => "Invalid PayPal email address."], 400); return;
            }
            if ($provider === 'CashApp' && (strpos($account_details, '$') !== 0 || strlen($account_details) < 2)) {
                 json_response(["status" => "error", "message" => "Invalid CashApp $cashtag."], 400); return;
            }
            if ($provider === 'Coinbase' && !filter_var($account_details, FILTER_VALIDATE_EMAIL)) {
                 json_response(["status" => "error", "message" => "Invalid Coinbase email address."], 400); return;
            }
            // Zelle can be email or phone - more complex validation might be needed
            if ($provider === 'Zelle' && !(filter_var($account_details, FILTER_VALIDATE_EMAIL) || preg_match('/^[0-9\-\s\(\)]+$/', $account_details))) {
                 json_response(["status" => "error", "message" => "Invalid Zelle email or phone number."], 400); return;
            }

            $result = $this->paymentMethodModel->create($user_id, $method_name, $provider, $account_details, $is_default);
            json_response($result, $result['status'] === 'success' ? 201 : 400);
        } else {
            json_response(["status" => "error", "message" => "Invalid request method."], 405);
        }
    }

    public function deleteMethod($method_id) {
        if (!is_logged_in()) {
            json_response(["status" => "error", "message" => "User not logged in."], 401);
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Should be DELETE, but forms often use POST
            $user_id = $_SESSION['user_id'];
            $result = $this->paymentMethodModel->delete($method_id, $user_id);
            json_response($result, $result['status'] === 'success' ? 200 : 400);
        } else {
            json_response(["status" => "error", "message" => "Invalid request method."], 405);
        }
    }
    
    public function setDefaultMethod($method_id) {
        if (!is_logged_in()) {
            json_response(["status" => "error", "message" => "User not logged in."], 401);
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            $result = $this->paymentMethodModel->setDefault($method_id, $user_id);
            json_response($result, $result['status'] === 'success' ? 200 : 400);
        } else {
            json_response(["status" => "error", "message" => "Invalid request method."], 405);
        }
    }

    // ---- Payment Processing & Escrow ----

    // Facility initiates payment for a completed shift
    public function initiateShiftPayment() {
        if (!is_logged_in() || !is_facility_admin()) { // Assuming is_facility_admin() checks user role
            json_response(["status" => "error", "message" => "Unauthorized access."], 403);
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $facility_id = $_SESSION['user_id'];
            $shift_id = sanitize_input($_POST['shift_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
            $professional_id = sanitize_input($_POST['professional_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
            $amount = sanitize_input($_POST['amount'] ?? 0.0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $facility_payment_method_id = sanitize_input($_POST['facility_payment_method_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);

            // Validate shift, professional, amount, and payment method
            $shift = $this->shiftModel->getById($shift_id);
            if (!$shift || $shift['facility_user_id'] != $facility_id) {
                json_response(["status" => "error", "message" => "Invalid shift or unauthorized."], 400);
                return;
            }
            // Ensure shift was completed by this professional (logic might be in ShiftApplication model)
            // For now, assume professional_id is correct for the shift

            if ($amount <= 0) {
                json_response(["status" => "error", "message" => "Invalid payment amount."], 400);
                return;
            }
            
            // TODO: Actual payment gateway integration (e.g., charge facility's card/account)
            // This part would involve calling PayPal/Stripe/etc. API to process the payment from facility
            // For now, we simulate a successful charge and record the transaction_id
            $external_transaction_id = "SIM_CHARGE_" . uniqid();
            $payment_processed_externally = true; // Simulate successful external charge

            if ($payment_processed_externally) {
                // Create payment record in our system, status 'escrow'
                $result = $this->paymentModel->create($professional_id, $facility_id, $shift_id, $amount, $facility_payment_method_id, "USD", $external_transaction_id, "escrow");
                json_response($result, $result['status'] === 'success' ? 201 : 400);
            } else {
                json_response(["status" => "error", "message" => "External payment processing failed."], 500);
            }
        } else {
            json_response(["status" => "error", "message" => "Invalid request method."], 405);
        }
    }

    // Release payment from escrow (e.g., automatically after X days, or by admin)
    public function releaseEscrowPayment($payment_id) {
        // This should typically be an admin action or an automated system (cron job)
        if (!is_logged_in() || !is_admin()) { 
            json_response(["status" => "error", "message" => "Unauthorized access."], 403);
            return;
        }
        
        $payment = $this->paymentModel->getById($payment_id);
        if (!$payment || $payment['status'] !== 'escrow') {
            json_response(["status" => "error", "message" => "Payment not found or not in escrow."], 404);
            return;
        }

        // TODO: Actual payout to professional's chosen method (PayPal, CashApp, Coinbase, Zelle)
        // This would involve API calls to the respective payment providers.
        // For Zelle/CashApp, it might be more of a notification if direct API payout isn't available.
        $payout_successful_externally = true; // Simulate successful payout
        $payout_transaction_id = "SIM_PAYOUT_" . uniqid();

        if ($payout_successful_externally) {
            $result = $this->paymentModel->updateStatus($payment_id, "completed", "Released from escrow. Payout ID: " . $payout_transaction_id);
            // TODO: Notify professional of payment release
            json_response($result, $result['status'] === 'success' ? 200 : 400);
        } else {
            // If payout fails, payment might remain in escrow or be marked for review
            $this->paymentModel->updateStatus($payment_id, "escrow_release_failed", "Attempted payout failed.");
            json_response(["status" => "error", "message" => "External payout processing failed."], 500);
        }
    }

    // ---- Transaction History ----

    public function transactionHistory() {
        if (!is_logged_in()) {
            redirect(BASE_URL . 'auth/login');
        }
        $user_id = $_SESSION['user_id'];
        $user_role = $_SESSION['role']; // Assuming role is stored in session

        if ($user_role === 'professional') {
            $data['transactions'] = $this->paymentModel->getByProfessional($user_id);
        } elseif ($user_role === 'facility_admin') {
            $data['transactions'] = $this->paymentModel->getByFacility($user_id);
        } else { // Platform admin or other roles might see all or none
            $data['transactions'] = []; // Or redirect, or show all for admin
        }
        
        $data['page_title'] = "Transaction History";
        $data['current_page_scripts'] = ['payment_management']; // JS for formatting, filtering etc.
        load_view('payments/transaction_history', $data);
    }

    // ---- Admin Payment Overview ----
    public function adminPaymentsList() {
        if (!is_logged_in() || !is_admin()) {
            redirect(BASE_URL . 'auth/login');
        }
        // Basic pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $data['payments'] = $this->paymentModel->getAll($limit, $offset);
        $total_payments = $this->paymentModel->countAll();
        $data['total_pages'] = ceil($total_payments / $limit);
        $data['current_page'] = $page;

        $data['page_title'] = "All Payments (Admin)";
        $data['current_page_scripts'] = ['admin_management']; // For table interactions
        load_view('admin/manage_payments', $data); // New template needed
    }

    // TODO: Methods for handling payment disputes, refunds, etc.
    // TODO: Cron job for automatic escrow release after a defined period (e.g., 3 days after shift completion if no dispute)
}

