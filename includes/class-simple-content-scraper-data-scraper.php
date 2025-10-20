<?php

use GuzzleHttp\Client; // For the Guzzle HTTP client
use Nette\Caching\Cache; // For the Nette caching (We can use this later on for a cache system)
use Nette\Caching\Storages\FileStorage; // For the Nette caching (We can use this later on for a cache system)

class Simple_Content_Scraper_Data_Scraper
{
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Process the URL's
     */
    public function simco_process_urls($args = [])
    {
        if (empty($args) || !is_array($args)) {
            // Throw an error
            throw new Exception('Error: (simco_process_urls) No $args found.');
        }

        // Check if we got any $args['urls']
        if (!isset($args['urls']) || empty($args['urls'])) {
            // Throw an error
            throw new Exception('Error: (simco_process_urls) No URLs found.');
        }

        // Check if 'urls' contains a | seperator
        if (strpos($args['urls'], '|') !== false) {
            // Strip from the beginning and end
            $args['urls'] = trim($args['urls'], '|');

            // Check if we're not empty now, and if we still got | left
            if (!empty($args['urls']) && strpos($args['urls'], '|') !== false) {
                // Explode the URL's by the | seperator
                $args['urls'] = explode('|', $args['urls']);
            } else {
                // Convert the URL's to an array
                $args['urls'] = [$args['urls']];
            }
        } else {
            // Convert the URL's to an array
            $args['urls'] = [$args['urls']];
        }

        // Get other args
        $title_element_id = !empty($args['title_element_id']) ? $args['title_element_id'] : '';
        $content_element_id = !empty($args['content_element_id']) ? $args['content_element_id'] : '';
        $image_element_id = !empty($args['image_element_id']) ? $args['image_element_id'] : '';
        $date_element_id = !empty($args['date_element_id']) ? $args['date_element_id'] : '';
        $category_element_id = !empty($args['category_element_id']) ? $args['category_element_id'] : '';
        $category_seperator = !empty($args['category_seperator']) ? $args['category_seperator'] : '';
        $import_type = !empty($args['import_type']) ? $args['import_type'] : 'post';
        $post_type = !empty($args['post_type']) ? $args['post_type'] : 'post';
        $taxonomy = !empty($args['taxonomy']) ? $args['taxonomy'] : '';
        $enable_slug_matching = !empty($args['enable_slug_matching']) ? $args['enable_slug_matching'] : false;
        $url_slug_part = !empty($args['url_slug_part']) ? $args['url_slug_part'] : 'last';

        // Loop through the URL's
        foreach ($args['urls'] as $url) {
            // Plan the single url action
            as_enqueue_async_action('simco_process_single_url', [[
                'post_id'                   => null,
                'url'                       => $url,
                'title_element_id'          => $title_element_id,
                'content_element_id'        => $content_element_id,
                'image_element_id'          => $image_element_id,
                'date_element_id'           => $date_element_id,
                'category_element_id'       => $category_element_id,
                'category_seperator'        => $category_seperator,
                'import_type'               => $import_type,
                'post_type'                 => $post_type,
                'taxonomy'                  => $taxonomy,
                'enable_slug_matching'      => $enable_slug_matching,
                'url_slug_part'             => $url_slug_part
            ]]);
        }
    }

