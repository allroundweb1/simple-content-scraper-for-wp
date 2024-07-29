(function ($) {
    'use strict';
    $(document).ready(function () {
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
            var post_type = $('#simco_post_type').val();

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
                    post_type: post_type
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
