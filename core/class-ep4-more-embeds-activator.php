<?php
/**
 * Fired during plugin activation
 *
 * @link       https://ep4.com
 * @since      1.0.0
 *
 * @package    EP4_More_Embeds
 * @subpackage EP4_More_Embeds/core
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 * For compatibility purposes, namespace aren't used so this file can be parsed on PHP 5.2.
 *
 * @since      1.0.0
 * @package    EP4_More_Embeds
 * @subpackage EP4_More_Embeds/core
 * @author     Dave Lavoie <dave.lavoie@ep4.com>
 */
class EP4_More_Embeds_Activator {
	/**
	 * The default settings of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $default_settings    The default settings of the plugin.
	 */
	public static $default_settings = array(
		'ep4-more-embeds-providers'	=> array(
						'bandcamp'              => 'on',
						'box'                   => 'on',
						'twitch'                => 'on',
						'vevo'                  => 'on',
		),
		'ep4-more-embeds-box'       => array(
						'width'                 => '550',
						'height'                => '400',
						'view'                  => 'list',
						'sort'                  => 'date',
						'direction'             => 'asc',
						'theme'                 => 'blue',
						'show_parent_path'      => 'on',
						'show_item_feed_action' => 'on',
						'view_file_only'        => false,
		),
		'ep4-more-embeds-bandcamp'  => array(
						'layout'                => 'standard',
						'artwork'               => 'big',
						'tracklist'             => false,
						'bgcol'                 => '#ffffff',
						'linkcol'               => '#0687F5',
						'width'                 => '350',
						'height'                => '470',
		),
	);

	/**
	 * Run on activation hook.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		self::register_default_settings();
	}

	/**
	 * Initialize the default settings in the database.
	 *
	 * @since    1.0.0
	 */
	public static function register_default_settings() {
		foreach ( self::$default_settings as $id => $settings ) {
			add_option( $id, $settings );
		}
	}

	/**
	 * Check if one of the settings is already inserted in the database.
	 *
	 * Check if any settings are missing from the database. If that's the case, it'll
	 * register missing options.
	 *
	 * @return   bool true if previously activated, false otherwise.
	 * @since    1.0.0
	 */
	public static function is_correctly_installed() {
		foreach ( self::$default_settings as $id => $settings ) {
			if ( false === get_option( $id, false ) ) {
				return false;
			}
		}

		return true;
	}

}
