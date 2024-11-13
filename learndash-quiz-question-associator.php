<?php
/**
 * Plugin Name: LearnDash Quiz Question Associator
 * Description: Associate existing LearnDash questions with quizzes via CSV upload
 * Version: 1.0.4
 * Author: Vlad Tudorie
 * Author URI: https://serenichron.com
 */

if (!defined('ABSPATH')) {
    exit;
}

// Add menu item under LearnDash menu
function ldqa_add_admin_menu() {
    add_submenu_page(
        'learndash-lms',          // Parent slug (LearnDash LMS menu)
        'Quiz Question Associator', // Page title
        'Quiz Question Associator', // Menu title
        'manage_options',          // Capability required
        'ldqa-associator',         // Menu slug
        'ldqa_admin_page'          // Function to display the page
    );
}
add_action('admin_menu', 'ldqa_add_admin_menu');

// Create the admin page
function ldqa_admin_page() {
    // Handle form submission
    if (isset($_POST['ldqa_submit']) && check_admin_referer('ldqa_upload_csv')) {
        if (!empty($_FILES['ldqa_csv_file']['tmp_name'])) {
            ldqa_process_csv($_FILES['ldqa_csv_file']['tmp_name']);
        }
    }
    
    // Admin page HTML
    ?>
    <div class="wrap">
        <h1>LearnDash Quiz Question Associator</h1>
        
        <div class="card">
            <h2>Upload CSV File</h2>
            <p>Upload a CSV file with each row containing a Quiz ID and its corresponding Question ID.</p>
            <p>Example CSV format:</p>
            <pre>1306846,1306847
1306849,1306850
1307452,1307453</pre>
            <p><strong>Format Details:</strong></p>
            <ul style="list-style-type: disc; margin-left: 20px;">
                <li>Each row should contain: Quiz ID, Question ID</li>
                <li>The IDs should be existing Quiz and Question IDs in your LearnDash system</li>
                <li>First column: Quiz IDs</li>
                <li>Second column: Question IDs</li>
            </ul>
            
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('ldqa_upload_csv'); ?>
                <input type="file" name="ldqa_csv_file" accept=".csv" required>
                <p class="submit">
                    <input type="submit" name="ldqa_submit" class="button button-primary" value="Process CSV">
                </p>
            </form>
        </div>
    </div>
    <?php
}

function ldqa_debug_log($message, $data = null) {
    if (WP_DEBUG) {
        error_log("LDQA Debug: $message");
        if ($data !== null) {
            error_log("LDQA Data: " . print_r($data, true));
        }
    }
}

function ldqa_verify_post_exists($post_id, $post_type) {
    $post = get_post($post_id);
    ldqa_debug_log("Verifying $post_type ID: $post_id", $post);
    
    if (!$post) {
        return array(
            'exists' => false,
            'message' => "Post ID $post_id does not exist"
        );
    }
    if ($post->post_type !== $post_type) {
        return array(
            'exists' => false,
            'message' => "Post ID $post_id is not a $post_type (found {$post->post_type})"
        );
    }
    return array(
        'exists' => true,
        'message' => 'Valid'
    );
}

