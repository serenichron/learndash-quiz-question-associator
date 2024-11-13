<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php echo esc_html__('LearnDash Quiz Question Associator', LDQA_TEXT_DOMAIN); ?></h1>
    
    <div class="card">
        <h2><?php echo esc_html__('Upload CSV File', LDQA_TEXT_DOMAIN); ?></h2>
        <p><?php echo esc_html__('Upload a CSV file with each row containing a Quiz ID and its corresponding Question ID.', LDQA_TEXT_DOMAIN); ?></p>
        <p><?php echo esc_html__('Example CSV format:', LDQA_TEXT_DOMAIN); ?></p>
        <pre><?php echo esc_html("1306846,1306847\n1306849,1306850\n1307452,1307453"); ?></pre>
        
        <p><strong><?php echo esc_html__('Format Details:', LDQA_TEXT_DOMAIN); ?></strong></p>
        <ul style="list-style-type: disc; margin-left: 20px;">
            <li><?php echo esc_html__('Each row should contain: Quiz ID, Question ID', LDQA_TEXT_DOMAIN); ?></li>
            <li><?php echo esc_html__('The IDs should be existing Quiz and Question IDs in your LearnDash system', LDQA_TEXT_DOMAIN); ?></li>
            <li><?php echo esc_html__('First column: Quiz IDs', LDQA_TEXT_DOMAIN); ?></li>
            <li><?php echo esc_html__('Second column: Question IDs', LDQA_TEXT_DOMAIN); ?></li>
        </ul>

        <?php 
        // Display any error messages or success notices
        settings_errors('ldqa_messages'); 
        ?>
        
        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('ldqa_upload_csv'); ?>
            <input type="file" 
                   name="ldqa_csv_file" 
                   accept=".csv,text/csv" 
                   required 
                   class="regular-text">
            <p class="description">
                <?php echo esc_html__('Maximum file size: 1MB', LDQA_TEXT_DOMAIN); ?>
            </p>
            <p class="submit">
                <?php 
                submit_button(
                    __('Process CSV', LDQA_TEXT_DOMAIN), 
                    'primary', 
                    'ldqa_submit', 
                    false
                ); 
                ?>
            </p>
        </form>
    </div>
</div>