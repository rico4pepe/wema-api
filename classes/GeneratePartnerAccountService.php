<?php

class GeneratePartnerAccountService
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
        $validationResult = $this->validateAccountData($inputData);

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
            $response = $this->httpClient->post('/account-creation/api/CustomerAccount/PostPartnershipAccountCreationWithBvn', $sanitizedData);

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
    private function validateAccountData(array $data): array
{
    $errors = [];

    // Validate email
    if (!isset($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'A valid email address is required.';
    }

    // Validate phone number (11-digit numeric string)
    if (!isset($data['phoneNumber']) || !is_string($data['phoneNumber'])) {
        $errors['phoneNumber'] = 'phone number is required. to receive OTP';
    }

    // Validate NIN (11-digit numeric string)
    if (!isset($data['bvn']) || !is_string($data['bvn'])) {
        $errors['bvn'] = 'bvn is required for validation';
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
        'email' => trim($data['email']),
        'phoneNumber' => trim($data['phoneNumber']), 
        'bvn' => trim($data['bvn']),
    ];
}

}
