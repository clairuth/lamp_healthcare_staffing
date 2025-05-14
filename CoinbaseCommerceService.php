<?php
// /home/ubuntu/lamp_healthcare_staffing/implementation/app/services/CoinbaseCommerceService.php

require_once __DIR__ . 
'/../../core/Database.php

';
require_once __DIR__ . 
'/../../config.php

';

// In a real setup with Coinbase Commerce PHP SDK: require __DIR__ . 
'/../../vendor/autoload.php

';
// use CoinbaseCommerce\ApiClient;
// use CoinbaseCommerce\Resources\Charge;

class CoinbaseCommerceService {
    private $db;
    private $apiKey;
    private $apiBaseUrl;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->apiKey = defined(
'COINBASE_COMMERCE_API_KEY

') ? COINBASE_COMMERCE_API_KEY : 
''
;
        $this->apiBaseUrl = defined(
'COINBASE_COMMERCE_API_BASE_URL

') ? COINBASE_COMMERCE_API_BASE_URL : 
'https://api.commerce.coinbase.com

';

        if (empty($this->apiKey)) {
            error_log("Coinbase Commerce API Key not configured.");
            // In a real app, throw an exception or handle this more gracefully
        }

        /* // SDK Initialization Example
        if (!empty($this->apiKey)) {
            ApiClient::init($this->apiKey);
        }
        */
    }

    /**
     * Creates a charge using the Coinbase Commerce API.
     *
     * @param float $amount The amount for the charge.
     * @param string $currency The currency code (e.g., 'USD').
     * @param string $name The name of the product or service.
     * @param string $description A brief description.
     * @param string $redirectUrl URL to redirect after successful payment.
     * @param string $cancelUrl URL to redirect after payment cancellation.
     * @param array $metadata Optional metadata to associate with the charge.
     * @return array Result of the charge creation attempt.
     */
    public function createCharge($amount, $currency, $name, $description, $redirectUrl, $cancelUrl, $metadata = []) {
        if (empty($this->apiKey)) {
            return ["success" => false, "message" => "Coinbase Commerce API Key not configured."];
        }

        $payload = [
            'name' => $name,
            'description' => $description,
            'local_price' => [
                'amount' => $amount,
                'currency' => $currency
            ],
            'pricing_type' => 'fixed_price',
            'redirect_url' => $redirectUrl, // e.g., SITE_URL . '/coinbase_success.php'
            'cancel_url' => $cancelUrl,   // e.g., SITE_URL . '/coinbase_cancel.php'
            'metadata' => $metadata // e.g., [ 'shift_id' => '123', 'user_id' => '456' ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiBaseUrl . '/charges');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-CC-Api-Key: ' . $this->apiKey,
            'X-CC-Version: 2018-03-22' // Specify API version
        ]);

        $result = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            error_log('Coinbase CreateCharge cURL error: ' . curl_error($ch));
            curl_close($ch);
            return ["success" => false, "message" => "cURL error during Coinbase charge creation."];
        }
        curl_close($ch);

        $data = json_decode($result, true);

        if ($httpStatusCode >= 200 && $httpStatusCode < 300 && isset($data['data']['hosted_url'])) {
            // Charge created successfully
            return ["success" => true, "charge_details" => $data['data']];
        } else {
            $errorMessage = 'Failed to create Coinbase charge.';
            if (isset($data['error']['message'])) {
                $errorMessage = $data['error']['message'];
            } else if (isset($data['errors']) && is_array($data['errors'])) {
                $errorMessages = [];
                foreach ($data['errors'] as $error) {
                    $errorMessages[] = $error['message'];
                }
                $errorMessage = implode('; ', $errorMessages);
            }
            error_log('Coinbase CreateCharge API error: ' . $result);
            return ["success" => false, "message" => $errorMessage, "response" => $data, "status_code" => $httpStatusCode];
        }
    }

    /**
     * Retrieves a charge by its ID or code.
     *
     * @param string $chargeId The ID or code of the charge.
     * @return array Result of the retrieval attempt.
     */
    public function getCharge($chargeId) {
        if (empty($this->apiKey)) {
            return ["success" => false, "message" => "Coinbase Commerce API Key not configured."];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiBaseUrl . '/charges/' . $chargeId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'X-CC-Api-Key: ' . $this->apiKey,
            'X-CC-Version: 2018-03-22'
        ]);

        $result = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = json_decode($result, true);

        if ($httpStatusCode === 200 && isset($data['data'])) {
            return ["success" => true, "charge_details" => $data['data']];
        } else {
            error_log('Coinbase GetCharge API error: ' . $result);
            return ["success" => false, "message" => "Failed to retrieve Coinbase charge.", "response" => $data, "status_code" => $httpStatusCode];
        }
    }

    // Webhook handling would be a separate endpoint in your application (e.g., /webhook/coinbase.php)
    // It would verify the signature and process events like 'charge:confirmed', 'charge:failed'.
    // public function verifyWebhookSignature($payload, $signatureHeader, $webhookSecret) { ... }
}
?>
