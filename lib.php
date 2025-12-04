<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Sends a prompt to the Ollama API and returns the response.
 *
 * @param string $model The Ollama model to use (e.g., 'llama2', 'mistral')
 * @param string $prompt The prompt to send to the model
 * @param array $options Additional options for the API call
 * @return string|bool The model's response or false on failure
 */
/**
 * Sends a prompt to the Ollama API and returns the response.
 *
 * @param string $model The Ollama model to use (e.g., 'llama2', 'mistral')
 * @param string $prompt The prompt to send to the model
 * @param array $options Additional options for the API call
 * @return array|string The model's response as array or error message as string
 */
function local_ollama_generate($model, $prompt, $options = [])
{
    global $CFG;
    
    // Load configuration
    $config = get_config('local_ollama');
    if (empty($config->apihost)) {
        return get_string('error:apinotconfigured', 'local_ollama');
    }

    // Force IPv4 for localhost to avoid issues
    $config->apihost = str_replace('localhost', '127.0.0.1', $config->apihost);

    // Validate API host URL
    $urlparts = parse_url($config->apihost);
    if (!$urlparts || !isset($urlparts['scheme']) || !in_array($urlparts['scheme'], ['http', 'https'])) {
        return get_string('error:invalidapihost', 'local_ollama', $config->apihost);
    }

    // Sanitize model name (alphanumeric, underscore, hyphen, dot, colon)
    $model = preg_replace('/[^a-zA-Z0-9_\-\.:]/', '', $model);
    if (empty($model)) {
        return get_string('error:invalidmodel', 'local_ollama');
    }

    // Sanitize and validate prompt
    $prompt = trim($prompt);
    if (empty($prompt)) {
        return get_string('error:emptyprompt', 'local_ollama');
    }

    $endpoint = rtrim($config->apihost, '/') . '/api/generate';

    // Prepare request data with default options
    $data = [
        'model' => $model,
        'prompt' => $prompt,
        'stream' => false,
        'options' => [
            'temperature' => 0.7,
            'top_p' => 0.9,
        ]
    ];

    // Merge any additional options, allowing them to override defaults
    $data = array_merge_recursive($data, $options);

    // Set up cURL with proper error handling
    $ch = curl_init();
    if ($ch === false) {
        debugging("Failed to initialize cURL", DEBUG_DEVELOPER);
        return get_string('error:apirequestfailed', 'local_ollama');
    }

    // Configure cURL options
    $curlOptions = [
        CURLOPT_URL => $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 180, // 3 minutes timeout
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true, // Enable SSL verification for security
        CURLOPT_SSL_VERIFYHOST => 2,    // Strict host verification
        CURLOPT_FAILONERROR => true,    // Fail on HTTP error status
    ];

    curl_setopt_array($ch, $curlOptions);

    // Execute the request
    $response = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    // Log the request for debugging
    $debugInfo = [
        'endpoint' => $endpoint,
        'http_code' => $httpCode,
        'content_type' => $contentType,
        'error' => $error,
        'error_code' => $errno,
        'response' => $response,
    ];
    debugging('Ollama API Request: ' . print_r($debugInfo, true), DEBUG_DEVELOPER);

    // Handle cURL errors
    if ($errno) {
        $errorMessage = "cURL Error #{$errno}: {$error}";
        debugging($errorMessage, DEBUG_DEVELOPER);
        return get_string('error:apirequestfailed', 'local_ollama');
    }

    // Handle HTTP errors
    if ($httpCode !== 200) {
        $errorMessage = "HTTP Error #{$httpCode}";
        if ($response) {
            $errorMessage .= " - Response: " . $response;
        }
        debugging($errorMessage, DEBUG_DEVELOPER);
        
        if ($httpCode === 429) { // Rate limit exceeded
            return get_string('error:ratelimitexceeded', 'local_ollama');
        }
        return get_string('error:apirequestfailed', 'local_ollama');
    }

    // Parse JSON response
    $result = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        debugging("Invalid JSON response: " . json_last_error_msg(), DEBUG_DEVELOPER);
        return get_string('error:invalidresponse', 'local_ollama');
    }

    // Check for error in response
    if (isset($result['error'])) {
        debugging("Ollama API error: " . $result['error'], DEBUG_DEVELOPER);
        return get_string('error:invalidresponse', 'local_ollama');
    }

    // Return the response or error
    return $result['response'] ?? get_string('error:invalidresponse', 'local_ollama');
}

/**
 * Gets available models from Ollama API.
 *
 * @return array|bool List of available models or false on failure
 */
function local_ollama_get_models()
{
    $config = get_config('local_ollama');
    if (empty($config->apihost)) {
        return false;
    }

    // Force IPv4 for localhost to avoid issues
    $config->apihost = str_replace('localhost', '127.0.0.1', $config->apihost);

    // Validate API host URL
    if (!filter_var($config->apihost, FILTER_VALIDATE_URL) || !in_array(parse_url($config->apihost, PHP_URL_SCHEME), ['http', 'https'])) {
        return false;
    }

    $endpoint = rtrim($config->apihost, '/') . '/api/tags';

    // Use direct cURL instead of Moodle's curl class
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if (!empty($error)) {
        return false;
    }

    $result = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return false;
    }

    return $result['models'] ?? [];
}

/**
 * Validates the Ollama API configuration.
 *
 * @return array Validation result with 'success' boolean and 'message' string
 */
function local_ollama_validate_config()
{
    $config = get_config('local_ollama');

    if (empty($config->apihost)) {
        return ['success' => false, 'message' => get_string('apihostinvalid', 'local_ollama')];
    }

    // Force IPv4 for localhost to avoid issues
    $config->apihost = str_replace('localhost', '127.0.0.1', $config->apihost);

    // Validate API host URL format
    if (!filter_var($config->apihost, FILTER_VALIDATE_URL) || !in_array(parse_url($config->apihost, PHP_URL_SCHEME), ['http', 'https'])) {
        return ['success' => false, 'message' => get_string('apihostinvalid', 'local_ollama')];
    }

    // Test connection using direct cURL (bypass Moodle's security)
    $endpoint = rtrim($config->apihost, '/') . '/api/tags';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if (!empty($error)) {
        debugging('Connection error details: ' . $error, DEBUG_DEVELOPER);
        return ['success' => false, 'message' => get_string('connectionfailed', 'local_ollama') . ' (' . $error . ')'];
    }

    $result = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['success' => false, 'message' => get_string('errorprocessingrequest', 'local_ollama')];
    }

    return ['success' => true, 'message' => 'Connection successful'];
}

/**
 * Gets available models as options array for Moodle settings dropdown.
 *
 * @return array Model options array with model names as keys and values
 */
function local_ollama_get_model_options()
{
    try {
        $models = local_ollama_get_models();
        $options = [];

        if ($models && is_array($models)) {
            foreach ($models as $model) {
                $model_name = $model['name'] ?? '';
                if (!empty($model_name)) {
                    $options[$model_name] = $model_name;
                }
            }
        }

        // If no models found, add a default option
        if (empty($options)) {
            $options[''] = get_string('noavailablemodels', 'local_ollama');
        }

        return $options;
    } catch (Exception $e) {
        // Return fallback options if there's an error
        return [
            'phi:latest' => 'phi:latest',
            'llama2' => 'llama2',
            'mistral' => 'mistral'
        ];
    }
}
