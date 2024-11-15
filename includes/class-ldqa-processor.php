<?php
if (!defined('ABSPATH')) {
    exit;
}

class LDQA_Processor {
    private $filesystem;
    private $cache = array();

    public function __construct() {
        $this->filesystem = new LDQA_Filesystem();
    }

    public function process_file($file_path) {
        if (!$this->filesystem->is_readable($file_path)) {
            return new WP_Error(
                'file_error', 
                __('Unable to read uploaded file.', LDQA_TEXT_DOMAIN)
            );
        }

        $results = array(
            'success' => array(),
            'errors' => array()
        );

        $rows = $this->filesystem->read_csv($file_path);
        if (is_wp_error($rows)) {
            return $rows;
        }

        foreach ($rows as $row_number => $row) {
            if (empty($row) || !is_array($row) || count($row) < 2) {
                $results['errors'][] = sprintf(
                    /* translators: %d: row number */
                    __('Row %d: Invalid row format.', LDQA_TEXT_DOMAIN),
                    $row_number + 1
                );
                continue;
            }

            $result = $this->process_row($row, $row_number + 1);
            if (is_wp_error($result)) {
                $results['errors'][] = $result->get_error_message();
            } else {
                $results['success'][] = $result;
            }
        }

        if (empty($results['success']) && empty($results['errors'])) {
            return new WP_Error(
                'no_data',
                __('No valid data found in the CSV file.', LDQA_TEXT_DOMAIN)
            );
        }

        return $results;
    }

    private function process_row($row, $row_number) {
        $quiz_id = isset($row[0]) ? absint(trim($row[0])) : 0;
        $question_id = isset($row[1]) ? absint(trim($row[1])) : 0;

        if (!$quiz_id || !$question_id) {
            return new WP_Error(
                'invalid_data', 
                sprintf(
                    /* translators: %d: row number */
                    __('Row %d: Invalid quiz or question ID.', LDQA_TEXT_DOMAIN),
                    $row_number
                )
            );
        }

        return $this->associate_question_with_quiz($question_id, $quiz_id, $row_number);
    }

    private function associate_question_with_quiz($question_id, $quiz_id, $row_number) {
        // Cache post verification results
        if (!isset($this->cache['question_' . $question_id])) {
            $this->cache['question_' . $question_id] = get_post($question_id);
        }
        if (!isset($this->cache['quiz_' . $quiz_id])) {
            $this->cache['quiz_' . $quiz_id] = get_post($quiz_id);
        }

        $question = $this->cache['question_' . $question_id];
        $quiz = $this->cache['quiz_' . $quiz_id];

        if (!$question || $question->post_type !== 'sfwd-question') {
            return new WP_Error(
                'invalid_question',
                sprintf(
                    /* translators: %1$d: row number, %2$d: question ID */
                    __('Row %1$d: Question ID %2$d not found or invalid.', LDQA_TEXT_DOMAIN),
                    $row_number,
                    $question_id
                )
            );
        }

        if (!$quiz || $quiz->post_type !== 'sfwd-quiz') {
            return new WP_Error(
                'invalid_quiz',
                sprintf(
                    /* translators: %1$d: row number, %2$d: quiz ID */
                    __('Row %1$d: Quiz ID %2$d not found or invalid.', LDQA_TEXT_DOMAIN),
                    $row_number,
                    $quiz_id
                )
            );
        }

        // Get quiz pro ID (with caching)
        if (!isset($this->cache['quiz_pro_' . $quiz_id])) {
            $this->cache['quiz_pro_' . $quiz_id] = get_post_meta($quiz_id, 'quiz_pro_id', true);
        }
        $quiz_pro_id = $this->cache['quiz_pro_' . $quiz_id];

        if (!$quiz_pro_id) {
            return new WP_Error(
                'missing_quiz_pro_id',
                sprintf(
                    /* translators: %1$d: row number, %2$d: quiz ID */
                    __('Row %1$d: Quiz pro ID not found for quiz: %2$d', LDQA_TEXT_DOMAIN),
                    $row_number,
                    $quiz_id
                )
            );
        }

        // Update question metadata
        update_post_meta($question_id, 'quiz_id', $quiz_id);
        update_post_meta($question_id, '_sfwd-question', array('sfwd-question_quiz' => $quiz_id));

        // Update pro quiz question table with caching
        $question_pro_id = get_post_meta($question_id, 'question_pro_id', true);
        if ($question_pro_id) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'learndash_pro_quiz_question';
            
            // Use cached query to update quiz_id
            $wpdb->update(
                $table_name,
                array('quiz_id' => $quiz_pro_id),
                array('id' => $question_pro_id),
                array('%d'),
                array('%d')
            );

            wp_cache_delete($question_id, 'post_meta');
        }

        return sprintf(
            /* translators: %1$d: row number, %2$d: question ID, %3$d: quiz ID */
            __('Row %1$d: Successfully associated question %2$d with quiz %3$d', LDQA_TEXT_DOMAIN),
            $row_number,
            $question_id,
            $quiz_id
        );
    }
}