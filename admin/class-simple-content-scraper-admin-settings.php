<?php

use Nette\Caching\Cache;
use Nette\Caching\Storages\FileStorage;

class Simple_Content_Scraper_Admin_Settings
{
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function simco_plugin_create_menu()
    {
        // Menu icon URL
        $simco_menu_icon = SIMCO_PLUGIN_URL . '/admin/img/plugin-icon-mini.svg';

        // Main menu item
        add_menu_page(
            'Simple Content Scraper',
            'Simple Content Scraper',
            'administrator',
            'simple-content-scraper',
            [$this, 'simple_content_scraper_plugin_main_page'],
            $simco_menu_icon
        );

        // add_submenu_page(
        //     'simple-content-scraper',
        //     'Connectie instellingen',
        //     'Connectie instellingen',
        //     'administrator',
        //     'simple-content-scraper-connection-settings',
        //     [$this, 'simple_content_scraper_plugin_connection_settings_page'],
        //     1
        // );

        add_submenu_page(
            'simple-content-scraper',
            'Scheduled Actions',
            'Scheduled Actions',
            'administrator',
            'simple-content-scraper-redirect-to-action-scheduler',
            [$this, 'simple_content_scraper_redirect_to_action_scheduler_content'],
            99
        );

        // License sub-menu item
        // add_submenu_page(
        //     'simple-content-scraper',
        //     'License',
        //     'License',
        //     'administrator',
        //     'simple-content-scraper-license',
        //     [$this, 'simple_content_scraper_plugin_license_page']
        // );

        // Register settings
        add_action('admin_init', [$this,  'register_simple_content_scraper_plugin_settings']);
    }

    public function register_simple_content_scraper_plugin_settings()
    {
        register_setting('simple-content-scraper-plugin-settings-group', 'simco_license');

        register_setting('simple-content-scraper-plugin-settings-group', 'simco_api_secret');
        register_setting('simple-content-scraper-plugin-settings-group', 'simco_api_key');
        register_setting('simple-content-scraper-plugin-settings-group', 'simco_api_customer_name');
    }

    private function simple_content_scraper_post_type_dropdown()
    {
        // Get all post types and put them in a select dropdown
        $post_types = get_post_types();

        // Remove the post types that are not needed - List of post types to unset
        $post_types_to_unset = [
            'attachment',
            'revision',
            'nav_menu_item',
            'custom_css',
            'customize_changeset',
            'oembed_cache',
            'user_request',
            'wp_block',
            'acf-field-group',
            'acf-field',
            'wp_template',
            'wp_template_part',
            'wp_global_styles',
            'wp_navigation',
            'wp_font_family',
            'wp_font_face',
            'elementor_library',
            'elementor_snippet',
            'elementor_font',
            'elementor_icons'
        ];

        // Unset the post types that are not needed
        foreach ($post_types_to_unset as $post_type_to_unset) {
            if (isset($post_types[$post_type_to_unset])) {
                unset($post_types[$post_type_to_unset]);
            }
        }

        // Create the dropdown
        $dropdown = '<select name="simco_post_type" id="simco_post_type" class="uk-select">';
        foreach ($post_types as $post_type) {
            $dropdown .= '<option value="' . $post_type . '">' . $post_type . '</option>';
        }
        $dropdown .= '</select>';

        return $dropdown;
    }

