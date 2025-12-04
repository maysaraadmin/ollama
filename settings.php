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

/**
 * Admin settings for Ollama integration.
 *
 * @package    local_ollama
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_ollama',
        get_string('pluginname', 'local_ollama')
    );

    $ADMIN->add('localplugins', $settings);

    // API Host setting
    $settings->add(new admin_setting_configtext(
        'local_ollama/apihost',
        get_string('apihost', 'local_ollama'),
        get_string('apihost_desc', 'local_ollama'),
        'http://127.0.0.1:11434',
        PARAM_URL
    ));

    // Default Model setting
    // Default Model setting
    $settings->add(new admin_setting_configtext(
        'local_ollama/defaultmodel',
        get_string('defaultmodel', 'local_ollama'),
        get_string('defaultmodel_desc', 'local_ollama'),
        'phi:latest',
        PARAM_TEXT
    ));

    // Add a section for model configuration
    $settings->add(new admin_setting_heading(
        'local_ollama/modelconfig',
        get_string('modelconfig', 'local_ollama'),
        get_string('modelconfig_desc', 'local_ollama')
    ));

    // Add a setting for request timeout
    $settings->add(new admin_setting_configtext(
        'local_ollama/timeout',
        get_string('timeout', 'local_ollama'),
        get_string('timeout_desc', 'local_ollama'),
        180,
        PARAM_INT
    ));

    // Add a setting for enabling/disabling SSL verification
    $settings->add(new admin_setting_configcheckbox(
        'local_ollama/verifyssl',
        get_string('verifyssl', 'local_ollama'),
        get_string('verifyssl_desc', 'local_ollama'),
        1
    ));
}
