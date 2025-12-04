<?php
defined('MOODLE_INTERNAL') || die();

$plugin = new stdClass();
$plugin->component = 'local_ollama';
$plugin->version = 2024120402;
$plugin->requires = 2022112800.00; // Moodle 4.1+
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '1.0.1';
