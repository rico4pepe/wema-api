<?php

class HttpClient
{
    private $baseUrl;
    private $api_key;
    private $primarySubscriptionKey;
    private $secondarySubscriptionKey;
    private $logger;
    private $usePrimaryKey; // Flag to determine which key to use
  

       public function __construct(array $config, $logger = null, bool $usePrimaryKey = true)
    {
        // Check required configuration keys
        $requiredKeys = ['base_url', 'x-api-key', 'primary_subscription_key', 'secondary_subscription_key'];
        foreach ($requiredKeys as $key) {
            if (!isset($config[$key])) {
                throw new InvalidArgumentException("Missing required configuration key: $key");
            }
        }

        // Initialize properties
        $this->logger = $logger;
        $this->baseUrl = $config['base_url'];
        $this->api_key = $config['x-api-key'];
        $this->primarySubscriptionKey = $config['primary_subscription_key'];
        $this->secondarySubscriptionKey = $config['secondary_subscription_key'];
        $this->usePrimaryKey = $usePrimaryKey;
  
        $this->logger->log('HttpClient initialized with base URL: ' . $this->baseUrl);
    }

    public function get(string $endpoint): ?array
    {

       
        $url = $this->baseUrl . $endpoint;
        $this->logger->log('Making GET request to: ' . $url);
        // Initialize cURL session
   
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => $this->formatHeaders(),
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        
    
        
        $this->logger->log('Response: ' . $response, ['http_code' => $httpCode]);
     
      
        return $this->handleResponse($response, $error, $httpCode);



    }






    public function post(string $endpoint, array $data): ?array
    {
        $url = $this->baseUrl . $endpoint;

        $this->logger->log('Making POST request to: ' . $url, ['data' => $data]);
       $this->logger->log('POST data being sent:', $data); // Add this line
        // Initialize cURL session

        $ch = curl_init();
        curl_setopt_array($ch, array(
            // CURLOPT_URL => $baseUrl . '/api-payment-servide/v1/service-status',
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => empty($data) ? '{}' : json_encode($data),
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => false,
           
            CURLOPT_HTTPHEADER => array_merge(
                $this->formatHeaders(),
                ['Content-Type: application/json']
            ),
        ));

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->logger->log('Response: ' . $response, ['http_code' => $httpCode]);

        return $this->handleResponse($response, $error, $httpCode);
    }

    
 private function handleResponse($response, $error, $httpCode): ?array
    {
        // If there was a curl error, throw an exception
        if ($error) {
            throw new Exception("Curl Error: $error");
        }
    
        // If the HTTP code indicates success (2xx), try to handle the response
        if ($httpCode >= 200 && $httpCode < 300) {
            // Try to decode the JSON response
            $decodedResponse = json_decode($response, true);
    
            // If JSON decoding is successful, return the decoded response
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decodedResponse;
            }
    
            // If JSON decoding fails, check if the response is numeric (float or integer)
            if (is_numeric($response)) {
                // Return the numeric value as an array
                return ['value' => (float) $response];
            }
    
            // If the response is neither JSON nor numeric, throw an exception
            throw new Exception("Invalid JSON response or unexpected response format.");
        }

            // If the HTTP code indicates failure (non-2xx), throw an exception
        $decodedErrorResponse = json_decode($response, true);
        $errorMessage = "HTTP Error: $httpCode.";
              if (json_last_error() === JSON_ERROR_NONE) {
            // If the error response is valid JSON, append its content.
            // You might want to cherry-pick specific fields, e.g., $decodedErrorResponse['message']
            if (isset($decodedErrorResponse['message'])) {
                $errorMessage .= " API Message: " . $decodedErrorResponse['message'];
             
                unset($decodedErrorResponse['message']); // Avoid duplicating the message
                if (!empty($decodedErrorResponse)) {
                     $errorMessage .= " API Details: " . json_encode($decodedErrorResponse);
                }

            } else {
                // If no 'message' key, but still valid JSON, include the whole decoded JSON.
                $errorMessage .= " Response: " . json_encode($decodedErrorResponse);
            }
        } else {
            // If the error response is not JSON, include the raw response.
            $errorMessage .= " Raw Response: " . $response;
        }

        
         throw new Exception($errorMessage);
    }

    
     private function formatHeaders(): array
    {
        $subscriptionKey = $this->usePrimaryKey 
            ? $this->primarySubscriptionKey 
            : $this->secondarySubscriptionKey;

        return [
            'x-api-key: ' . $this->api_key,
            'Ocp-Apim-Subscription-Key: ' . $subscriptionKey,
        ];
    }

      /**
     * Switch between primary and secondary subscription keys
     */
    public function usePrimaryKey(bool $usePrimary): void
    {
        $this->usePrimaryKey = $usePrimary;
        $keyType = $usePrimary ? 'primary' : 'secondary';
        $this->logger->log("Switched to using $keyType subscription key");
    }
}
