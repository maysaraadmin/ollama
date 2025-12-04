<?php
// Simple test script to check available Ollama models
require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once(__DIR__.'/lib.php');

echo "<h2>Ollama Available Models Check</h2>";

// Test connection and get models
$models = local_ollama_get_models();

if ($models && is_array($models)) {
    echo "<h3>Found " . count($models) . " models:</h3>";
    echo "<ul>";
    foreach ($models as $model) {
        $name = $model['name'] ?? 'Unknown';
        $size = $model['size'] ?? 'Unknown size';
        $modified = $model['modified_at'] ?? 'Unknown';
        echo "<li><strong>" . htmlspecialchars($name) . "</strong><br>";
        echo "Size: " . htmlspecialchars($size) . "<br>";
        echo "Modified: " . htmlspecialchars($modified) . "</li>";
    }
    echo "</ul>";
    
    // Test model options function
    echo "<h3>Model Options for Settings:</h3>";
    $options = local_ollama_get_model_options();
    echo "<pre>";
    print_r($options);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>No models found or connection failed.</p>";
    
    // Test validation
    $validation = local_ollama_validate_config();
    echo "<h3>Connection Test:</h3>";
    echo "<pre>";
    print_r($validation);
    echo "</pre>";
}
?>
