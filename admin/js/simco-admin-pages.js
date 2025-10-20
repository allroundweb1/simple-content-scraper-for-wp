(function ($) {
    'use strict';
    $(document).ready(function () {
        // Handle import type change to show/hide relevant sections
        $('#simco_import_type').on('change', function() {
            var importType = $(this).val();
            
            if (importType === 'taxonomy') {
                $('#simco_post_type_section').hide();
                $('#simco_taxonomy_section').show();
                $('#simco_slug_matching_section').show();
            } else {
                $('#simco_post_type_section').show();
                $('#simco_taxonomy_section').hide();
                $('#simco_slug_matching_section').hide();
            }
        });

        // Handle slug matching checkbox to show/hide URL slug part selection
        $('#simco_enable_slug_matching').on('change', function() {
            if ($(this).is(':checked')) {
                $('#simco_url_slug_part_section').show();
            } else {
                $('#simco_url_slug_part_section').hide();
            }
        });

        // When the button #simco-start-scraper gets clicked, do an ajax call to scrape all URLs
        $('#simco-start-scraper').on('click', function (e) {
            e.preventDefault();

            // Get all field values
            var urls = $('#simco_urls').val();
            var title_element_id = $('#simco_title_id').val();
            var content_element_id = $('#simco_content_id').val();
            var image_element_id = $('#simco_image_id').val();
            var date_element_id = $('#simco_date_id').val();
            var category_element_id = $('#simco_category_id').val();
            var category_seperator = $('#simco_category_separator').val();
            
            // Import type and related fields
            var import_type = $('#simco_import_type').val();
            var post_type = $('#simco_post_type').val();
            var taxonomy = $('#simco_taxonomy').val();
            var enable_slug_matching = $('#simco_enable_slug_matching').is(':checked') ? 1 : 0;
            var url_slug_part = $('#simco_url_slug_part').val();

            // Check if the urls are not empty
            if (urls === '') {
                // Hide success alert
                $('#successAlert').hide();
                // Show the error alert
                $('#errorAlert').show();
                // Set the message
                $('#alertErrorMessage').text('Er zijn geen URLs ingevuld');
                return;
            }

            // Validate taxonomy selection if import type is taxonomy
            if (import_type === 'taxonomy' && !taxonomy) {
                $('#successAlert').hide();
                $('#errorAlert').show();
                $('#alertErrorMessage').text('Selecteer een taxonomie voor taxonomie import');
                return;
            }

            // Check if the urls are on multiple lines and split them with a | separator
            if (urls.includes('\n')) {
                urls = urls.split('\n').join('|');
            }

            // Do ajax request to import all objects
            $.ajax({
                url: simple_content_scraper.ajax_url,
                type: 'POST',
                data: {
                    action: 'simco_process_urls',
                    nonce: simple_content_scraper.ajax_settings_nonce,
                    urls: urls,
                    title_element_id: title_element_id,
                    content_element_id: content_element_id,
                    image_element_id: image_element_id,
                    date_element_id: date_element_id,
                    category_element_id: category_element_id,
                    category_seperator: category_seperator,
                    import_type: import_type,
                    post_type: post_type,
                    taxonomy: taxonomy,
                    enable_slug_matching: enable_slug_matching,
                    url_slug_part: url_slug_part
                },
                success: function (response) {
                    // First check if it's no WP JSON error
                    if (!response.success) {
                        // Hide success alert
                        $('#successAlert').hide();
                        // Show the error alert
                        $('#errorAlert').show();
                        // Set the message
                        $('#alertErrorMessage').text('Er is een fout opgetreden: ' + response.data);
                        return;
                    } else {
                        // Hide error alert
                        $('#errorAlert').hide();
                        // Show the alert
                        $('#successAlert').show();
                        // Set the message
                        $('#alertSuccessMessage').text(response.data);
                    }

                },
                error: function (error) {
                    // Hide success alert
                    $('#successAlert').hide();
                    // Show the error alert
                    $('#errorAlert').show();
                    // Set the message
                    $('#alertErrorMessage').text('Er is een fout opgetreden: ' + error.responseText);
                }
            });
        });

        // If .aw-alert-close is clicked, find the first parent called .uk-alert and hide it
        UIkit.util.on('.aw-alert-close', 'click', function (e) {
            e.preventDefault();
            e.target.blur();
            $(e.target).closest('.uk-alert').hide();
        });
    });
})(jQuery);
