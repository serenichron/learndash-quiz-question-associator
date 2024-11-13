<?php
if (!defined('ABSPATH')) {
    exit;
}

class LDQA_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_admin'));
    }

    public function add_admin_menu() {
        if (!current_user_can('edit_courses')) {
            return;
        }

        add_submenu_page(
            'learndash-lms',
            esc_html__('Quiz Question Associator', 'ldqa'),
            esc_html__('Quiz Question Associator', 'ldqa'),
            'edit_courses',
            'ldqa-associator',
            array($this, 'render_admin_page')
        );
    }

    public function init_admin() {
        register_setting('ldqa_options', 'ldqa_settings');
    }

    public function render_admin_page() {
        if (!current_user_can('edit_courses')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'ldqa'));
        }

        // Process form submission
        $this->handle_form_submission();

        // Load the admin view
        require_once LDQA_PLUGIN_DIR . 'views/admin-page.php';
    }

    private function handle_form_submission() {
        if (!isset($_POST['ldqa_submit'])) {
            return;
        }

        if (!check_admin_referer('ldqa_upload_csv') || !wp_verify_nonce($_POST['_wpnonce'], 'ldqa_upload_csv')) {
            wp_die(esc_html__('Security check failed.', 'ldqa'));
        }

        // Handle file upload with WP_Filesystem
        $processor = new LDQA_Processor();
        $filesystem = new LDQA_Filesystem();
        
        $file = $filesystem->handle_upload($_FILES['ldqa_csv_file']);
        if (is_wp_error($file)) {
            add_settings_error(
                'ldqa_messages', 
                'ldqa_error', 
                $file->get_error_message(), 
                'error'
            );
            return;
        }

        $result = $processor->process_file($file);
        if (is_wp_error($result)) {
            add_settings_error(
                'ldqa_messages', 
                'ldqa_error', 
                $result->get_error_message(), 
                'error'
            );
        }
    }
}
