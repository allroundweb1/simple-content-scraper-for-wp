<?php
class Simple_Content_Scraper_i18n
{
    public function load_plugin_textdomain()
    {
        // Load the plugin text domain for translation
        load_plugin_textdomain('simple-content-scraper', false, dirname(dirname(plugin_basename(__FILE__))) . '/languages/');
    }
}