    /**
     * Process the single URL
     */
    public function simco_process_single_url($args = [])
    {
        if (empty($args) || !is_array($args)) {
            // Throw an error
            throw new Exception('Error: (simco_process_single_url) No $args found.');
        }

        // Check if we got any $args['url']
        if (!isset($args['url']) || empty($args['url'])) {
            // Throw an error
            throw new Exception('Error: (simco_process_single_url) No URL found.');
        }

        // Get the post_id
        $post_id = !empty($args['post_id']) ? $args['post_id'] : null;

        // Get the URL
        $url = $args['url'];

        // Get the title element ID
        $title_element_id = !empty($args['title_element_id']) ? $args['title_element_id'] : '';

        // Get the content element ID
        $content_element_id = !empty($args['content_element_id']) ? $args['content_element_id'] : '';

        // Get the image element ID
        $image_element_id = !empty($args['image_element_id']) ? $args['image_element_id'] : '';

        // Get the date element ID
        $date_element_id = !empty($args['date_element_id']) ? $args['date_element_id'] : '';

        // Get the category element ID
        $category_element_id = !empty($args['category_element_id']) ? $args['category_element_id'] : '';

        // Get the category seperator
        $category_seperator = !empty($args['category_seperator']) ? $args['category_seperator'] : '';

        // Get import type and related fields
        $import_type = !empty($args['import_type']) ? $args['import_type'] : 'post';
        $post_type = !empty($args['post_type']) ? $args['post_type'] : 'post';
        $taxonomy = !empty($args['taxonomy']) ? $args['taxonomy'] : '';
        $enable_slug_matching = !empty($args['enable_slug_matching']) ? $args['enable_slug_matching'] : false;
        $url_slug_part = !empty($args['url_slug_part']) ? $args['url_slug_part'] : 'last';

        // Scrape the URL and get the data from this URL based on the Element ID's
        $scraped_data = $this->simco_scrape_url($url, $title_element_id, $content_element_id, $image_element_id, $date_element_id, $category_element_id, $category_seperator);

        // Handle based on import type
        if ($import_type === 'taxonomy') {
            // Handle taxonomy import
            $this->simco_create_or_update_taxonomy($url, $scraped_data, $taxonomy, $enable_slug_matching, $url_slug_part);
        } else {
            // Handle post import (existing functionality)
            $this->simco_create_or_update_post($post_id, $scraped_data, $post_type);
        }
    }

