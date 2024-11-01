<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       www.skynet.com.my
 * @since      1.0.6
 *
 * @package    Skynet
 * @subpackage Skynet/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.6
 * @package    Skynet
 * @subpackage Skynet/includes
 * @author     skynetmalaysia <malaysiaskynet@gmail.com>
 */
class Skynet_i18n
{


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.6
	 */
	public function load_plugin_textdomain()
	{

		load_plugin_textdomain(
			'skynet',
			false,
			dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
		);
	}
}
