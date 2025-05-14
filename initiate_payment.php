<?php
// /home/ubuntu/lamp_healthcare_staffing/implementation/templates/payments/initiate_payment.php
// This is a conceptual page. In a real application, this flow would likely be integrated
// into a shift management page where a facility pays for a completed or booked shift.

require_once __DIR__ . 
'/../../app/controllers/AuthController.php

';
require_once __DIR__ . 
'/../../app/controllers/ShiftController.php

';
require_once __DIR__ . 
'/../../app/controllers/PaymentController.php

';

$authController = new AuthController();
$authController->ensureLoggedIn();
$authController->checkRole([
'facility

']); // Only facilities can initiate payments for shifts

$user = $_SESSION[
'user

'];
$shiftController = new ShiftController();
$paymentController = new PaymentController();

$shiftId = isset($_GET[
'shift_id

']) ? (int)$_GET[
'shift_id

'] : null;
$shiftDetails = null;
$paymentError = 
''
;
$paymentSuccess = 
''
;

if ($shiftId) {
    // In a real app, fetch full shift details, including amount due
    // $shiftDetails = $shiftController->getShiftDetailsForPayment($shiftId, $user[
'id

']);
    // For demonstration, let's assume some details:
    $shiftDetails = [
        
'id

' => $shiftId,
        
'title

' => 
'Shift #

' . $shiftId . 
' - Evening Nurse Coverage

',
        
'amount_due

' => 150.00, // Example amount
        
'currency

' => 
'USD

'
    ];
} else {
    // Redirect or show error if no shift_id is provided
    header(
'Location: 

' . SITE_URL . 
'/templates/shifts/facility_shifts.php

');
    exit;
}

if ($_SERVER[
'REQUEST_METHOD

'] === 
'POST

' && isset($_POST[
'payment_method

']) && $shiftDetails) {
    $paymentMethod = $_POST[
'payment_method

'];
    $amount = $shiftDetails[
'amount_due

'];
    $currency = $shiftDetails[
'currency

'];
    $description = 
'Payment for Shift ID: 

' . $shiftId;

    // This is where you would call the appropriate service based on $paymentMethod
    // For example, if $paymentMethod === 'paypal', call PayPalService->createOrder()
    // Then redirect the user to PayPal or handle the response.
    // For Square/CashApp, you'd need client-side JS to generate a nonce first.

    // Simulate a payment processing attempt
    $result = $paymentController->processPaymentRequest($user[
'id

'], $shiftId, $amount, $currency, $paymentMethod, $description);

    if (isset($result[
'success

']) && $result[
'success

']) {
        if (isset($result[
'approval_url

'])) { // For PayPal, Coinbase redirect
            header(
'Location: 

' . $result[
'approval_url

']);
            exit;
        } elseif (isset($result[
'payment_details

'])) { // For direct success like Square (if nonce handled)
            $paymentSuccess = "Payment initiated successfully! Transaction ID: " . ($result[
'payment_details

'][
'id

'] ?? 
'N/A

');
        } else {
            $paymentSuccess = $result[
'message

'] ?? 
'Payment processing started.

';
        }
    } else {
        $paymentError = $result[
'message

'] ?? 
'Payment processing failed. Please try again.

';
    }
}

$pageTitle = "Initiate Payment for Shift";
include __DIR__ . 
'/../header.php


';
?>

<div class="container mt-5">
    <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
    <hr>

    <?php if ($paymentError): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($paymentError); ?></div>
    <?php endif; ?>
    <?php if ($paymentSuccess): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($paymentSuccess); ?></div>
    <?php endif; ?>

    <?php if ($shiftDetails && !$paymentSuccess): // Don't show form if payment was successful ?>
        <div class="card mb-4">
            <div class="card-header">
                <h4>Shift Details</h4>
            </div>
            <div class="card-body">
                <p><strong>Shift:</strong> <?php echo htmlspecialchars($shiftDetails[
'title

']); ?></p>
                <p><strong>Amount Due:</strong> <?php echo htmlspecialchars(number_format($shiftDetails[
'amount_due

'], 2)); ?> <?php echo htmlspecialchars($shiftDetails[
'currency

']); ?></p>
            </div>
        </div>

        <h4>Select Payment Method</h4>
        <form method="POST" action="">
            <input type="hidden" name="shift_id" value="<?php echo htmlspecialchars($shiftId); ?>">
            
            <div class="list-group">
                <label class="list-group-item">
                    <input class="form-check-input me-1" type="radio" name="payment_method" value="paypal" required> PayPal
                </label>
                <label class="list-group-item">
                    <input class="form-check-input me-1" type="radio" name="payment_method" value="cashapp_square"> Cash App (via Square)
                </label>
                <label class="list-group-item">
                    <input class="form-check-input me-1" type="radio" name="payment_method" value="coinbase"> Coinbase (Cryptocurrency)
                </label>
                <label class="list-group-item">
                    <input class="form-check-input me-1" type="radio" name="payment_method" value="zelle"> Zelle
                </label>
            </div>

            <!-- For Square/CashApp, client-side JS would generate a payment nonce and submit it -->
            <!-- This form is simplified for demonstration -->

            <button type="submit" class="btn btn-primary mt-3">Proceed to Payment</button>
        </form>
        
        <?php if (isset($_POST[
'payment_method

']) && $_POST[
'payment_method

'] === 
'zelle

' && !$paymentError && !$paymentSuccess): ?>
        <div class="alert alert-info mt-3">
            <strong>Zelle Payment Instructions:</strong><br>
            Please send <?php echo htmlspecialchars(number_format($shiftDetails[
'amount_due

'], 2)); ?> <?php echo htmlspecialchars($shiftDetails[
'currency

']); ?> via Zelle to:
            <strong><?php echo htmlspecialchars(defined(
'ZELLE_RECIPIENT_INFO


') ? ZELLE_RECIPIENT_INFO : 
'Not Configured


'); ?></strong>.<br>
            Include Shift ID <strong><?php echo htmlspecialchars($shiftId); ?></strong> in the payment memo.
            After sending, our team will manually verify the payment. This may take some time.
            <form method="POST" action="<?php echo SITE_URL; ?>/app/controllers/PaymentController.php?action=record_zelle_intent">
                <input type="hidden" name="shift_id" value="<?php echo htmlspecialchars($shiftId); ?>">
                <input type="hidden" name="amount" value="<?php echo htmlspecialchars($shiftDetails[
'amount_due

']); ?>">
                <input type="hidden" name="currency" value="<?php echo htmlspecialchars($shiftDetails[
'currency

']); ?>">
                <button type="submit" class="btn btn-info btn-sm mt-2">I have sent/will send the Zelle payment</button>
            </form>
        </div>
        <?php endif; ?>

    <?php elseif (!$shiftDetails): ?>
        <div class="alert alert-warning">Shift details not found or you do not have permission to pay for this shift.</div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="<?php echo SITE_URL; ?>/templates/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<?php include __DIR__ . 
'/../footer.php


'; ?>

