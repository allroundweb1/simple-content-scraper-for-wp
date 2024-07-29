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
        // Check nonce  wp_create_nonce('simco_settings_nonce')
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'simco_settings_nonce')) {
            wp_send_json_error('Invalid nonce');
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
        $post_type = !empty($_POST['post_type']) ? $_POST['post_type'] : 'post';

        // Create delete request via action scheduler for each simco_panden
        $args = array(
            'process_data' => [
                'urls' => $_POST['urls'],
                'title_element_id' => $title_element_id,
                'content_element_id' => $content_element_id,
                'image_element_id' => $image_element_id,
                'date_element_id' => $date_element_id,
                'category_element_id' => $category_element_id,
                'category_seperator' => $category_seperator,
                'post_type' => $post_type
            ]
        );
        as_enqueue_async_action('simco_process_urls', $args);

        // Return success message
        wp_send_json_success('The data is being retrieved. This may take some time. You can go ahead and close the website in the meantime.');

        // Always die the request
        wp_die();
    }
}
