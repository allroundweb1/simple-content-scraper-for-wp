<?php
class Simple_Content_Scraper_Admin_Ajax
{
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Execute the action scheduler task 'simco_get_data'
     */
    public function simco_process_urls()
    {
        // Security check: Only administrators can execute this function
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Access denied. Only administrators can execute this function.');
        }

        // Check nonce  wp_create_nonce('simco_settings_nonce')
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'simco_settings_nonce')) {
            wp_send_json_error('Invalid nonce');
        }

        // Validate that this is an AJAX request from admin area
        if (!is_admin() || !wp_doing_ajax()) {
            wp_send_json_error('Invalid request context');
        }

        // Check if we got any $_POST['urls']
        if (!isset($_POST['urls']) || empty($_POST['urls'])) {
            wp_send_json_error('No URLs found');
        }

        // Get other args
        $title_element_id = !empty($_POST['title_element_id']) ? $_POST['title_element_id'] : '';
        $content_element_id = !empty($_POST['content_element_id']) ? $_POST['content_element_id'] : '';
        $image_element_id = !empty($_POST['image_element_id']) ? $_POST['image_element_id'] : '';
        $date_element_id = !empty($_POST['date_element_id']) ? $_POST['date_element_id'] : '';
        $category_element_id = !empty($_POST['category_element_id']) ? $_POST['category_element_id'] : '';
        $category_seperator = !empty($_POST['category_seperator']) ? $_POST['category_seperator'] : '';
        
        // New taxonomy-related fields
        $import_type = !empty($_POST['import_type']) ? $_POST['import_type'] : 'post';
        $post_type = !empty($_POST['post_type']) ? $_POST['post_type'] : 'post';
        $taxonomy = !empty($_POST['taxonomy']) ? $_POST['taxonomy'] : '';
        $enable_slug_matching = !empty($_POST['enable_slug_matching']) ? (bool) $_POST['enable_slug_matching'] : false;
        $url_slug_part = !empty($_POST['url_slug_part']) ? $_POST['url_slug_part'] : 'last';

        // Validate taxonomy requirement if import_type is taxonomy
        if ($import_type === 'taxonomy' && empty($taxonomy)) {
            wp_send_json_error('Taxonomy must be selected for taxonomy import type');
        }

        // Check if Action Scheduler is available
        if (!function_exists('as_enqueue_async_action')) {
            wp_send_json_error('Action Scheduler is not available');
        }

        // Split URLs into smaller batches to avoid Action Scheduler size limits
        $urls_string = $_POST['urls'];
        $urls_array = explode('|', trim($urls_string, '|'));
        
        // Process URLs in batches of 10 to stay under the 8000 character limit
        $batch_size = 10;
        $total_batches = ceil(count($urls_array) / $batch_size);
        $queued_tasks = 0;
        
        for ($i = 0; $i < $total_batches; $i++) {
            $batch_urls = array_slice($urls_array, $i * $batch_size, $batch_size);
            $batch_urls_string = implode('|', $batch_urls);
            
            $args = [
                'urls' => $batch_urls_string,
                'title_element_id' => $title_element_id,
                'content_element_id' => $content_element_id,
                'image_element_id' => $image_element_id,
                'date_element_id' => $date_element_id,
                'category_element_id' => $category_element_id,
                'category_seperator' => $category_seperator,
                'import_type' => $import_type,
                'post_type' => $post_type,
                'taxonomy' => $taxonomy,
                'enable_slug_matching' => $enable_slug_matching,
                'url_slug_part' => $url_slug_part
            ];
            
            // Queue each batch as a separate task
            $task_id = as_enqueue_async_action('simco_process_urls', [$args]);
            
            if ($task_id > 0) {
                $queued_tasks++;
            }
        }
        
        // Add debugging info
        error_log('SIMCO DEBUG: Total URLs: ' . count($urls_array));
        error_log('SIMCO DEBUG: Batches created: ' . $total_batches);
        error_log('SIMCO DEBUG: Tasks queued successfully: ' . $queued_tasks);

        // Return success message
        if ($queued_tasks > 0) {
            $message = sprintf(
                'Successfully queued %d batch(es) for processing %d URLs. The data is being retrieved in the background. You can go ahead and close the website in the meantime.',
                $queued_tasks,
                count($urls_array)
            );
            wp_send_json_success($message);
        } else {
            wp_send_json_error('Failed to queue any tasks. Please check the debug log for details.');
        }

        // Always die the request
        wp_die();
    }
}
