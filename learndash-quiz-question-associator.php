<?php
/**
 * Plugin Name: LearnDash Quiz Question Associator
 * Description: Associate existing LearnDash questions with quizzes via CSV upload
 * Version: 1.0
 * Author: Vlad Tudorie
 */

if (!defined('ABSPATH')) {
    exit;
}

// Add menu item under LearnDash menu
function ldqa_add_admin_menu() {
    add_submenu_page(
        'learndash-lms',
        'Quiz Question Associator',
        'Quiz Question Associator',
        'manage_options',
        'ldqa-associator',
        'ldqa_admin_page'
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
            <p>Upload a CSV file with Quiz IDs in the first row and corresponding Question IDs in the second row.</p>
            <p>Example CSV format:</p>
            <pre>1301894,1301895,1301896
1306847,1306848,1306849</pre>
            
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

// Process the uploaded CSV file
function ldqa_process_csv($file) {
    $results = array(
        'success' => array(),
        'errors' => array()
    );

    // Read CSV file
    $handle = fopen($file, 'r');
    if ($handle !== FALSE) {
        $quiz_ids = fgetcsv($handle);
        $question_ids = fgetcsv($handle);
        fclose($handle);

        if (!$quiz_ids || !$question_ids || count($quiz_ids) !== count($question_ids)) {
            echo '<div class="notice notice-error"><p>Invalid CSV format. Please ensure you have two rows with matching column counts.</p></div>';
            return;
        }

        // Process each pair
        for ($i = 0; $i < count($quiz_ids); $i++) {
            $quiz_id = trim($quiz_ids[$i]);
            $question_id = trim($question_ids[$i]);
            
            $result = ldqa_associate_question_with_quiz($question_id, $quiz_id);
            
            if ($result['success']) {
                $results['success'][] = "Question $question_id successfully associated with Quiz $quiz_id";
            } else {
                $results['errors'][] = "Failed to associate Question $question_id with Quiz $quiz_id: " . $result['message'];
            }
        }

        // Display results
        if (!empty($results['success'])) {
            echo '<div class="notice notice-success"><p>' . implode('<br>', $results['success']) . '</p></div>';
        }
        if (!empty($results['errors'])) {
            echo '<div class="notice notice-error"><p>' . implode('<br>', $results['errors']) . '</p></div>';
        }
    }
}

// Associate a question with a quiz using WordPress functions
function ldqa_associate_question_with_quiz($question_id, $quiz_id) {
    // Verify posts exist and are correct type
    $question = get_post($question_id);
    $quiz = get_post($quiz_id);
    
    if (!$question || $question->post_type !== 'sfwd-question') {
        return array(
            'success' => false,
            'message' => "Invalid question ID: $question_id"
        );
    }
    
    if (!$quiz || $quiz->post_type !== 'sfwd-quiz') {
        return array(
            'success' => false,
            'message' => "Invalid quiz ID: $quiz_id"
        );
    }

    try {
        // Get quiz pro ID
        $quiz_pro_id = get_post_meta($quiz_id, 'quiz_pro_id', true);
        if (!$quiz_pro_id) {
            return array(
                'success' => false,
                'message' => "Quiz pro ID not found for quiz: $quiz_id"
            );
        }

        // Update question metadata
        update_post_meta($question_id, 'quiz_id', $quiz_id);
        update_post_meta($question_id, '_sfwd-question', array('sfwd-question_quiz' => $quiz_id));
        
        // Get and update question pro ID in the LearnDash tables if it exists
        $question_pro_id = get_post_meta($question_id, 'question_pro_id', true);
        if ($question_pro_id) {
            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . 'learndash_pro_quiz_question',
                array('quiz_id' => $quiz_pro_id),
                array('id' => $question_pro_id),
                array('%d'),
                array('%d')
            );
        }

        return array(
            'success' => true,
            'message' => "Successfully associated"
        );

    } catch (Exception $e) {
        return array(
            'success' => false,
            'message' => $e->getMessage()
        );
    }
}
