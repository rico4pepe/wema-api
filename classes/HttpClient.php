<?php

class HttpClient
{
    private $baseUrl;
    private $api_key;

  

    public function __construct(array $config)
    {
        $this->baseUrl = $config['base_url'];
        $this->api_key = $config['api_key'];
        
    }

    public function get(string $endpoint): ?array
    {

       
        $url = $this->baseUrl . $endpoint;

   
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

        
    
        

      
        return $this->handleResponse($response, $error, $httpCode);



    }






    public function post(string $endpoint, array $data): ?array
    {
        $url = $this->baseUrl . $endpoint;

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
        throw new Exception("HTTP Error: $httpCode. Response: $response");
    }
    
    private function formatHeaders(): array
    {
        return [
            'x-api-key: ' . $this->api_key;
        ];
    }
}
