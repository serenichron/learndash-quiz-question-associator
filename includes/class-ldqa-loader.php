<?php
if (!defined('ABSPATH')) {
    exit;
}

class LDQA_Loader {
    private static $instance = null;
    private $admin;
    private $processor;
    private $filesystem;

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
        
        $this->filesystem = new LDQA_Filesystem();
        $this->processor = new LDQA_Processor();
        $this->admin = new LDQA_Admin();
    }

    private function setup_hooks() {
        add_action('init', array($this, 'load_textdomain'));
        add_action('admin_notices', array($this, 'check_dependencies'));
    }

    public function load_textdomain() {
        load_plugin_textdomain(
            LDQA_TEXT_DOMAIN,
            false,
            dirname(plugin_basename(LDQA_PLUGIN_DIR)) . '/languages/'
        );
    }

    public function check_dependencies() {
        if (!class_exists('SFWD_LMS')) {
            add_settings_error(
                'ldqa_messages',
                'ldqa_error',
                __('LearnDash LMS is required for the Quiz Question Associator plugin to work.', LDQA_TEXT_DOMAIN),
                'error'
            );
        }
    }
}