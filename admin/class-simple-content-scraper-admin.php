<?php
class Simple_Content_Scraper_Admin
{
    private $plugin_name;
    private $version;

    /**
     * Constructor
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Styles
     */
    public function enqueue_styles()
    {
        // CSS for the whole admin area
        wp_enqueue_style($this->plugin_name . '-admin-global', plugin_dir_url(__FILE__) . 'css/simco-admin-global.css', array(), $this->version, 'all');

        // CSS for our admin pages
        $screen = get_current_screen();
        if (str_contains($screen->id, 'simple-content-scraper')) {
            // Select2 library for autocomplete multi-select
            wp_enqueue_style($this->plugin_name . '-select2', plugin_dir_url(__FILE__) . 'lib/select2-4.0.13/select2.css', array(), '4.0.13', 'all');

            // Enqueue uikit CSS file
            wp_enqueue_style($this->plugin_name . '-uikit', plugin_dir_url(__FILE__) . 'lib/uikit-3.16.22/css/uikit.min.css', array(), '3.16.22', 'all');

            // SIMCO admin pages CSS
            wp_enqueue_style($this->plugin_name . '-admin-pages', plugin_dir_url(__FILE__) . 'css/simco-admin-pages.css', array(), $this->version, 'all');
        }
    }

    /**
     * Scripts
     */
    public function enqueue_scripts()
    {
        // JS for our admin pages
        $screen = get_current_screen();
        if (str_contains($screen->id, 'simple-content-scraper')) {
            // Enqueue WordPress media scripts
            wp_enqueue_media();

            // Enable jQuery UI core and jQuery UI autocomplete
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-autocomplete');
            wp_enqueue_script('jquery-ui-draggable');
            wp_enqueue_script('jquery-ui-droppable');

            // Select2 library for autocomplete multi-select
            wp_enqueue_script($this->plugin_name . '-select2', plugin_dir_url(__FILE__) . 'lib/select2-4.0.13/select2.js', array('jquery'), '4.0.13', false);

            // Enqueue uikit JS files
            wp_enqueue_script($this->plugin_name . '-uikit', plugin_dir_url(__FILE__) . 'lib/uikit-3.16.22/js/uikit.min.js', array('jquery'), '3.16.22', false);
            wp_enqueue_script($this->plugin_name . '-uikit-icons', plugin_dir_url(__FILE__) . 'lib/uikit-3.16.22/js/uikit-icons.min.js', array('jquery', $this->plugin_name . '-uikit'), '3.16.22', false);

            // SIMCO admin pages JS
            wp_enqueue_script($this->plugin_name . '-admin-pages', plugin_dir_url(__FILE__) . 'js/simco-admin-pages.js', array('jquery', 'jquery-ui-core', 'jquery-ui-autocomplete', 'jquery-ui-draggable', 'jquery-ui-droppable', $this->plugin_name . '-select2', $this->plugin_name . '-uikit', $this->plugin_name . '-uikit-icons'), $this->version, false);

            // Create a JS object 'simple_content_scraper' for PHP variable that we want to pass to the JS
            $variable_array = array();
            $variable_array['ajax_url'] = admin_url('admin-ajax.php');
            $variable_array['ajax_settings_nonce'] = wp_create_nonce('simco_settings_nonce');
            $variable_array['backend_settings_page'] = admin_url('admin.php?page=simple-content-scraper');
            wp_localize_script($this->plugin_name . '-admin-pages', 'simple_content_scraper', $variable_array);
        }
    }
}
