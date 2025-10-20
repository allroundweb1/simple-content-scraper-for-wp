<?php
class Simple_Content_Scraper
{
    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct()
    {
        global $wpdb;
        if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        if (defined('SIMCO_VERSION')) {
            $this->version = SIMCO_VERSION;
        } else {
            $this->version = '1.0.0';
        }

        $this->plugin_name = 'simple-content-scraper';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->optimize_plugin();
    }

    private function load_dependencies()
    {
        /**
         * Core files
         */
        require_once SIMCO_PLUGIN_DIR . 'includes/class-simple-content-scraper-loader.php';

        /**
         * Composer
         */
        require_once SIMCO_PLUGIN_DIR . 'vendor/autoload.php';

        /**
         * Action scheduler
         */
        require_once SIMCO_PLUGIN_DIR . 'vendor/woocommerce/action-scheduler/action-scheduler.php';

        /**
         * Translations
         */
        require_once SIMCO_PLUGIN_DIR . 'includes/class-simple-content-scraper-i18n.php';

        /**
         * Admin
         */
        require_once SIMCO_PLUGIN_DIR . 'admin/class-simple-content-scraper-admin.php';
        require_once SIMCO_PLUGIN_DIR . 'admin/class-simple-content-scraper-admin-settings.php';
        require_once SIMCO_PLUGIN_DIR . 'admin/class-simple-content-scraper-admin-ajax.php';

        /**
         * Data scraper
         */
        require_once SIMCO_PLUGIN_DIR . 'includes/class-simple-content-scraper-data-scraper.php';

        /**
         * Plugin optimizer
         */
        require_once SIMCO_PLUGIN_DIR . 'includes/class-simple-content-scraper-optimizer.php';

        /**
         * Create loader
         */
        $this->loader = new Simple_Content_Scraper_Loader();
    }

    /**
     * Translations
     */
    private function set_locale()
    {
        $plugin_i18n = new Simple_Content_Scraper_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain', 99);
    }

    /**
     * Admin
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new Simple_Content_Scraper_Admin($this->get_plugin_name(), $this->get_version());
        $plugin_admin_settings = new Simple_Content_Scraper_Admin_Settings($this->get_plugin_name(), $this->get_version());
        $plugin_admin_ajax = new Simple_Content_Scraper_Admin_Ajax($this->get_plugin_name(), $this->get_version());
        $plugin_data_scraper = new Simple_Content_Scraper_Data_Scraper($this->get_plugin_name(), $this->get_version());

        // Load styles and scripts
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        // Settings menu
        $this->loader->add_action('admin_menu', $plugin_admin_settings, 'simco_plugin_create_menu');

        // Load the settings menu redirect for the action scheduler sub menu
        $this->loader->add_action('admin_init', $plugin_admin_settings, 'simple_content_scraper_redirect_to_action_scheduler', 1, 0);

        // Data scraper actions
        $this->loader->add_action('simco_process_urls', $plugin_data_scraper, 'simco_process_urls', 10, 1);
        $this->loader->add_action('simco_process_single_url', $plugin_data_scraper, 'simco_process_single_url', 10, 1);

        // Register admin AJAX actions
        $this->loader->add_action('wp_ajax_simco_process_urls', $plugin_admin_ajax, 'simco_process_urls');
        
        // Add taxonomy meta fields display (for all taxonomies)
        $this->loader->add_action('edit_term', $plugin_data_scraper, 'simco_add_taxonomy_html_field', 10, 2);
        $this->loader->add_action('product_cat_edit_form_fields', $plugin_data_scraper, 'simco_show_taxonomy_html_field', 10, 1);
        $this->loader->add_action('category_edit_form_fields', $plugin_data_scraper, 'simco_show_taxonomy_html_field', 10, 1);
        $this->loader->add_action('post_tag_edit_form_fields', $plugin_data_scraper, 'simco_show_taxonomy_html_field', 10, 1);
    }

    /**
     * Optimize the plugin
     */
    private function optimize_plugin()
    {
        $plugin_optimizer = new Simple_Content_Scraper_Optimizer($this->get_plugin_name(), $this->get_version());

        // Schedule the cleanup action to run daily
        $this->loader->add_action('init', $plugin_optimizer, 'simco_cleaning_schedules');

        // Create custom action to clean old action scheduler records
        $this->loader->add_action('simco_clean_old_action_scheduler_records', $plugin_optimizer, 'simco_clean_old_action_scheduler_records');
    }

    /**
     * Run the loader
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * Get plugin name
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * Get loader
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Get version
     */
    public function get_version()
    {
        return $this->version;
    }
}
