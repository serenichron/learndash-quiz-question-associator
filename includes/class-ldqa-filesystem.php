<?php
if (!defined('ABSPATH')) {
    exit;
}

class LDQA_Filesystem {
    private $wp_filesystem;

    public function __construct() {
        global $wp_filesystem;
        
        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        
        WP_Filesystem();
        $this->wp_filesystem = $wp_filesystem;
    }

    public function handle_upload($file) {
        if (empty($file['tmp_name'])) {
            return new WP_Error('upload_error', esc_html__('No file uploaded.', 'ldqa'));
        }

        // Verify file type
        $file_type = wp_check_filetype($file['name'], array('csv' => 'text/csv'));
        if ($file_type['ext'] !== 'csv') {
            return new WP_Error('invalid_type', esc_html__('Only CSV files are allowed.', 'ldqa'));
        }

        // Check file size (1MB limit)
        if ($file['size'] > 1024 * 1024) {
            return new WP_Error('file_too_large', esc_html__('File size exceeds limit (1MB).', 'ldqa'));
        }

        // Create a temporary file with WordPress prefix
        $upload_dir = wp_upload_dir();
        $temp_file = wp_tempnam('ldqa_', $upload_dir['basedir']);

        // Move uploaded file to temporary location using WP_Filesystem
        if (!$this->wp_filesystem->move($file['tmp_name'], $temp_file, true)) {
            return new WP_Error('move_error', esc_html__('Failed to process uploaded file.', 'ldqa'));
        }

        return $temp_file;
    }

    public function read_csv($file_path) {
        if (!$this->is_readable($file_path)) {
            return new WP_Error('read_error', esc_html__('Unable to read file.', 'ldqa'));
        }

        $content = $this->wp_filesystem->get_contents($file_path);
        if (!$content) {
            return new WP_Error('empty_file', esc_html__('File is empty.', 'ldqa'));
        }

        $rows = array();
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $content);
        rewind($handle);

        while (($data = fgetcsv($handle)) !== false) {
            $rows[] = $data;
        }

        fclose($handle);

        if (empty($rows)) {
            return new WP_Error('no_data', esc_html__('No data found in CSV file.', 'ldqa'));
        }

        return $rows;
    }

    public function is_readable($file) {
        return $this->wp_filesystem->exists($file) && $this->wp_filesystem->is_readable($file);
    }

    public function delete($file) {
        if ($this->wp_filesystem->exists($file)) {
            return $this->wp_filesystem->delete($file);
        }
        return true;
    }
}
