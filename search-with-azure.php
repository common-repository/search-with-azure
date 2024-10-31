<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://wpazuresearch.com
 * @package           Azure_Search
 *
 * @wordpress-plugin
 * Plugin Name:       Search with Azure
 * Plugin URI:        http://wpazuresearch.com
 * Description:       Use the power of the Microsoft Cloud to reduce load on your server and have a faster and more intelligent search.
 * Version:           1.1.1
 * Author:            Neil Boyd
 * Author URI:        http://wpazuresearch.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       search-with-azure
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// define a log function for debugging
if (!function_exists('_log')) {
    function _log($message)
    {
        if (WP_DEBUG === true) {
            if (is_array($message) || is_object($message)) {
                error_log(print_r($message, true));
            } else {
                error_log($message);
            }
        }
    }
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-azure-search-activator.php
 */
function activate_azure_search() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-azure-search-activator.php';
	Azure_Search_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-azure-search-deactivator.php
 */
function deactivate_azure_search() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-azure-search-deactivator.php';
	Azure_Search_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_azure_search' );
register_deactivation_hook( __FILE__, 'deactivate_azure_search' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-azure-search.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
function run_azure_search() {

	$plugin = new Azure_Search();
	$plugin->run();

}
run_azure_search();
