<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace aiprovider_ollama;

use core_ai\aiactions\generate_image;
use core_ai\aiactions\generate_text;
use core_ai\aiactions\summarise_text;

/**
 * Ollama AI provider class.
 *
 * @package    aiprovider_ollama
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider extends \core_ai\provider
{

    /**
     * Get the list of actions supported by this provider.
     *
     * @return array List of action class names
     */
    public function get_action_list(): array
    {
        return [
            generate_text::class,
            summarise_text::class,
        ];
    }

    /**
     * Check if the provider is configured correctly.
     *
     * @return bool True if configured, false otherwise
     */
    public function is_provider_configured(): bool
    {
        $apihost = get_config('aiprovider_ollama', 'apihost');

        if (empty($apihost)) {
            return false;
        }

        // Validate URL format
        if (!filter_var($apihost, FILTER_VALIDATE_URL)) {
            return false;
        }

        return true;
    }

    /**
     * Test the connection to Ollama server.
     *
     * @return bool True if connection successful
     */
    public function test_connection(): bool
    {
        $apihost = get_config('aiprovider_ollama', 'apihost');

        if (empty($apihost)) {
            return false;
        }

        // Force IPv4 for localhost
        $apihost = str_replace('localhost', '127.0.0.1', $apihost);

        $endpoint = rtrim($apihost, '/') . '/api/tags';

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
            return false;
        }

        $result = json_decode($response, true);
        return (json_last_error() === JSON_ERROR_NONE && isset($result['models']));
    }

    /**
     * Get available models from Ollama server.
     *
     * @return array List of available models
     */
    public function get_models(): array
    {
        $apihost = get_config('aiprovider_ollama', 'apihost');

        if (empty($apihost)) {
            return [];
        }

        // Force IPv4 for localhost
        $apihost = str_replace('localhost', '127.0.0.1', $apihost);

        $endpoint = rtrim($apihost, '/') . '/api/tags';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if (!empty($error)) {
            return [];
        }

        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        return $result['models'] ?? [];
    }
}
