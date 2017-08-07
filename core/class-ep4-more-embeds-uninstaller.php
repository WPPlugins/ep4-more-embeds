<?php
/**
 * Fired during plugin uninstallation.
 *
 * @link       https://ep4.com
 * @since      1.0.0
 *
 * @package    EP4_More_Embeds
 * @subpackage EP4_More_Embeds/core
 */

/**
 * Fired during plugin uninstallation.
 *
 * This class defines all code necessary to run during the plugin's uninstallation.
 *
 * @since      1.0.0
 * @package    EP4_More_Embeds
 * @subpackage EP4_More_Embeds/core
 * @author     Dave Lavoie <dave.lavoie@ep4.com>
 */
class EP4_More_Embeds_Uninstaller {
	/**
	 * The registered options of the plugin in the database.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $options    The options of the plugin.
	 */
	public static $options = array(
		'ep4-more-embeds-providers',
		'ep4-more-embeds-box',
		'ep4-more-embeds-bandcamp',
	);

	/**
	 * Run on uninstall hook.
	 *
	 * @since    1.0.0
	 */
	public static function uninstall() {
		self::delete_options();
	}

	/**
	 * Clear oembed cache when deactivating the plugin.
	 *
	 * @since    1.0.0
	 */
	public static function delete_options() {
		foreach ( self::$options as $option_name ) {
			delete_option( $option_name );
		}
	}

}
