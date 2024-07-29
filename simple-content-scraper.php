<?php

/**
 * Plugin Name:         Simple Content Scraper
 * Plugin URI:          https://rickvanoirschot.nl
 * Description:         Simple Content Scraper created by Rick.
 * Version:             1.0.0
 * Requires at least:   6.0.0
 * Tested up to:        6.2.2
 * Requires PHP:        8.0.0
 * Author:              Rick
 * Author URI:          https://rickvanoirschot.nl
 * License:             GPL-2.0+
 * Text Domain:         simple-content-scraper
 * Domain Path:         /languages
 */

/**
 * If this file is being executed directly, terminate the program.
 */
if (!defined('WPINC')) {
    die();
}

/**
 * Current plugin version
 */
define('SIMCO_VERSION', '1.0.0');

/**
 * Plugin root file
 */
define('SIMCO_PLUGIN_FILE', __FILE__);

/**
 * Plugin base
 */
define('SIMCO_PLUGIN_BASE', plugin_basename(SIMCO_PLUGIN_FILE));

/**
 * Plugin Folder Path
 */
define('SIMCO_PLUGIN_DIR', plugin_dir_path(SIMCO_PLUGIN_FILE));

/**
 * Plugin Folder URL
 */
define('SIMCO_PLUGIN_URL', plugin_dir_url(SIMCO_PLUGIN_FILE));

/**
 * Code for activation
 */
function activate_simple_content_scraper()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-simple-content-scraper-activator.php';
    Simple_Content_Scraper_Activator::activate();
}

/**
 * Code for deactivation
 */
function deactivate_simple_content_scraper()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-simple-content-scraper-deactivator.php';
    Simple_Content_Scraper_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_simple_content_scraper');
register_deactivation_hook(__FILE__, 'deactivate_simple_content_scraper');

/**
 * Core file of the plugin
 */
require plugin_dir_path(__FILE__) . 'includes/class-simple-content-scraper.php';

/**
 * Execute plugin
 */
function start_simple_content_scraper()
{
    $plugin = new Simple_Content_Scraper();
    $plugin->run();
}
start_simple_content_scraper();
