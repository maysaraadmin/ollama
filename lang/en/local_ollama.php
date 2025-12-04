<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     local_ollama
 * @category    string
 * @copyright   2024 Your Name <your@email.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Plugin info
$string['pluginname'] = 'Ollama Integration';
$string['pluginname_help'] = 'This plugin allows integration with Ollama AI models for generating responses within Moodle.';
$string['privacy:metadata'] = 'The Ollama integration plugin does not store any personal data.';

// Settings page strings
$string['modelconfig'] = 'Model Configuration';
$string['modelconfig_desc'] = 'Configure the behavior of the Ollama models.';
$string['timeout'] = 'Request Timeout';
$string['timeout_desc'] = 'Maximum time in seconds to wait for a response from the Ollama API.';
$string['verifyssl'] = 'Verify SSL';
$string['verifyssl_desc'] = 'Verify SSL certificate when connecting to the Ollama API. Disable only for testing with self-signed certificates.';
$string['defaultmodel'] = 'Default Model';
$string['defaultmodel_desc'] = 'The default Ollama model to use for text generation.';
$string['apihost'] = 'API Host';
$string['apihost_desc'] = 'The base URL of your Ollama API server (e.g., http://localhost:11434)';

// UI Strings
$string['ollamademo'] = 'Ollama Demo';
$string['prompt'] = 'Prompt';
$string['model'] = 'Model';
$string['response'] = 'Response';
$string['submit'] = 'Generate Response';
$string['selectmodel'] = 'Select model';
$string['availablemodels'] = 'Available models';
$string['refreshmodels'] = 'Refresh available models';
$string['noavailablemodels'] = 'No models available. Please check your Ollama server.';
$string['processing'] = 'Processing... (this may take 1-2 minutes for first request)';

// Status messages
$string['connectionfailed'] = 'Failed to connect to Ollama server';
$string['connectionok'] = 'Successfully connected to Ollama server';
$string['modelfetcherror'] = 'Error fetching models from Ollama server';
$string['modelnotfound'] = 'Model not found or unavailable';
$string['apihostinvalid'] = 'Invalid API host URL';
$string['errorprocessingrequest'] = 'Error processing request. Please check your Ollama server configuration.';

// Error messages
$string['error:apinotconfigured'] = 'Ollama API host is not configured. Please contact your site administrator.';
$string['error:invalidapihost'] = 'Invalid API host URL: {$a}. Please check your Ollama API host configuration.';
$string['error:invalidmodel'] = 'Invalid model name. Please provide a valid model name.';
$string['error:emptyprompt'] = 'Empty prompt provided. Please enter a prompt to generate a response.';
$string['error:apirequestfailed'] = 'Failed to connect to Ollama API. Please check your server configuration and try again.';
$string['error:invalidresponse'] = 'Received an invalid response from Ollama API. Please try again.';
$string['error:ratelimitexceeded'] = 'Rate limit exceeded. Please wait before making another request.';
$string['modelslow'] = 'Note: AI models may take 1-2 minutes to respond, especially on first use. Please be patient.';