    /**
     * Function to scrape the URL
     */
    private function simco_scrape_url($url, $title_element_id, $content_element_id, $image_element_id, $date_element_id, $category_element_id, $category_seperator)
    {
        // Check if the URL is empty
        if (empty($url)) {
            // Throw an error
            throw new Exception('Error: (simco_scrape_url) No $url found.');
        }

        // Return args empty
        $title = '';
        $content = '';
        $image = '';
        $date = '';
        $category = '';
        $categories = [];

        // Create a new Guzzle client
        $client = new Client();

        // Get the response from the URL
        $response = $client->request('GET', $url);

        // Get the body of the response
        $body = $response->getBody();

        // Get the body contents
        $body_contents = $body->getContents();

        // Load the body contents into a new DOMDocument
        $dom = new DOMDocument();
        @$dom->loadHTML($body_contents);

        // Create a new DOMXPath
        $xpath = new DOMXPath($dom);

        // Build the xpath query with the Class, ID, or HTML tag in $title_element_id
        if (!empty($title_element_id) && strpos($title_element_id, '.') !== false) {
            // Strip . from the beginning
            $title_element_id = ltrim($title_element_id, '.');

            $title_element_xpath_query = "//*[contains(@class, '$title_element_id')]";
        } elseif (!empty($title_element_id) && strpos($title_element_id, '#') !== false) {
            // Strip # from the beginning
            $title_element_id = ltrim($title_element_id, '#');

            $title_element_xpath_query = "//*[@id='$title_element_id']";
        } elseif (!empty($title_element_id) && preg_match('/^[a-zA-Z][a-zA-Z0-9]*$/', $title_element_id)) {
            // HTML tag (like h1, h2, p, div, etc.)
            $title_element_xpath_query = "//$title_element_id";
        } elseif (!empty($title_element_id)) {
            $title_element_xpath_query = "//*[contains(@class, '$title_element_id')] | //*[@id='$title_element_id']";
        }

        // Get the title element
        $title_element = $xpath->query($title_element_xpath_query);

        // Build the xpath query with the Class, ID, or HTML tag in $content_element_id
        if (!empty($content_element_id) && strpos($content_element_id, '.') !== false) {
            // Strip . from the beginning
            $content_element_id = ltrim($content_element_id, '.');

            $content_element_xpath_query = "//*[contains(@class, '$content_element_id')]";
        } elseif (!empty($content_element_id) && strpos($content_element_id, '#') !== false) {
            // Strip # from the beginning
            $content_element_id = ltrim($content_element_id, '#');

            $content_element_xpath_query = "//*[@id='$content_element_id']";
        } elseif (!empty($content_element_id) && preg_match('/^[a-zA-Z][a-zA-Z0-9]*$/', $content_element_id)) {
            // HTML tag (like div, p, article, section, etc.)
            $content_element_xpath_query = "//$content_element_id";
        } elseif (!empty($content_element_id)) {
            $content_element_xpath_query = "//*[contains(@class, '$content_element_id')] | //*[@id='$content_element_id']";
        }

        // Get the content element
        $content_element = $xpath->query($content_element_xpath_query);

        // Build the xpath query with the Class, ID, or HTML tag in $image_element_id
        $image_element_xpath_query = '';
        if (!empty($image_element_id) && strpos($image_element_id, '.') !== false) {
            // Strip . from the beginning
            $image_element_id = ltrim($image_element_id, '.');

            $image_element_xpath_query = "//*[contains(@class, '$image_element_id')]";
        } elseif (!empty($image_element_id) && strpos($image_element_id, '#') !== false) {
            // Strip # from the beginning
            $image_element_id = ltrim($image_element_id, '#');

            $image_element_xpath_query = "//*[@id='$image_element_id']";
        } elseif (!empty($image_element_id) && preg_match('/^[a-zA-Z][a-zA-Z0-9]*$/', $image_element_id)) {
            // HTML tag (like img, figure, picture, etc.)
            $image_element_xpath_query = "//$image_element_id";
        } elseif (!empty($image_element_id)) {
            $image_element_xpath_query = "//*[contains(@class, '$image_element_id')] | //*[@id='$image_element_id']";
        }

        // Get the image element
        $image_element = !empty($image_element_xpath_query) ? $xpath->query($image_element_xpath_query) : null;

        // Build the xpath query with the Class, ID, or HTML tag in $date_element_id
        $date_element_xpath_query = '';
        if (!empty($date_element_id) && strpos($date_element_id, '.') !== false) {
            // Strip . from the beginning
            $date_element_id = ltrim($date_element_id, '.');

            $date_element_xpath_query = "//*[contains(@class, '$date_element_id')]";
        } elseif (!empty($date_element_id) && strpos($date_element_id, '#') !== false) {
            // Strip # from the beginning
            $date_element_id = ltrim($date_element_id, '#');

            $date_element_xpath_query = "//*[@id='$date_element_id']";
        } elseif (!empty($date_element_id) && preg_match('/^[a-zA-Z][a-zA-Z0-9]*$/', $date_element_id)) {
            // HTML tag (like time, span, p, etc.)
            $date_element_xpath_query = "//$date_element_id";
        } elseif (!empty($date_element_id)) {
            $date_element_xpath_query = "//*[contains(@class, '$date_element_id')] | //*[@id='$date_element_id']";
        }

        // Get the date element
        $date_element = !empty($date_element_xpath_query) ? $xpath->query($date_element_xpath_query) : null;

        // Build the xpath query with the Class, ID, or HTML tag in $category_element_id
        $category_element_xpath_query = '';
        if (!empty($category_element_id) && strpos($category_element_id, '.') !== false) {
            // Strip . from the beginning
            $category_element_id = ltrim($category_element_id, '.');

            $category_element_xpath_query = "//*[contains(@class, '$category_element_id')]";
        } elseif (!empty($category_element_id) && strpos($category_element_id, '#') !== false) {
            // Strip # from the beginning
            $category_element_id = ltrim($category_element_id, '#');

            $category_element_xpath_query = "//*[@id='$category_element_id']";
        } elseif (!empty($category_element_id) && preg_match('/^[a-zA-Z][a-zA-Z0-9]*$/', $category_element_id)) {
            // HTML tag (like span, p, div, etc.)
            $category_element_xpath_query = "//$category_element_id";
        } elseif (!empty($category_element_id)) {
            $category_element_xpath_query = "//*[contains(@class, '$category_element_id')] | //*[@id='$category_element_id']";
        }

        // Get the category element
        $category_element = !empty($category_element_xpath_query) ? $xpath->query($category_element_xpath_query) : null;

        // Check if the title element is not empty
        if (!empty($title_element) && !empty($title_element->item(0))) {
            // Get the title
            $title = $title_element->item(0)->nodeValue;
        }

        // Check if the content element is not empty
        if (!empty($content_element) && !empty($content_element->item(0))) {
            // Get the content
            // $content = $content_element->item(0)->nodeValue;

            // Get the item HTML
            $content = $dom->saveHTML($content_element->item(0));

            // Check if the content has src images
            if (strpos($content, '<img') !== false) {
                // Get the images from the content
                $doc = new DOMDocument();
                @$doc->loadHTML($content);
                $xpath = new DOMXPath($doc);
                $images = $xpath->query('//img');

                // Loop through the images
                foreach ($images as $image) {
                    // Get the image URL
                    $image_url = $image->getAttribute('src');
                    $original_image_url = $image_url;

                    // Check if the image URL is not empty
                    if (!empty($image_url)) {
                        // Check if the URL contains http:// or https:// other wise get the base URL from the original page URL and then add the image URL
                        if (strpos($image_url, 'http://') === false && strpos($image_url, 'https://') === false) {
                            // Get the base URL from the original page URL
                            $base_url = parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST);

                            // Add the image URL to the base URL
                            $image_url = $base_url . $image_url;
                        }

                        // Get the image ID
                        $image_id = $this->simco_upload_image($image_url, null, 'Afbeelding', 'Afbeelding', 'image_');

                        // Check if the image ID is not empty
                        if (!empty($image_id)) {
                            // Replace the image URL with the image ID
                            $content = str_replace($original_image_url, wp_get_attachment_url($image_id), $content);
                        }
                    }
                }
            }
        }

