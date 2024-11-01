<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              www.skynet.com.my
 * @since             1.0.6
 * @package           Skynet
 *
 * @wordpress-plugin
 * Plugin Name:       Skynet Malaysia
 * Plugin URI:        www.skynet.com.my
 * Description:       This is a plugin for Skynet integration with WooCommerce
 * Version:           1.0.6
 * Author:            skynetmalaysia
 * Author URI:        https://profiles.wordpress.org/skynetmalaysia/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       skynet
 * Domain Path:       /languages
 * 
 * WC requires at least: 3.0.0
 * WC tested up to: 4.8.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.6 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('SKYNET_VERSION', '1.0.6');
// define('SKYNET_API_URL1', 'https://api.skynet.com.my/api/');
// define('SKYNET_API_URL2', 'http://api.skynet.com.my/api/');
define('SKYNET_API_URL', 'https://internalapi.skynet.com.my/api/');
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-skynet-activator.php
 */
function activate_skynet()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-skynet-activator.php';
	Skynet_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-skynet-deactivator.php
 */
function deactivate_skynet()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-skynet-deactivator.php';
	Skynet_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_skynet');
register_deactivation_hook(__FILE__, 'deactivate_skynet');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-skynet.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.6
 */
function run_skynet()
{
	$plugin = new Skynet();
	$plugin->run();
}
run_skynet();
