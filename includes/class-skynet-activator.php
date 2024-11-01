<?php

/**
 * Fired during plugin activation
 *
 * @link       www.skynet.com.my
 * @since      1.0.6
 *
 * @package    Skynet
 * @subpackage Skynet/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.6
 * @package    Skynet
 * @subpackage Skynet/includes
 * @author     skynetmalaysia <malaysiaskynet@gmail.com>
 */
class Skynet_Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.6
	 */
	public static function activate()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . "wc_skynet_printed_sticker";
		$my_products_db_version = '1.0.6';
		$charset_collate = $wpdb->get_charset_collate();

		if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {

			$sql = "CREATE TABLE $table_name (
            ID mediumint(9) NOT NULL AUTO_INCREMENT,
            `order_id` bigint(9) NOT NULL,
            `awbnumber` varchar(20) NOT NULL,
            `created_on` DATETIME,
            PRIMARY KEY  (ID)
    ) $charset_collate;";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
			add_option('wc_skynet_db_version', $my_products_db_version);
		}
	}
}
