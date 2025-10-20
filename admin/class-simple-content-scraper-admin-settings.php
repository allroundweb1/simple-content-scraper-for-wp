<?php

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

        // Submenu to navigate to the action scheduler
        add_submenu_page(
            'simple-content-scraper',
            'Scheduled Actions',
            'Scheduled Actions',
            'administrator',
            'simple-content-scraper-redirect-to-action-scheduler',
            [$this, 'simple_content_scraper_redirect_to_action_scheduler_content'],
            99
        );

        // Register settings
        add_action('admin_init', [$this,  'register_simple_content_scraper_plugin_settings']);
    }

    public function register_simple_content_scraper_plugin_settings()
    {
        // No settings (yet)
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

    private function simple_content_scraper_taxonomy_dropdown()
    {
        // Get all public taxonomies
        $taxonomies = get_taxonomies(['public' => true], 'objects');
        
        // Filter out some built-in taxonomies that are not typically used for content import
        $taxonomies_to_exclude = ['nav_menu', 'link_category', 'post_format'];
        
        // Create the dropdown
        $dropdown = '<select name="simco_taxonomy" id="simco_taxonomy" class="uk-select">';
        $dropdown .= '<option value="">Select taxonomy...</option>';
        
        foreach ($taxonomies as $taxonomy) {
            if (!in_array($taxonomy->name, $taxonomies_to_exclude)) {
                $dropdown .= '<option value="' . $taxonomy->name . '">' . $taxonomy->label . ' (' . $taxonomy->name . ')</option>';
            }
        }
        $dropdown .= '</select>';

        return $dropdown;
    }

    private function simple_content_scraper_import_type_dropdown()
    {
        $dropdown = '<select name="simco_import_type" id="simco_import_type" class="uk-select">';
        $dropdown .= '<option value="post">Post</option>';
        $dropdown .= '<option value="taxonomy">Taxonomy</option>';
        $dropdown .= '</select>';

        return $dropdown;
    }

    private function simple_content_scraper_url_slug_part_dropdown()
    {
        $dropdown = '<select name="simco_url_slug_part" id="simco_url_slug_part" class="uk-select">';
        $dropdown .= '<option value="last">Last part</option>';
        $dropdown .= '<option value="second_last">Second to last part</option>';
        $dropdown .= '<option value="third_last">Third to last part</option>';
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
                    <!-- URL's to scrape -->
                    <div class="uk-margin">
                        <label class="uk-form-label uk-margin-remove-bottom" for="simco_urls">URL's</label>
                        <p class="uk-margin-remove-top uk-margin-remove-bottom">Fill in the URL's of the pages you want to scrape, each on a new line.</p>
                        <div class="uk-form-controls">
                            <textarea class="uk-textarea" name="simco_urls" id="simco_urls" rows="10"></textarea>
                        </div>
                    </div>
                    <!-- ID/CLASS/TAG of the Title element of the URL -->
                    <div class="uk-margin">
                        <label class="uk-form-label uk-margin-remove-bottom" for="simco_title_id">Title ID/class/tag</label>
                        <p class="uk-margin-remove-top uk-margin-remove-bottom">Fill in the ID, class name, or HTML tag to scrape the page title. Use .class-name for classes, #id-name for IDs, or just the tag name like h1, h2, etc.</p>
                        <div class="uk-form-controls">
                            <input class="uk-input" type="text" name="simco_title_id" id="simco_title_id" value="" placeholder="e.g. h1, .title, #main-title">
                        </div>
                    </div>
                    <!-- ID/CLASS/TAG of the Content element of the URL -->
                    <div class="uk-margin">
                        <label class="uk-form-label uk-margin-remove-bottom" for="simco_content_id">Content ID/class/tag</label>
                        <p class="uk-margin-remove-top uk-margin-remove-bottom">Fill in the ID, class name, or HTML tag to scrape the page content. Use .class-name for classes, #id-name for IDs, or just the tag name like article, section, div, etc.</p>
                        <div class="uk-form-controls">
                            <input class="uk-input" type="text" name="simco_content_id" id="simco_content_id" value="" placeholder="e.g. article, .content, #main-content">
                        </div>
                    </div>
                    <!-- ID/CLASS/TAG of the Image element of the URL -->
                    <div class="uk-margin">
                        <label class="uk-form-label uk-margin-remove-bottom" for="simco_image_id">Image ID/class/tag</label>
                        <p class="uk-margin-remove-top uk-margin-remove-bottom">Fill in the ID, class name, or HTML tag to scrape the page image. Use .class-name for classes, #id-name for IDs, or just the tag name like img, figure, picture, etc.</p>
                        <div class="uk-form-controls">
                            <input class="uk-input" type="text" name="simco_image_id" id="simco_image_id" value="" placeholder="e.g. img, .hero-image, #featured-image">
                        </div>
                    </div>
                    <!-- ID/CLASS/TAG of the Date element of the URL -->
                    <div class="uk-margin">
                        <label class="uk-form-label uk-margin-remove-bottom" for="simco_date_id">Date ID/class/tag</label>
                        <p class="uk-margin-remove-top uk-margin-remove-bottom">Fill in the ID, class name, or HTML tag to scrape the page date. Use .class-name for classes, #id-name for IDs, or just the tag name like time, span, p, etc.</p>
                        <div class="uk-form-controls">
                            <input class="uk-input" type="text" name="simco_date_id" id="simco_date_id" value="" placeholder="e.g. time, .date, #publish-date">
                        </div>
                    </div>
                    <!-- ID/CLASS of the category element of the URL with the option to define the separator -->
                    <div class="uk-margin">
                        <div class="uk-grid uk-grid-small">
                            <div class="uk-width-1-1 uk-width-1-1@s uk-width-3-5@m">
                                <label class="uk-form-label" for="simco_category_id">Category ID/class/tag</label>
                                <p class="uk-margin-remove-top uk-margin-remove-bottom">Fill in the ID, class name, or HTML tag to scrape the page categories. Use .class-name for classes, #id-name for IDs, or just the tag name like span, nav, ul, etc.</p>
                                <div class="uk-form-controls">
                                    <input class="uk-input" type="text" name="simco_category_id" id="simco_category_id" value="" placeholder="e.g. nav, .categories, #tags">
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
                    <!-- Import type selection -->
                    <div class="uk-margin">
                        <label class="uk-form-label" for="simco_import_type">Import Type</label>
                        <p class="uk-margin-remove-top uk-margin-remove-bottom">Choose whether to import as posts or taxonomies.</p>
                        <div class="uk-form-controls">
                            <?php echo $this->simple_content_scraper_import_type_dropdown(); ?>
                        </div>
                    </div>
                    <!-- Select dropdown with all post types -->
                    <div class="uk-margin" id="simco_post_type_section">
                        <label class="uk-form-label" for="simco_post_type">Post Type</label>
                        <p class="uk-margin-remove-top uk-margin-remove-bottom">Select the post type where the scraped content should be saved.</p>
                        <div class="uk-form-controls">
                            <?php echo $this->simple_content_scraper_post_type_dropdown(); ?>
                        </div>
                    </div>
                    <!-- Select dropdown with all taxonomies (hidden by default) -->
                    <div class="uk-margin" id="simco_taxonomy_section" style="display: none;">
                        <label class="uk-form-label" for="simco_taxonomy">Taxonomy</label>
                        <p class="uk-margin-remove-top uk-margin-remove-bottom">Select the taxonomy where the scraped content should be saved.</p>
                        <div class="uk-form-controls">
                            <?php echo $this->simple_content_scraper_taxonomy_dropdown(); ?>
                        </div>
                    </div>
                    <!-- URL slug matching settings (hidden by default) -->
                    <div class="uk-margin" id="simco_slug_matching_section" style="display: none;">
                        <div class="uk-grid uk-grid-small">
                            <div class="uk-width-1-1 uk-width-1-2@s">
                                <label class="uk-form-label" for="simco_enable_slug_matching">
                                    <input class="uk-checkbox" type="checkbox" name="simco_enable_slug_matching" id="simco_enable_slug_matching" value="1"> Enable slug matching
                                </label>
                                <p class="uk-margin-remove-top uk-margin-remove-bottom">Match existing taxonomies by URL slug part.</p>
                            </div>
                            <div class="uk-width-1-1 uk-width-1-2@s" id="simco_url_slug_part_section" style="display: none;">
                                <label class="uk-form-label" for="simco_url_slug_part">URL Slug Part</label>
                                <p class="uk-margin-remove-top uk-margin-remove-bottom">Choose which part of the URL to use for matching.</p>
                                <div class="uk-form-controls">
                                    <?php echo $this->simple_content_scraper_url_slug_part_dropdown(); ?>
                                </div>
                            </div>
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
