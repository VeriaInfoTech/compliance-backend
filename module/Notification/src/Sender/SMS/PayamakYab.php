<?php

namespace Pi\Notification\Sender\SMS;

class PayamakYab implements SMSInterface
{
    /**
     * Get error message for PayamakYab result code
     *
     * @param int $code
     * @return string
     */
    private function getErrorMessage(int $code): string
    {
        $errorMessages = [
            -1 => 'Invalid username or password',
            -2 => 'Insufficient credit',
            -3 => 'Invalid sender number',
            -4 => 'Invalid recipient number',
            -5 => 'Message is empty',
            -6 => 'Invalid message content',
            -7 => 'Service unavailable',
            -8 => 'Invalid request format',
            -9 => 'Rate limit exceeded',
            -10 => 'Account is disabled',
        ];
        
        return $errorMessages[$code] ?? "Unknown error code: {$code}";
    }
    
    public function send($config, $params): void
    {
        // Set SMS params
        $smsParams = [
            'username' => $config['payamakyab']['username'],
            'password' => $config['payamakyab']['password'],
            'from'     => $config['payamakyab']['number'],
            'to'       => [str_replace('+98', '', $params['mobile'])],
            'text'     => $params['message'],
            'isflash'  => false,
            'udh'      => '',
            'recId'    => [0],
            'status'   => [0],
        ];

        // Initialize variables for error logging
        $url = null;
        $requestBody = null;
        
        // Send SMS
        try {
            $url = str_replace('https://', 'http://', $config['payamakyab']['url']);
            $requestBody = json_encode($smsParams, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            
            // Use native PHP cURL to avoid threading issues with Laminas HTTP Client
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            
            $responseBody = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            $curlErrno = curl_errno($ch);
            $curlInfo = curl_getinfo($ch);
            curl_close($ch);
            
            // Log detailed request/response information for debugging
            $debugInfo = [
                'url' => $url,
                'http_code' => $httpCode,
                'curl_error' => $curlError,
                'curl_errno' => $curlErrno,
                'request_body' => $requestBody,
                'response_body' => $responseBody,
                'curl_info' => $curlInfo,
            ];
            
            if ($responseBody === false || !empty($curlError)) {
                $errorMessage = sprintf(
                    "PayamakYab cURL Error [%d]: %s\nURL: %s\nRequest: %s\nResponse: %s\ncURL Info: %s",
                    $curlErrno,
                    $curlError,
                    $url,
                    $requestBody,
                    $responseBody ?: 'No response',
                    json_encode($curlInfo, JSON_PRETTY_PRINT)
                );
                error_log($errorMessage);
                throw new \Exception('cURL error [' . $curlErrno . ']: ' . $curlError);
            }
            
            // Log if HTTP code indicates an error
            if ($httpCode < 200 || $httpCode >= 300) {
                $errorMessage = sprintf(
                    "PayamakYab HTTP Error [%d]: %s\nURL: %s\nRequest: %s\nResponse: %s",
                    $httpCode,
                    $responseBody ?: 'No response body',
                    $url,
                    $requestBody,
                    $responseBody
                );
                error_log($errorMessage);
            }
            
            // Parse response (could be JSON or XML)
            $result = null;
            $decodedBody = json_decode($responseBody, true);
            
            if (json_last_error() === JSON_ERROR_NONE && isset($decodedBody)) {
                // JSON response
                $result = $decodedBody['SendSmsResult'] ?? $decodedBody['result'] ?? $decodedBody;
            } else {
                // Try to parse as XML or extract numeric result
                if (preg_match('/<SendSmsResult>(-?\d+)<\/SendSmsResult>/', $responseBody, $matches)) {
                    $result = (int)$matches[1];
                } elseif (preg_match('/result[>"](-?\d+)/i', $responseBody, $matches)) {
                    $result = (int)$matches[1];
                } else {
                    // Try to extract any numeric value as result
                    if (preg_match('/(-?\d+)/', $responseBody, $matches)) {
                        $result = (int)$matches[1];
                    }
                }
            }
            
            // Convert result to integer if it's not already
            if ($result !== null && !is_int($result)) {
                $result = is_numeric($result) ? (int)$result : null;
            }
            
            // Log if result couldn't be parsed
            if ($result === null) {
                $errorMessage = sprintf(
                    "PayamakYab Response Parse Error: Could not extract result from response\nURL: %s\nHTTP Code: %d\nRequest: %s\nResponse: %s\nDecoded: %s",
                    $url,
                    $httpCode,
                    $requestBody,
                    $responseBody,
                    json_encode($decodedBody ?? 'null', JSON_PRETTY_PRINT)
                );
                error_log($errorMessage);
            }
            
            // PayamakYab result codes:
            // Positive numbers: Success (message ID)
            // Negative numbers: Error codes
            // Common error codes: -1 (invalid username/password), -2 (insufficient credit), etc.
            
            if ($result !== null && $result < 0) {
                // Log error but don't throw exception to avoid breaking the flow
                $errorMessage = sprintf(
                    "PayamakYab SMS Error: Code %d for mobile %s\nMessage: %s\nURL: %s\nHTTP Code: %d\nRequest: %s\nResponse: %s",
                    $result,
                    $params['mobile'] ?? 'unknown',
                    $this->getErrorMessage($result),
                    $url,
                    $httpCode,
                    $requestBody,
                    $responseBody
                );
                error_log($errorMessage);
            }
        } catch (\Exception $e) {
            // Log detailed exception information
            $errorMessage = sprintf(
                "PayamakYab SMS Exception: %s\nMobile: %s\nURL: %s\nRequest: %s\nFile: %s\nLine: %d\nTrace: %s",
                $e->getMessage(),
                $params['mobile'] ?? 'unknown',
                $url ?? 'N/A',
                $requestBody ?? 'N/A',
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString()
            );
            error_log($errorMessage);
        }
    }
}