        // Check if the image element is not empty
        if (!empty($image_element) && $image_element->length > 0 && !empty($image_element->item(0))) {
            // Get the image URL
            $image = $image_element->item(0)->getAttribute('src');
        }

        // Check if the date element is not empty
        if (!empty($date_element) && $date_element->length > 0 && !empty($date_element->item(0))) {
            // Get the date
            $date = $date_element->item(0)->nodeValue;
        } else {
            // Use today's date if no date element found
            $date = date('d-m-Y');
        }

        // Check if the category element is not empty
        if (!empty($category_element) && $category_element->length > 0 && !empty($category_element->item(0))) {
            // Get the category
            $category = $category_element->item(0)->nodeValue;
        }

        // Check if the category is not empty
        if (!empty($category) && !empty($category_seperator)) {
            // Explode the category by the category seperator
            $categories = explode($category_seperator, $category);
        } elseif (!empty($category)) {
            // Add the category to the categories array
            $categories[] = $category;
        }

        // Create the scraped data array
        $scraped_data = [
            'title' => $title,
            'content' => $content,
            'image' => $image,
            'date' => $date,
            'categories' => $categories
        ];

        return $scraped_data;
    }

    /**
     * Create or update post
     */
    private function simco_create_or_update_post($post_id = null, $scraped_data = null, $post_type = 'post')
    {
        // Check if $post_data is not empty, otherwise throw an error
        if (empty($scraped_data)) {
            // Trow an error
            throw new Exception('Error: (simco_create_or_update_post) No $scraped_data found.');
        }

        // Check if $scraped_data contains the title, content, image, date and categories
        $title = !empty($scraped_data['title']) ? $scraped_data['title'] : '';
        $content = !empty($scraped_data['content']) ? $scraped_data['content'] : '';
        $image = !empty($scraped_data['image']) ? $scraped_data['image'] : '';
        $date = !empty($scraped_data['date']) ? $scraped_data['date'] : '';
        $categories = !empty($scraped_data['categories']) ? $scraped_data['categories'] : [];

        // If $post_id is null we create a new post, otherwise we update the post
        if (empty($post_id)) {
            // Create the post
            $post_id = wp_insert_post([
                'post_title' => $title,
                'post_type' => $post_type,
                'post_status' => 'publish'
            ]);
        } else {
            // Update the post title of the existing post
            wp_update_post([
                'ID' => $post_id,
                'post_title' => $title,
            ]);
        }

        // Update the content if not empty
        if (!empty($content)) {
            // Update the post content
            wp_update_post([
                'ID' => $post_id,
                'post_content' => $content,
            ]);
        }

        // Update the image if not empty
        if (!empty($image)) {
            // Upload the image
            $image_id = $this->simco_upload_image($image, $post_id, 'Afbeelding', 'Afbeelding', 'image_');

            // Update the post thumbnail
            set_post_thumbnail($post_id, $image_id);
        }

        // Update the date if not empty
        if (!empty($date)) {
            $date = DateTime::createFromFormat('d-m-Y', $date);
            $formattedDate = $date->format('Y-m-d'); // Convert to YYYY-MM-DD format

            // Update the post date
            wp_update_post([
                'ID' => $post_id,
                'post_date' => $formattedDate,
            ]);
        }

        // Update the categories if not empty
        if (!empty($categories)) {
            // Get the category ID's
            $category_ids = [];

            foreach ($categories as $category) {
                // Check if the category is not empty
                if (!empty($category)) {
                    // Get the category by name
                    $category_id = get_cat_ID($category);

                    // Check if the category ID is not empty
                    if (!empty($category_id)) {
                        // Add the category ID to the category ID's array
                        $category_ids[] = $category_id;
                    } else {
                        // Create the category
                        $new_cat = wp_insert_term($category, 'category');

                        // Check if the $new_cat is not empty and no WP error
                        if (!empty($new_cat) && !is_wp_error($new_cat)) {
                            $category_ids[] = $new_cat['term_id'];
                        }
                    }
                }
            }

            // Update the post categories
            wp_set_post_categories($post_id, $category_ids);
        }

        // Check if the post_id is empty
        if (empty($post_id)) {
            // Trow an error
            throw new Exception('Error: (simco_create_or_update_post) Could not create the post.');
        }

        return;
    }

    /**
     * Upload image
     */
    private function simco_upload_image($file_url, $post_id, $desc = 'Afbeelding', $alt = 'Afbeelding', $file_name_prefix = '')
    {
        preg_match('/[^\?]+\.(jpe?g|jpe|gif|png|webp)\b/i', $file_url, $matches);
        if (!$matches) {
            return new WP_Error('image_sideload_failed', __('Invalid image URL'));
        }

        // Require media, file and image files
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        // Use $wpdb
        global $wpdb;

        // Attachment placeholder
        $attachment_id = null;

        // Encode the file URL for image comparison in the database using Base64
        $file_url_base64 = base64_encode($file_url);

        // Create new table if it doesn't exist
        $table_name = $wpdb->prefix . 'simco_already_imported_images';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                already_imported_image_url VARCHAR(255) NOT NULL,
                attachment_id mediumint(9) NOT NULL,
                UNIQUE KEY id (id)
            );";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }

        // Comparing the Base64 encoded image URL to check if the image has already been imported, we limit this to one result
        $already_imported_image = $wpdb->get_row("SELECT * FROM $table_name WHERE already_imported_image_url = '$file_url_base64' LIMIT 1");

        // Check if is wp error
        if (is_wp_error($already_imported_image)) {
            // Throw an error
            throw new Exception('Error: (simco_upload_image)' . $already_imported_image->get_error_message());
        }

        // Get the post with attachment id $already_imported_image->attachment_id
        $attachment_post = (!empty($already_imported_image) && !empty($already_imported_image->attachment_id)) ? get_post($already_imported_image->attachment_id) : null;

        // If the image is already imported, return the attachment ID
        if (!empty($already_imported_image) && !empty($already_imported_image->attachment_id) && !empty($attachment_post) && !empty($attachment_post->ID) && $attachment_post->ID == $already_imported_image->attachment_id) {
            return (int)$already_imported_image->attachment_id;
        } else {
            // Check if there is an ID, then remove the row from the table and continue importing the image
            if (!empty($already_imported_image) && !empty($already_imported_image->id)) {
                // If the attachment doesn't exist anymore, remove the row from the table and continue importing the image
                $wpdb->delete($table_name, array('id' => $already_imported_image->id));
            }
        }

        // If the image is not imported yet, import it and save the attachment ID
        $file_array = array();
        $file_array['name'] = $file_name_prefix . basename($matches[0]);
        $file_array['tmp_name'] = download_url($file_url);

        if (is_wp_error($file_array['tmp_name'])) {
            // Trow an error
            // throw new Exception('Error: (simco_upload_image) ' . $file_array['tmp_name']->get_error_message());
            return '';
        }

        $attachment_id = media_handle_sideload($file_array, $post_id, $desc);

        if (is_wp_error($attachment_id)) {
            @unlink($file_array['tmp_name']);

            // Trow an error
            // throw new Exception('Error: (simco_upload_image) ' . $attachment_id->get_error_message());
            return '';
        }

        if (wp_attachment_is_image($attachment_id)) {
            $image_desc = $desc;
            $image_desc = preg_replace('%\s*[-_\s]+\s*%', ' ',  $image_desc);
            $image_desc = ucwords(strtolower($image_desc));

            $image_meta = array(
                'ID' => $attachment_id, // Specify the image (ID) to be updated
                'post_title' => $image_desc, // Set image Title to sanitized title
                'post_excerpt' => $image_desc, // Set image Caption (Excerpt) to sanitized title
                'post_content' => $image_desc, // Set image Description (Content) to sanitized title
            );
            wp_update_post($image_meta);

            $image_alt = $alt;
            $image_alt = preg_replace('%\s*[-_\s]+\s*%', ' ',  $image_alt);
            $image_alt = ucwords(strtolower($image_alt));
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $image_alt);
        }

        // Save the imported image URL and attachment ID
        $wpdb->insert($table_name, array(
            'already_imported_image_url' => $file_url_base64, // Use Base64 encoded URL for later comparison
            'attachment_id' => $attachment_id
        ));

        return $attachment_id;
    }

    /**
     * Extract URL slug part for taxonomy matching
     */
    private function simco_extract_url_slug_part($url, $part_position = 'last')
    {
        // Parse the URL to get the path
        $parsed_url = parse_url($url);
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        
        // Remove leading and trailing slashes, then split by slashes
        $path = trim($path, '/');
        $parts = explode('/', $path);
        
        // Filter out empty parts
        $parts = array_filter($parts, function($part) {
            return !empty($part);
        });
        
        // Re-index array after filtering
        $parts = array_values($parts);
        
        if (empty($parts)) {
            return '';
        }
        
        // Get the requested part
        switch ($part_position) {
            case 'last':
                return end($parts);
            case 'second_last':
                return count($parts) >= 2 ? $parts[count($parts) - 2] : '';
            case 'third_last':
                return count($parts) >= 3 ? $parts[count($parts) - 3] : '';
            default:
                return end($parts);
        }
    }

    /**
     * Find existing taxonomy by slug match
     */
    private function simco_find_taxonomy_by_slug($taxonomy, $slug_part)
    {
        if (empty($slug_part) || empty($taxonomy)) {
            return null;
        }

        // Get all terms for the taxonomy
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'number' => 0 // Get all terms
        ]);

        if (is_wp_error($terms)) {
            return null;
        }

        // Look for exact slug match or partial slug match
        foreach ($terms as $term) {
            // Check if the slug contains the slug part or if the slug part contains the term slug
            if (strpos($term->slug, $slug_part) !== false || strpos($slug_part, $term->slug) !== false) {
                return $term;
            }
        }

        return null;
    }

    /**
     * Create or update taxonomy
     */
    private function simco_create_or_update_taxonomy($url, $scraped_data, $taxonomy, $enable_slug_matching = false, $url_slug_part = 'last')
    {
        // Check if scraped_data is valid
        if (empty($scraped_data)) {
            throw new Exception('Error: (simco_create_or_update_taxonomy) No scraped_data found.');
        }

        // Extract data from scraped_data
        $title = !empty($scraped_data['title']) ? $scraped_data['title'] : '';
        $content = !empty($scraped_data['content']) ? $scraped_data['content'] : '';
        $image = !empty($scraped_data['image']) ? $scraped_data['image'] : '';
        
        $term_id = null;

        // Handle slug matching if enabled
        if ($enable_slug_matching) {
            $extracted_slug = $this->simco_extract_url_slug_part($url, $url_slug_part);
            
            if (!empty($extracted_slug)) {
                $existing_term = $this->simco_find_taxonomy_by_slug($taxonomy, $extracted_slug);
                if ($existing_term) {
                    $term_id = $existing_term->term_id;
                }
            }
        }

        // Create or update term
        if ($term_id) {
            // Update existing term
            $result = wp_update_term($term_id, $taxonomy, [
                'name' => $title,
                'description' => wp_strip_all_tags($content)
            ]);
        } else {
            // Create new term
            $slug = $enable_slug_matching ? $this->simco_extract_url_slug_part($url, $url_slug_part) : '';
            $term_args = [
                'name' => $title,
                'description' => wp_strip_all_tags($content)
            ];
            
            if (!empty($slug)) {
                $term_args['slug'] = sanitize_title($slug);
            }

            $result = wp_insert_term($title, $taxonomy, $term_args);

            if (!is_wp_error($result)) {
                $term_id = $result['term_id'];
            }
        }

        // Handle image as term meta if term was created/updated successfully
        if ($term_id && !empty($image)) {
            // Upload the image first
            $image_id = $this->simco_upload_image($image, null, 'Taxonomy Image', 'Taxonomy Image', 'taxonomy_image_');
            
            if (!empty($image_id)) {
                // Save image as term meta (many themes and plugins look for 'thumbnail_id')
                update_term_meta($term_id, 'thumbnail_id', $image_id);
                // Also save with a more generic meta key
                update_term_meta($term_id, 'simco_image_id', $image_id);
            }
        }

        // Check if the term was created/updated successfully
        if (is_wp_error($result)) {
            throw new Exception('Error: (simco_create_or_update_taxonomy) ' . $result->get_error_message());
        }

        return $term_id;
    }

    /**
     * Flatten array data
     */
    private function simco_flatten_array($array, $prefix = '')
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (is_object($value)) {
                $value = (array)$value;
            }

            if (is_array($value)) {
                if (preg_match("/^[0-9]+$/", (string)$key)) {
                    foreach ($value as $key_arr => $value_arr) {
                        // Trim '_' on the end
                        $prefix = rtrim($prefix, '_');

                        if (!isset($result[$prefix])) {
                            $result[$prefix] = array();
                        }
                        $result[$prefix][$key][$key_arr] = $value_arr;
                    }
                    $result = array_merge($result, $this->simco_flatten_array($value, $prefix . $key . '_'));
                } else {
                    $result = array_merge($result, $this->simco_flatten_array($value, $prefix . $key . '_'));
                }
            } else {
                if (preg_match("/^[0-9]+$/", (string)$key)) {
                    // Trim '_' on the end
                    $prefix = rtrim($prefix, '_');

                    if (!isset($result[$prefix])) {
                        $result[$prefix] = $value;
                    } else {
                        $result[$prefix] .= ', ' . $value;
                    }
                } else {
                    if (!isset($result[$prefix . $key])) {
                        $result[$prefix . $key] = $value;
                    } else {
                        $result[$prefix . $key] .= ', ' . $value;
                    }
                }
            }
        }

        return $result;
    }
}
