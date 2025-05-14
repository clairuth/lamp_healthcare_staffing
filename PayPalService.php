<?php
// /home/ubuntu/lamp_healthcare_staffing/implementation/app/services/PayPalService.php

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../config.php';

// It's highly recommended to use the official PayPal PHP SDK via Composer.
// For this environment, we'll simulate the SDK interaction logic.
// In a real setup: require __DIR__ . '/../../vendor/autoload.php';

class PayPalService {
    private $db;
    private $clientId;
    private $clientSecret;
    private $apiBaseUrl;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->clientId = defined('PAYPAL_CLIENT_ID') ? PAYPAL_CLIENT_ID : '';
        $this->clientSecret = defined('PAYPAL_CLIENT_SECRET') ? PAYPAL_CLIENT_SECRET : '';
        $this->apiBaseUrl = defined('PAYPAL_API_BASE_URL') ? PAYPAL_API_BASE_URL : 'https://api.sandbox.paypal.com'; // Default to sandbox

        if (empty($this->clientId) || empty($this->clientSecret)) {
            // Log error or throw exception: PayPal credentials not set
            error_log("PayPal client ID or secret not configured.");
        }
    }

    private function getAccessToken() {
        // In a real SDK, this is handled automatically or via a method.
        // Simulating token retrieval for now.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiBaseUrl . '/v1/oauth2/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_USERPWD, $this->clientId . ':' . $this->clientSecret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Accept-Language: en_US'
        ]);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            error_log('PayPal Access Token cURL error: ' . curl_error($ch));
            curl_close($ch);
            return null;
        }
        curl_close($ch);
        $data = json_decode($result);
        return isset($data->access_token) ? $data->access_token : null;
    }

    public function createOrder($amount, $currency, $returnUrl, $cancelUrl, $description = 'Healthcare Service Payment') {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return ['success' => false, 'message' => 'Failed to get PayPal access token.'];
        }

        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => $amount
                    ],
                    'description' => $description
                ]
            ],
            'application_context' => [
                'return_url' => $returnUrl, // e.g., SITE_URL . '/payment_success.php'
                'cancel_url' => $cancelUrl, // e.g., SITE_URL . '/payment_cancel.php'
                'brand_name' => 'Healthcare Staffing Platform',
                'user_action' => 'PAY_NOW'
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiBaseUrl . '/v2/checkout/orders');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
            // 'PayPal-Request-Id: ' . uniqid() // Optional for idempotency
        ]);

        $result = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            error_log('PayPal Create Order cURL error: ' . curl_error($ch));
            curl_close($ch);
            return ['success' => false, 'message' => 'cURL error during order creation.'];
        }
        curl_close($ch);

        $data = json_decode($result, true);

        if ($httpStatusCode >= 200 && $httpStatusCode < 300 && isset($data['id']) && isset($data['links'])) {
            $approvalUrl = null;
            foreach ($data['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    $approvalUrl = $link['href'];
                    break;
                }
            }
            if ($approvalUrl) {
                return ['success' => true, 'order_id' => $data['id'], 'approval_url' => $approvalUrl, 'response' => $data];
            } else {
                return ['success' => false, 'message' => 'Approval URL not found in PayPal response.', 'response' => $data];
            }
        } else {
            error_log('PayPal Create Order API error: ' . $result);
            return ['success' => false, 'message' => 'Failed to create PayPal order.', 'response' => $data, 'status_code' => $httpStatusCode];
        }
    }

    public function captureOrder($orderId) {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return ['success' => false, 'message' => 'Failed to get PayPal access token.'];
        }

        $ch = curl_init();
        // The endpoint for capturing an order is /v2/checkout/orders/{order_id}/capture
        curl_setopt($ch, CURLOPT_URL, $this->apiBaseUrl . '/v2/checkout/orders/' . $orderId . '/capture');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        // Capture request typically has an empty body or specific headers if needed
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{}'); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
            // 'PayPal-Request-Id: ' . uniqid() // Optional for idempotency
        ]);

        $result = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            error_log('PayPal Capture Order cURL error: ' . curl_error($ch));
            curl_close($ch);
            return ['success' => false, 'message' => 'cURL error during order capture.'];
        }
        curl_close($ch);

        $data = json_decode($result, true);

        if ($httpStatusCode >= 200 && $httpStatusCode < 300 && isset($data['status']) && $data['status'] === 'COMPLETED') {
            return ['success' => true, 'capture_details' => $data];
        } else {
            error_log('PayPal Capture Order API error: ' . $result);
            // Attempt to get a more specific error message from PayPal's response
            $errorMessage = 'Failed to capture PayPal order.';
            if (isset($data['message'])) {
                $errorMessage = $data['message'];
            } elseif (isset($data['details'][0]['description'])) {
                $errorMessage = $data['details'][0]['description'];
            }
            return ['success' => false, 'message' => $errorMessage, 'response' => $data, 'status_code' => $httpStatusCode];
        }
    }

    // Placeholder for Payouts - this would use the Payouts API
    public function createPayout($recipientEmail, $amount, $currency, $note = 'Shift Payment') {
        $accessToken = $this->getAccessToken(); // Payouts API might use different auth or scopes
        if (!$accessToken) {
            return ['success' => false, 'message' => 'Failed to get PayPal access token for payout.'];
        }

        // This is a simplified representation. The Payouts API has a specific structure.
        // See: https://developer.paypal.com/docs/payouts/
        $payload = [
            'sender_batch_header' => [
                'sender_batch_id' => 'Payouts_' . uniqid(),
                'email_subject' => 'You have a pPpayment!',
                'email_message' => 'You have received a payment for your completed shift.'
            ],
            'items' => [
                [
                    'recipient_type' => 'EMAIL',
                    'amount' => [
                        'value' => $amount,
                        'currency' => $currency
                    ],
                    'note' => $note,
                    'sender_item_id' => 'item_' . uniqid(),
                    'receiver' => $recipientEmail
                ]
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiBaseUrl . '/v1/payments/payouts'); // Payouts API endpoint
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ]);

        $result = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = json_decode($result, true);

        if ($httpStatusCode === 201 && isset($data['batch_header']['payout_batch_id'])) { // 201 Created for payouts
            return ['success' => true, 'payout_batch_id' => $data['batch_header']['payout_batch_id'], 'response' => $data];
        } else {
            error_log('PayPal Create Payout API error: ' . $result);
            return ['success' => false, 'message' => 'Failed to create PayPal payout.', 'response' => $data, 'status_code' => $httpStatusCode];
        }
    }
}
?>
