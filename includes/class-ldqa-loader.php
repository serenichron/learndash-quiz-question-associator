<?php
if (!defined('ABSPATH')) {
    exit;
}

class LDQA_Loader {
    private static $instance = null;
    private $admin;
    private $processor;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->setup_hooks();
    }

    private function load_dependencies() {
        require_once LDQA_PLUGIN_DIR . 'includes/class-ldqa-admin.php';
        require_once LDQA_PLUGIN_DIR . 'includes/class-ldqa-processor.php';
        require_once LDQA_PLUGIN_DIR . 'includes/class-ldqa-filesystem.php';
        
        $this->admin = new LDQA_Admin();
        $this->processor = new LDQA_Processor();
    }

    private function setup_hooks() {
        // Add initialization hooks if needed
        add_action('init', array($this, 'load_textdomain'));
    }

    public function load_textdomain() {
        load_plugin_textdomain('ldqa', false, dirname(plugin_basename(LDQA_PLUGIN_DIR)) . '/languages/');
    }
}
