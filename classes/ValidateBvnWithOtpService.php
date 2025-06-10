<?php

class ValidateBvnWithOtpService
{
    private $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Process account generation request
     *
     * @param array|null $inputData Input data from JSON body or POST
     * @return array Processed result or validation errors
     */
    public function generateAccount($inputData = null): array
    {
        // If no input data provided, try to get from JSON or POST
        if ($inputData === null) {
            $inputData = $this->getInputData();
        }

        // Validate input data
        $validationResult = $this->validateBvnData($inputData);

        // If validation fails, return errors
        if (!$validationResult['valid']) {
            return [
                'status' => 'error',
                'errors' => $validationResult['errors'],
            ];
        }

        // Sanitize the data
        $sanitizedData = $this->sanitizeAccountData($validationResult['data']);

        try {
            // Make POST request to the account generation endpoint
            $response = $this->httpClient->post('/account-creation/api/CustomerAccount/ValidateBVNandEnqueueAccountCreation', $sanitizedData);

            return [
                'status' => 'success',
                'data' => $response,
            ];
        } catch (Exception $e) {
            // Handle errors
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Retrieve input data from JSON body or POST
     *
     * @return array Input data
     */
    private function getInputData(): array
    {
        $jsonInput = file_get_contents('php://input');
        if (!empty($jsonInput)) {
            $jsonData = json_decode($jsonInput, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $jsonData;
            }
        }

        return [];
    }
 /**
     * Validate account generation data
     *
     * @param array $data Input data to validate
     * @return array Validation result
     */
    private function validateBvnData(array $data): array
{
    $errors = [];

   

    // Validate phone number 
    if (!isset($data['phoneNumber']) || !is_string($data['phoneNumber'])) {
        $errors['phoneNumber'] = 'phone number is required. you received OTP';
    }

    // Validate OTP
   if (!isset($data['otp']) || !is_string($data['otp'])) {
    $errors['otp'] = 'OTP is required';
}

       // Validate OTP
    if (!isset($data['trackingId']) || !is_string($data['trackingId'])) {
        $errors['trackingId'] = 'trackingId is required';
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'data' => $data,
    ];
}
  /**
     * Sanitize input data
     *
     * @param array $data Input data to sanitize
     * @return array Sanitized data
     */
    private function sanitizeAccountData(array $data): array
{
    return [
        'otp' => trim($data['otp']),
        'trackingId' => trim($data['trackingId']),
        'phoneNumber' => trim($data['phoneNumber']), 
    ];
}

}
