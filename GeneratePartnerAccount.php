<?php




require_once __DIR__ . DIRECTORY_SEPARATOR . '/classes/config.php';
require_once __DIR__ . DIRECTORY_SEPARATOR .  'classes/HttpClient.php';
require_once __DIR__ . DIRECTORY_SEPARATOR .  'classes/GeneratePartnerAccountService.php';




// Load configuration
$config = [
    'base_url' => BASE_URL,
    'api_key' => API_KEY,
    
];


try {
    $httpClient = new HttpClient($config);
    $accountservice = new GeneratePartnerAccountService($httpClient);

    // Get service status as JSON response
    $status = $accountservice->generateAccount();

    // Output the result in JSON format (for debugging/response handling)
    echo json_encode($status, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    // Handle exceptions gracefully and return an error message in JSON format
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT);
}