function ldqa_process_csv($file) {
    $results = array(
        'success' => array(),
        'errors' => array(),
        'debug' => array()
    );

    // Read CSV file
    $handle = fopen($file, 'r');
    if ($handle !== FALSE) {
        $row_number = 0;
        while (($row = fgetcsv($handle)) !== FALSE) {
            $row_number++;
            if (count($row) < 2) {
                ldqa_debug_log("Skipping row $row_number - insufficient columns", $row);
                continue;
            }
            
            $quiz_id = trim($row[0]);
            $question_id = trim($row[1]);
            
            ldqa_debug_log("Processing row $row_number", array(
                'quiz_id' => $quiz_id,
                'question_id' => $question_id
            ));

            // Store debug info
            $results['debug'][] = "Row $row_number: Quiz ID = $quiz_id, Question ID = $question_id";
            
            // Verify both posts exist and are correct type
            $quiz_check = ldqa_verify_post_exists($quiz_id, 'sfwd-quiz');
            $question_check = ldqa_verify_post_exists($question_id, 'sfwd-question');

            if (!$quiz_check['exists']) {
                $results['errors'][] = "Row $row_number skipped - Quiz error: {$quiz_check['message']}";
                continue;
            }

            if (!$question_check['exists']) {
                $results['errors'][] = "Row $row_number skipped - Question error: {$question_check['message']}";
                continue;
            }

            // Both exist and are correct type, proceed with association
            $result = ldqa_associate_question_with_quiz($question_id, $quiz_id);
            
            if ($result['success']) {
                $results['success'][] = "✓ Row $row_number: Question $question_id successfully associated with Quiz $quiz_id";
            } else {
                $results['errors'][] = "✗ Row $row_number: Association failed - Question $question_id with Quiz $quiz_id: {$result['message']}";
            }
        }
        fclose($handle);

        // Display all results including debug info
        echo '<div class="wrap">';
        
        // Debug Information
        if (WP_DEBUG && !empty($results['debug'])) {
            echo '<div class="notice notice-info"><p><strong>Debug Information:</strong></p><ul style="list-style-type: disc; margin-left: 20px;">';
            foreach ($results['debug'] as $debug) {
                echo "<li>$debug</li>";
            }
            echo '</ul></div>';
        }
        
        // Errors
        if (!empty($results['errors'])) {
            echo '<div class="notice notice-error"><p><strong>Errors Found:</strong></p><ul style="list-style-type: disc; margin-left: 20px;">';
            foreach ($results['errors'] as $error) {
                echo "<li>$error</li>";
            }
            echo '</ul></div>';
        }
        
        // Successes
        if (!empty($results['success'])) {
            echo '<div class="notice notice-success"><p><strong>Successful Associations:</strong></p><ul style="list-style-type: disc; margin-left: 20px;">';
            foreach ($results['success'] as $success) {
                echo "<li>$success</li>";
            }
            echo '</ul></div>';
        }
        echo '</div>';
    }
}

function ldqa_associate_question_with_quiz($question_id, $quiz_id) {
    try {
        // Get quiz pro ID
        $quiz_pro_id = get_post_meta($quiz_id, 'quiz_pro_id', true);
        ldqa_debug_log("Quiz $quiz_id pro ID: $quiz_pro_id");
        
        if (!$quiz_pro_id) {
            return array(
                'success' => false,
                'message' => "Quiz pro ID not found for quiz: $quiz_id"
            );
        }

        // Get current metadata for debugging
        $current_meta = get_post_meta($question_id);
        ldqa_debug_log("Current question metadata:", $current_meta);

        // Update question metadata
        update_post_meta($question_id, 'quiz_id', $quiz_id);
        update_post_meta($question_id, '_sfwd-question', array('sfwd-question_quiz' => $quiz_id));
        
        // Get and update question pro ID in the LearnDash tables if it exists
        $question_pro_id = get_post_meta($question_id, 'question_pro_id', true);
        ldqa_debug_log("Question $question_id pro ID: $question_pro_id");
        
        if ($question_pro_id) {
            global $wpdb;
            $result = $wpdb->update(
                $wpdb->prefix . 'learndash_pro_quiz_question',
                array('quiz_id' => $quiz_pro_id),
                array('id' => $question_pro_id),
                array('%d'),
                array('%d')
            );
            ldqa_debug_log("Database update result:", $result);
        }

        // Get updated metadata for verification
        $updated_meta = get_post_meta($question_id);
        ldqa_debug_log("Updated question metadata:", $updated_meta);

        return array(
            'success' => true,
            'message' => "Successfully associated"
        );

    } catch (Exception $e) {
        ldqa_debug_log("Error in association:", $e->getMessage());
        return array(
            'success' => false,
            'message' => $e->getMessage()
        );
    }
}