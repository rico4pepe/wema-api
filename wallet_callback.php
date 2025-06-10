<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'classes/Logger.php';

  $logger = new Logger(); // Initialize the logger

/**
 * Handle the incoming wallet generation callback
 *
 * @param Logger $logger The logger instance
 */
function handleWalletCallback($logger)
{
    // Get the callback data from the request
    $jsonInput = file_get_contents('php://input');
    $callbackData = json_decode($jsonInput, true);
    
   // Log the incoming callback data
    $logger->log($callbackData);
    
    
    if (validateCallback($callbackData)) {
        
        // Send success responseå
        http_response_code(200);
         echo json_encode($callbackData, JSON_PRETTY_PRINT);
    } else {
           // Log the error for invalid data
        $logger->log('Invalid callback data: ' . json_encode($callbackData));
        // Send error response for invalid data
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid callback data']);
    }
}


/**
 * Validate the callback data structure
 * 
 * @param array $data The callback data to validate
 * @return bool Whether the callback data is valid
 */
function validateCallback($data)
{
    // Basic validation of required fields
    if (!isset($data['title']) || !isset($data['message']) || 
        !isset($data['data']) || !isset($data['requestType'])) {
        return false;
    }
    
    // Check that the data field has the required properties
    $walletData = $data['data'];
    if (!isset($walletData['email']) || !isset($walletData['nuban']) || 
        !isset($walletData['nubanName']) || !isset($walletData['type']) || 
        !isset($walletData['nubanStatus']) || !isset($walletData['phoneNumber'])) {
        return false;
    }
    
    return true;
}

handleWalletCallback($logger);