    public function simple_content_scraper_plugin_main_page()
    {
?>
        <div class="main-settings-wrap">
            <div class="uk-section uk-section-muted ">
                <div class="uk-container uk-container-expand">
                    <h3 class="uk-margin-remove-bottom">Content Scraper</h3>
                    <hr>
                    <div class="uk-margin">
                        <label class="uk-form-label uk-margin-remove-bottom" for="simco_urls">URL's</label>
                        <p class="uk-margin-remove-top uk-margin-remove-bottom">Fill in the URL's of the pages you want to scrape, each on a new line.</p>
                        <div class="uk-form-controls">
                            <textarea class="uk-textarea" name="simco_urls" id="simco_urls" rows="10"></textarea>
                        </div>
                    </div>
                    <!-- ID/CLASS of the Title element of the URL -->
                    <div class="uk-margin">
                        <label class="uk-form-label uk-margin-remove-bottom" for="simco_title_id">Title ID/class</label>
                        <p class="uk-margin-remove-top uk-margin-remove-bottom">Fill in the ID or class name of the page title to be scraped, using .class-name for class names or #id-name for IDs.</p>
                        <div class="uk-form-controls">
                            <input class="uk-input" type="text" name="simco_title_id" id="simco_title_id" value="">
                        </div>
                    </div>
                    <!-- ID/CLASS of the Content element of the URL -->
                    <div class="uk-margin">
                        <label class="uk-form-label uk-margin-remove-bottom" for="simco_content_id">Content ID/class</label>
                        <p class="uk-margin-remove-top uk-margin-remove-bottom">Fill in the ID or class name of the page content to be scraped, using .class-name for class names or #id-name for IDs.</p>
                        <div class="uk-form-controls">
                            <input class="uk-input" type="text" name="simco_content_id" id="simco_content_id" value="">
                        </div>
                    </div>
                    <!-- ID/CLASS of the Image element of the URL -->
                    <div class="uk-margin">
                        <label class="uk-form-label uk-margin-remove-bottom" for="simco_image_id">Image ID/class</label>
                        <p class="uk-margin-remove-top uk-margin-remove-bottom">Fill in the ID or class name of the page image to be scraped, using .class-name for class names or #id-name for IDs.</p>
                        <div class="uk-form-controls">
                            <input class="uk-input" type="text" name="simco_image_id" id="simco_image_id" value="">
                        </div>
                    </div>
                    <!-- ID/CLASS of the Date element of the URL -->
                    <div class="uk-margin">
                        <label class="uk-form-label uk-margin-remove-bottom" for="simco_date_id">Date ID/class</label>
                        <p class="uk-margin-remove-top uk-margin-remove-bottom">Fill in the ID or class name of the page date to be scraped, using .class-name for class names or #id-name for IDs.</p>
                        <div class="uk-form-controls">
                            <input class="uk-input" type="text" name="simco_date_id" id="simco_date_id" value="">
                        </div>
                    </div>
                    <!-- ID/CLASS of the category element of the URL with the option to define the separator -->
                    <div class="uk-margin">
                        <!-- Container 80/20 -->
                        <div class="uk-grid uk-grid-small">
                            <div class="uk-width-1-1 uk-width-1-1@s uk-width-3-5@m">
                                <label class="uk-form-label" for="simco_category_id">Category ID/class</label>
                                <p class="uk-margin-remove-top uk-margin-remove-bottom">Fill in the ID or class name of the page category to be scraped, using .class-name for class names or #id-name for IDs.</p>
                                <div class="uk-form-controls">
                                    <input class="uk-input" type="text" name="simco_category_id" id="simco_category_id" value="">
                                </div>
                            </div>
                            <div class="uk-width-1-1 uk-width-1-1@s uk-width-2-5@m">
                                <label class="uk-form-label" for="simco_category_separator">Separator (optional)</label>
                                <p class="uk-margin-remove-top uk-margin-remove-bottom">Fill in the separator used in the category string.</p>
                                <div class="uk-form-controls">
                                    <input class="uk-input" type="text" name="simco_category_separator" id="simco_category_separator" value="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Select dropdown with all post types -->
                    <div class="uk-margin">
                        <label class="uk-form-label" for="simco_post_type">Post Type</label>
                        <p class="uk-margin-remove-top uk-margin-remove-bottom">Select the post type where the scraped content should be saved.</p>
                        <div class="uk-form-controls">
                            <?php echo $this->simple_content_scraper_post_type_dropdown(); ?>
                        </div>
                    </div>
                    <p uk-margin class="uk-flex aw-flex uk-flex-left uk-flex-middle">
                        <button class="uk-button uk-button-default uk-flex uk-flex-center uk-flex-middle uk-margin-small-right" id="simco-start-scraper"><span>Start scraper</span></button>
                    </p>
                    <div id="successAlert" class="uk-alert uk-alert-success" style="display:none;">
                        <a href="#" class="aw-alert-close" uk-icon="close"></a>
                        <p id="alertSuccessMessage"></p>
                    </div>
                    <div id="errorAlert" class="uk-alert uk-alert-danger" style="display:none;">
                        <a href="#" class="aw-alert-close" uk-icon="close"></a>
                        <p id="alertErrorMessage"></p>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

    public function simple_content_scraper_plugin_license_page()
    {
    ?>
        <div class="uk-section uk-section-muted">
            <div class="uk-container uk-container-expand">
                <h3>SIMCO_PLUGIN_BASE WP Koppeling</h3>
                <div class="uk-grid-match uk-child-width-1-3@m" uk-grid>
                    <form method="post" action="options.php" class="uk-form-horizontal">
                        <?php settings_fields('simple-content-scraper-plugin-settings-group'); ?>
                        <?php do_settings_sections('simple-content-scraper-plugin-settings-group'); ?>

                        <label class="uk-form-label" for="simco_license">Plugin Licence</label>

                        <div class="uk-inline uk-margin-small-bottom">
                            <span class="uk-form-icon uk-form-icon-flip" uk-icon="icon: lock"></span>
                            <input class="uk-input" type="text" name="simco_license" value="<?php echo esc_attr(get_option('simco_license')); ?>" />
                        </div>

                        <input type="submit" name="submit" id="submit" class="uk-button uk-button-primary" value="Submit">
                    </form>
                </div>
            </div>
        </div>
    <?php
    }

    public function simple_content_scraper_plugin_connection_settings_page()
    {
    ?>
        <div class="uk-section uk-section-muted">
            <div class="uk-container uk-container-expand">
                <h3>SIMCO API Instellingen</h3>
                <div class="uk-grid-match uk-child-width-1-3@m" uk-grid>
                    <form method="post" action="options.php" class="uk-form-horizontal">
                        <?php settings_fields('simple-content-scraper-plugin-settings-group'); ?>
                        <?php do_settings_sections('simple-content-scraper-plugin-settings-group'); ?>

                        <label class="uk-form-label" for="simco_api_secret">SIMCO API Secret</label>

                        <div class="uk-inline uk-margin-small-bottom">
                            <span class="uk-form-icon uk-form-icon-flip" uk-icon="icon: lock"></span>
                            <input class="uk-input" type="text" name="simco_api_secret" value="<?php echo esc_attr(get_option('simco_api_secret')); ?>" />
                        </div>

                        <label class="uk-form-label" for="simco_api_key">SIMCO API Key</label>

                        <div class="uk-inline uk-margin-small-bottom">
                            <span class="uk-form-icon uk-form-icon-flip" uk-icon="icon: lock"></span>
                            <input class="uk-input" type="text" name="simco_api_key" value="<?php echo esc_attr(get_option('simco_api_key')); ?>" />
                        </div>

                        <label class="uk-form-label" for="simco_api_customer_name">SIMCO Customer Name</label>

                        <div class="uk-inline uk-margin-small-bottom">
                            <span class="uk-form-icon uk-form-icon-flip" uk-icon="icon: lock"></span>
                            <input class="uk-input" type="text" name="simco_api_customer_name" value="<?php echo esc_attr(get_option('simco_api_customer_name')); ?>" />
                        </div>

                        <input type="submit" name="submit" id="submit" class="uk-button uk-button-primary" value="Opslaan">
                    </form>
                </div>
            </div>
        </div>
<?php
    }

    public function simple_content_scraper_redirect_to_action_scheduler_content()
    {
        // This function is used to redirect to the action scheduler page
        // The logic of the redirect is handled in the main plugin file
        // See: $this->loader->add_action('admin_init', $plugin_admin_settings, 'simple_content_scraper_redirect_to_action_scheduler', 1, 0);
        return '';
    }

    public function simple_content_scraper_redirect_to_action_scheduler()
    {
        // Check if page that is requested is in the admin side and that the page is ?page=simple-content-scraper-redirect-to-action-scheduler
        if (!is_admin() || empty($_GET['page']) || $_GET['page'] !== 'simple-content-scraper-redirect-to-action-scheduler') {
            return;
        }

        // Perform the redirect
        wp_redirect(admin_url('tools.php?page=action-scheduler') . '&status=pending');
        exit;
    }
}
