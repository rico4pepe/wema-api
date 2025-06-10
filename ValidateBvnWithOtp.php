<?php




require_once __DIR__ . DIRECTORY_SEPARATOR . '/classes/config.php';
require_once __DIR__ . DIRECTORY_SEPARATOR .  'classes/HttpClient.php';
require_once __DIR__ . DIRECTORY_SEPARATOR .  'classes/Logger.php';
require_once __DIR__ . DIRECTORY_SEPARATOR .  'classes/ValidateBvnWithOtpService.php';




// Load configuration
// Load configuratio
$config = [
    'base_url' => BASE_URL,
    'x-api-key' => API_KEY,
    'primary_subscription_key' => PRIMARY_SUBSCRIPTION_KEY,
    'secondary_subscription_key' => SECONDARY_SUBSCRIPTION_KEY
];


try {
      $logger = new Logger(); // Initialize the logger
    $httpClient = new HttpClient($config, $logger);

     // Initialize the ValidateBvnWithOtpService with the HttpClient
    $accountservice = new ValidateBvnWithOtpService($httpClient);

    // Get service status as JSON response
    $status = $accountservice->generateAccount();

    // Output the result in JSON format (for debugging/response handling)
    echo json_encode($status, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    // Log the exception message
    $logger->log('Error: ' . $e->getMessage());
    // Handle exceptions gracefully and return an error message in JSON format
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT);
}
