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
 * Ollama integration demo page.
 *
 * @package    local_ollama
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

require_login();

$context = context_system::instance();
$PAGE->set_url(new moodle_url('/local/ollama/index.php'));
$PAGE->set_context($context);
$PAGE->set_title('Ollama AI Integration');
$PAGE->set_heading('Ollama AI Integration');
$PAGE->set_pagelayout('standard');
$PAGE->requires->css(new moodle_url('/local/ollama/styles.css'));

// Get configuration
$apihost = get_config('local_ollama', 'apihost');
$defaultmodel = get_config('local_ollama', 'defaultmodel');

if (empty($defaultmodel)) {
    $defaultmodel = 'phi:latest';
}

// Handle form submission
$output = '';
$prompt = optional_param('prompt', '', PARAM_TEXT);
$model = optional_param('model', $defaultmodel, PARAM_TEXT);

if (!empty($prompt) && confirm_sesskey()) {
    // Sanitize model parameter - allow alphanumeric, underscore, hyphen, dot, and colon
    $model = preg_replace('/[^a-zA-Z0-9_\-\.:]/', '', $model);
    if (empty($model)) {
        $model = $defaultmodel;
    }

    $output = local_ollama_generate($model, $prompt);
}

echo $OUTPUT->header();

// Add body class for styling
echo '<div class="path-local-ollama ollama-container">';

// Header Section
echo '<div class="ollama-header">';
echo '<h2>🤖 Ollama AI Assistant</h2>';
echo '<p>Powered by local AI models - Fast, Private, and Secure</p>';
echo '</div>';

// Connection Status and Models
$validation = local_ollama_validate_config();
$models = local_ollama_get_models();
$modelcount = is_array($models) ? count($models) : 0;

echo '<div class="status-cards">';

// Connection Status Card
echo '<div class="status-card">';
echo '<div class="status-card-icon">' . ($validation['success'] ? '✅' : '⚠️') . '</div>';
echo '<div class="status-card-title">Connection Status</div>';
echo '<div class="status-card-value">' . ($validation['success'] ? 'Connected' : 'Disconnected') . '</div>';
echo '</div>';

// Models Count Card
echo '<div class="status-card">';
echo '<div class="status-card-icon">📦</div>';
echo '<div class="status-card-title">Available Models</div>';
echo '<div class="status-card-value">' . $modelcount . ' Model' . ($modelcount != 1 ? 's' : '') . '</div>';
echo '</div>';

// API Host Card
echo '<div class="status-card">';
echo '<div class="status-card-icon">🌐</div>';
echo '<div class="status-card-title">API Host</div>';
echo '<div class="status-card-value" style="font-size: 0.9em;">' . ($apihost ? s($apihost) : 'Not configured') . '</div>';
echo '</div>';

echo '</div>'; // End status-cards

// Show detailed status messages
if ($validation['success']) {
    if (!empty($models) && is_array($models)) {
        $modelnames = array_map(function ($m) {
            return $m['name'] ?? 'unknown'; }, $models);
        echo html_writer::div(
            '<strong>Available models:</strong> ' . implode(', ', array_map(function ($name) {
                return '<span class="model-badge">' . s($name) . '</span>';
            }, $modelnames)),
            'alert alert-info'
        );
    }

    echo html_writer::div(
        '💡 <strong>Tip:</strong> First request may take 1-2 minutes as the model loads into memory. Subsequent requests will be faster!',
        'alert alert-info'
    );
} else {
    echo html_writer::div(
        '<strong>⚠️ Configuration Required</strong><br>' .
        'Please configure the Ollama API host at: <strong>Site administration → Plugins → Local plugins → Ollama Integration</strong>',
        'alert alert-warning'
    );
}

// Get available models for dropdown
$model_options = [];
if ($models !== false && is_array($models)) {
    foreach ($models as $model_info) {
        if (isset($model_info['name']) && is_string($model_info['name'])) {
            $model_options[$model_info['name']] = $model_info['name'];
        }
    }
}

// Main Form Container
echo '<div class="ollama-form-container">';
echo '<form method="post" class="ollama-form">';
echo html_writer::empty_tag('input', [
    'type' => 'hidden',
    'name' => 'sesskey',
    'value' => sesskey()
]);

// Prompt Field
echo '<div class="form-group">';
echo '<label for="prompt">✍️ Your Prompt</label>';
echo html_writer::tag('textarea', s($prompt), [
    'name' => 'prompt',
    'id' => 'prompt',
    'rows' => 5,
    'required' => 'required',
    'placeholder' => 'Ask me anything... For example: "Explain quantum computing in simple terms" or "Write a haiku about coding"'
]);
echo '</div>';

// Model Selection
echo '<div class="form-group">';
echo '<label for="model">🎯 Select Model</label>';
if (!empty($model_options)) {
    echo html_writer::select($model_options, 'model', $model, '', ['id' => 'model']);
} else {
    echo html_writer::empty_tag('input', [
        'type' => 'text',
        'name' => 'model',
        'id' => 'model',
        'value' => s($model),
        'placeholder' => 'e.g., phi:latest',
        'required' => 'required'
    ]);
}
echo '</div>';

// Submit Button
echo html_writer::empty_tag('input', [
    'type' => 'submit',
    'value' => '🚀 Generate Response',
    'class' => 'ollama-submit-btn'
]);

echo '</form>';
echo '</div>'; // End ollama-form-container

// Display the output if any
if (!empty($output)) {
    echo '<div class="ollama-response-container">';

    // Check if output is an error message
    if (strpos($output, 'Error:') === 0) {
        echo '<h3>❌ Error Occurred</h3>';
        echo html_writer::div(nl2br(s($output)), 'alert alert-danger');

        // Show troubleshooting tips
        echo '<div class="troubleshooting-box">';
        echo '<strong>🔧 Troubleshooting Tips:</strong><br><br>';
        echo '1. <strong>Check Ollama is running:</strong> <code>ollama list</code><br>';
        echo '2. <strong>Verify the model exists:</strong> <code>ollama pull ' . s($model) . '</code><br>';
        echo '3. <strong>Test API connection:</strong> <code>curl http://127.0.0.1:11434/api/tags</code><br>';
        echo '4. <strong>Check Ollama logs</strong> for detailed error messages<br>';
        echo '</div>';
    } else {
        echo '<h3><span class="success-icon">✨</span> AI Response</h3>';
        echo '<div class="alert alert-success" style="margin-bottom: 20px;">';
        echo '<strong>Model used:</strong> <span class="model-badge">' . s($model) . '</span>';
        echo '</div>';
        echo html_writer::div(
            nl2br(s($output)),
            'ollama-response'
        );
    }

    echo '</div>'; // End ollama-response-container
}

echo '</div>'; // End ollama-container

echo $OUTPUT->footer();
