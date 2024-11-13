<?php
/**
 * Plugin Name: LearnDash Quiz Question Associator
 * Plugin URI: https://serenichron.com/plugins/learndash-quiz-question-associator
 * Description: Associate existing LearnDash questions with quizzes via CSV upload
 * Version: 1.0.6
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * Author: Vlad Tudorie
 * Author URI: https://serenichron.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: learndash-quiz-question-associator
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('LDQA_VERSION', '1.0.6');
define('LDQA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LDQA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LDQA_TEXT_DOMAIN', 'learndash-quiz-question-associator');

// Load required files
require_once LDQA_PLUGIN_DIR . 'includes/class-ldqa-loader.php';

// Initialize the plugin
function ldqa_init() {
    return LDQA_Loader::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'ldqa_init');