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
            __('Quiz Question Associator', LDQA_TEXT_DOMAIN),
            __('Quiz Question Associator', LDQA_TEXT_DOMAIN),
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
            wp_die(__('You do not have sufficient permissions to access this page.', LDQA_TEXT_DOMAIN));
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

        $nonce = isset($_POST['_wpnonce']) ? wp_unslash($_POST['_wpnonce']) : '';
        if (!wp_verify_nonce($nonce, 'ldqa_upload_csv')) {
            add_settings_error(
                'ldqa_messages',
                'ldqa_error',
                __('Security check failed.', LDQA_TEXT_DOMAIN),
                'error'
            );
            return;
        }

        // Validate and sanitize file input
        $file = isset($_FILES['ldqa_csv_file']) ? $_FILES['ldqa_csv_file'] : null;
        if (!$file) {
            add_settings_error(
                'ldqa_messages',
                'ldqa_error',
                __('No file uploaded.', LDQA_TEXT_DOMAIN),
                'error'
            );
            return;
        }

        // Handle file upload with WP_Filesystem
        $processor = new LDQA_Processor();
        $filesystem = new LDQA_Filesystem();
        
        $uploaded_file = $filesystem->handle_upload($file);
        if (is_wp_error($uploaded_file)) {
            add_settings_error(
                'ldqa_messages',
                'ldqa_error',
                $uploaded_file->get_error_message(),
                'error'
            );
            return;
        }

        $results = $processor->process_file($uploaded_file);
        
        if (is_wp_error($results)) {
            add_settings_error(
                'ldqa_messages',
                'ldqa_error',
                $results->get_error_message(),
                'error'
            );
            return;
        }

        // Handle successful and failed associations
        if (!empty($results['success'])) {
            add_settings_error(
                'ldqa_messages',
                'ldqa_success',
                sprintf(
                    /* translators: %d: number of successful associations */
                    __('Successfully processed %d associations.', LDQA_TEXT_DOMAIN),
                    count($results['success'])
                ),
                'success'
            );
        }

        if (!empty($results['errors'])) {
            foreach ($results['errors'] as $error) {
                add_settings_error(
                    'ldqa_messages',
                    'ldqa_error',
                    $error,
                    'error'
                );
            }
        }

        // If no successes and no errors, show a message
        if (empty($results['success']) && empty($results['errors'])) {
            add_settings_error(
                'ldqa_messages',
                'ldqa_warning',
                __('No valid associations were found in the CSV file.', LDQA_TEXT_DOMAIN),
                'warning'
            );
        }

        // Clean up the uploaded file
        $filesystem->delete($uploaded_file);
    }
}