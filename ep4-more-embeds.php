<?php
/**
 * EP4 More Embeds
 *
 * Support for more embedded content on your website. Current supported embeds are Bandcamp, Box.com, VEVO & Twitch.
 *
 * @link                http://ep4.com
 * @since               1.0.0
 * @package             EP4_More_Embeds
 *
 * @wordpress-plugin
 * Plugin Name:         EP4 More Embeds
 * Plugin URI:          https://vizemedia.com/plugins/ep4-more-embeds
 * Description:         Support for more embedded content on your website. Current supported embeds are Bandcamp, Box.com, VEVO & Twitch.
 * Version:             1.0.0
 * Author:              Dave Lavoie, EP4
 * Author URI:          https://ep4.com
 * License:             GPL-3.0+
 * License URI:         https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:         ep4-more-embeds
 * Domain Path:         /languages
 *
 * @compatibility-checker
 * Requires at least:   4.7
 * Tested up to:        4.7.3
 * PHP:                 5.2
 * MySQL:               5.5
 *
 * This plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this plugin. If not, see https://www.gnu.org/licenses/gpl-3.0.html.
 */

// If this file is called directly, abort.
defined( 'ABSPATH' ) || die( 'Whaddya think ye doing?!' );

/**
 * Compatibility Check before anything else. Stop loading the plugin if it's not the admin and not compatible.
 *
 * This is a preliminary check only for front-end users. The plugin won't be deactivated
 * but it'll silently stop from being loaded. Since this is only a preliminary check, we may
 * need to do more checks later, such as on plugin activation.
 */
require_once plugin_dir_path( __FILE__ ) . 'core/class-ep4-more-embeds-compatibility-checker.php';
if ( ! EP4_More_Embeds_Compatibility_Checker::is_pre_compatible( __FILE__ ) ) { return; } // End if().

/**
 * The code that runs during plugin activation.
 * This action is documented in core/class-ep4-more-embeds-activator.php
 */
require_once plugin_dir_path( __FILE__ ) . 'core/class-ep4-more-embeds-activator.php';
register_activation_hook( __FILE__, array( 'EP4_More_Embeds_Activator', 'activate' ) );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in core/class-ep4-more-embeds-deactivator.php
 */
require_once plugin_dir_path( __FILE__ ) . 'core/class-ep4-more-embeds-deactivator.php';
register_deactivation_hook( __FILE__, array( 'EP4_More_Embeds_Deactivator', 'deactivate' ) );

/**
 * The code that runs during plugin uninstallation.
 * This action is documented in core/class-ep4-more-embeds-uninstaller.php
 */
require_once plugin_dir_path( __FILE__ ) . 'core/class-ep4-more-embeds-uninstaller.php';
register_uninstall_hook( __FILE__, array( 'EP4_More_Embeds_Uninstaller', 'uninstall' ) );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
require plugin_dir_path( __FILE__ ) . 'core/class-ep4-more-embeds.php';
call_user_func( array( 'EP4_More_Embeds', 'run' ) );
