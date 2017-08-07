<?php
/**
 * Validate if the plugin is compatible with the current environment
 *
 * For obvious reasons, we must refrain from using most recent PHP features in this file such
 * as namespaces and closures, or else the compatibility checker will fail on older environments.
 *
 * @link       https://ep4.com
 * @since      1.0.0
 *
 * @package    EP4_More_Embeds
 * @subpackage EP4_More_Embeds/core
 */

/**
 * Validate if the plugin is compatible with the current environment
 *
 * @since      1.0.0
 * @package    EP4_More_Embeds
 * @subpackage EP4_More_Embeds/core
 * @author     Dave Lavoie <dave.lavoie@ep4.com>
 */
class EP4_More_Embeds_Compatibility_Checker {
	/**
	 * The minimum WordPress Version compatible with this plugin
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $required_wordpress_version	The minimum WordPress Version compatible with this plugin.
	 */
	public $required_wordpress_version = '';

	/**
	 * The minimum PHP Version compatible with this plugin
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $required_php_version	    The minimum PHP Version compatible with this plugin.
	 */
	public $required_php_version = '';

	/**
	 * Other plugins on which this plugin could rely on, if any.
	 *
	 * If this plugin is extending another plugin, or needs another plugin to run, the array should be
	 * populated with the needed plugin slugs.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      array    $required_plugins             Other plugins on which this plugin could rely on, if any.
	 */
	public $required_plugins = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 * @access	public
	 *
	 * @param file $file Plugin file.
	 */
	public function __construct( $file = null ) {
	    $plugin_data = $this->get_plugin_data( $file );

	    if ( empty( $this->required_wordpress_version ) ) {
	        $this->required_wordpress_version = $plugin_data['RequiresAtLeast'];
	    }

	    if ( empty( $this->required_php_version ) ) {
	        $this->required_php_version = $plugin_data['PHP'];
	    }
	}

	/**
	 * Parses the file to retrieve plugin's metadata.
	 *
	 * This function is a modified version of WordPress get_plugin_data() function.
	 *
	 * The metadata of the plugin's data searches for the following in the plugin's
	 * header. All plugin data must be on its own line. For plugin description, it
	 * must not have any newlines or only parts of the description will be displayed
	 * and the same goes for the plugin data. The below is formatted for printing.
	 *
	 *     Plugin Name: Name of Plugin
	 *     Plugin URI: Link to plugin information
	 *     Description: Plugin Description
	 *     Author: Plugin author's name
	 *     Author URI: Link to the author's web site
	 *     Version: Must be set in the plugin for WordPress 2.3+
	 *     Text Domain: Optional. Unique identifier, should be same as the one used in
	 *              load_plugin_textdomain()
	 *     Domain Path: Optional. Only useful if the translations are located in a
	 *              folder above the plugin's base path. For example, if .mo files are
	 *              located in the locale folder then Domain Path will be "/locale/" and
	 *              must have the first slash. Defaults to the base folder the plugin is
	 *              located in.
	 *     Network: Optional. Specify "Network: true" to require that a plugin is activated
	 *              across all sites in an installation. This will prevent a plugin from being
	 *              activated on a single site when Multisite is enabled.
	 *     Requires WordPress: Minimum WordPress version required for running the plugin.
	 *     Compatible Up To: Optional. Most Recent WordPress version tested for compatibility.
	 *     PHP: Minimum PHP Version needed to run the plugin.
	 *     MySQL: Minimum MySQL version needed to run the plugin.
	 *
	 * @since 1.0.0
	 * @access public
	 * @see get_plugin_data()
	 *
	 * @param string $plugin_file Optional. Path to the plugin file. If not set, it'll try to get the plugin file path on its own.
	 * @return array {
	 *     Plugin data. Values will be empty if not supplied by the plugin.
	 *
	 *     @type string $Name               Name of the plugin. Should be unique.
	 *     @type string $Title              Title of the plugin and link to the plugin's site (if set).
	 *     @type string $Description        Plugin description.
	 *     @type string $Author             Author's name.
	 *     @type string $AuthorURI          Author's website address (if set).
	 *     @type string $Version            Plugin version.
	 *     @type string $TextDomain         Plugin textdomain.
	 *     @type string $DomainPath         Plugins relative directory path to .mo files.
	 *     @type bool   $Network            Whether the plugin can only be activated network-wide.
	 *     @type string $RequiresAtLeast   Minimum compatible version of WordPress.
	 *     @type string $TestedUpTo   Maximum compatible version of WordPress.
	 *     @type string $PHP                Minimum compatible version of PHP.
	 *     @type string $MySQL              Minimum compatible version of MySQL.
	 * }
	 */
	private function get_plugin_data( $plugin_file = null ) {
		$plugin_file = isset( $plugin_file ) && is_file( $plugin_file ) ? $plugin_file : $this->get_plugin_main_file();
		$default_headers = array(
			'Name'              => 'Plugin Name',
			'PluginURI'         => 'Plugin URI',
			'Version'           => 'Version',
			'Description'       => 'Description',
			'Author'            => 'Author',
			'AuthorURI'         => 'Author URI',
			'TextDomain'        => 'Text Domain',
			'DomainPath'        => 'Domain Path',
			'Network'           => 'Network',
			'RequiresAtLeast'   => 'Requires at least', // Min WP version.
			'TestedUpTo'        => 'Tested up to', // Max WP version.
			'PHP'               => 'PHP',
			'MySQL'             => 'MySQL',
		);

		$plugin_data = get_file_data( $plugin_file, $default_headers, 'plugin' );

		$plugin_data['Network']    = ( 'true' === strtolower( $plugin_data['Network'] ) );
		$plugin_data['Title']      = $plugin_data['Name'];
		$plugin_data['AuthorName'] = $plugin_data['Author'];

		return $plugin_data;
	}

	/**
	 * Get current plugin directory.
	 *
	 * @since   1.0.0
	 * @access	public
	 *
	 * @param bool $fullpath Include plugin fullpath.
	 * @return string Plugin directory.
	 */
	private function get_plugin_directory( $fullpath = true ) {
		$plugin_dir = '/' . strtok( plugin_basename( ( __FILE__ ) ), '/' );

		if ( $fullpath ) {
			return WP_PLUGIN_DIR . $plugin_dir;
		}

		return $plugin_dir;
	}

	/**
	 * Get a list of all files of the plugin based on plugin root.
	 *
	 * @since   1.0.0
	 * @access	public
	 *
	 * @param string $plugin_root Plugin Root directory.
	 * return array List of plugin file paths.
	 */
	private function get_plugin_files( $plugin_root = null ) {
		if ( ! $plugin_root ) {
			$plugin_root = $this->get_plugin_directory();
		}

		$plugin_dir = @ opendir( $plugin_root );

		$plugin_files = array();
		if ( $plugin_dir ) {
			while ( ( $file = readdir( $plugin_dir ) ) !== false ) {
				if ( substr( $file, 0, 1 ) === '.' ) {
					continue;
				}
				if ( is_dir( $plugin_root . '/' . $file ) ) {
					$plugins_subdir = @ opendir( $plugin_root . '/' . $file );
					if ( $plugins_subdir ) {
						while ( ($subfile = readdir( $plugins_subdir ) ) !== false ) {
							if ( substr( $subfile, 0, 1 ) === '.' ) {
								continue;
							}

							if ( substr( $subfile, -4 ) === '.php' ) {
								$plugin_files[] = "$file/$subfile";
							}
						}
						closedir( $plugins_subdir );
					}
				} else {
					if ( substr( $file, -4 ) === '.php' ) {
						$plugin_files[] = $file;
					}
				}
			}
			closedir( $plugin_dir );
		}

		if ( empty( $plugin_files ) ) {
			return false;
		}

		return $plugin_files;
	}

	/**
	 * Get plugin main file.
	 *
	 * @since   1.0.0
	 * @access	public
	 *
	 * @param string $plugin_root Plugin Root directory.
	 * return string Plugin file path.
	 */
	private function get_plugin_main_file( $plugin_root = null ) {
		if ( ! $plugin_root ) {
			$plugin_root = $this->get_plugin_directory();
		}

		$plugin_files = $this->get_plugin_files( $plugin_root );
		$default_headers = array(
			'Name'              => 'Plugin Name',
			'RequiresAtLeast'  => 'Requires at least',
			'TestedUpTo'  => 'Tested up to',
			'PHP'               => 'PHP',
			'MySQL'             => 'MySQL',
		);

		$main_plugin_file = null;

		foreach ( $plugin_files as $plugin_file ) {
			if ( ! is_readable( "$plugin_root/$plugin_file" ) ) {
				continue; // Not readable. Go to next file.
			}

			$plugin_data = get_file_data( "$plugin_root/$plugin_file", $default_headers );

			if ( empty( $plugin_data['Name'] ) || 'Name of Plugin' === $plugin_data['Name'] ) {
				continue; // No plugin data, so not main plugin file. Go to next file.
			}

			$main_plugin_file = "$plugin_root/$plugin_file"; // Plugin metadata was found, let's save the file name which includes that data.

			if ( ! empty( $plugin_data['RequiresAtLeast'] ) || ! empty( $plugin_data['PHP'] ) || ! empty( $plugin_data['MySQL'] ) ) {
				break; // If the file contains data related to plugin compatibility, just stop there, that's exactly the file we need.
			}
		}

		return $main_plugin_file;
	}


	/**
	 * Get minimum compatible WordPress version.
	 *
	 * @since   1.0.0
	 * @access	public
	 *
	 * return string Minimum compatible WordPress Version.
	 */
	public function get_minimum_wp_version() {
		// If minimum WP version isn't given, let'set the requirement to 0.
		if ( empty( $this->required_wordpress_version ) ) {
			return 0;
		}

		return $this->required_wordpress_version;
	}

	/**
	 * Get minimum compatible PHP version.
	 *
	 * @since   1.0.0
	 * @access	public
	 *
	 * return string Minimum compatible PHP Version.
	 */
	public function get_required_php_version() {
		// If minimum PHP version isn't given, let's set the requirement to default WP requirements..
		if ( empty( $this->required_php_version ) ) {
			return $GLOBALS['required_php_version'];
		}

		return $this->required_php_version;
	}

	/**
	 * Get required plugins.
	 *
	 * @since   1.0.0
	 * @access	public
	 *
	 * return array List of required plugins.
	 */
	public function get_required_plugins() {
		if ( empty( $this->required_plugins ) ) {
			return false;
		}

		return $this->required_plugins;
	}

	/**
	 * Is the plugin compatible with the current WP version?.
	 *
	 * @since   1.0.0
	 * @access	public
	 *
	 * return array List of required plugins.
	 */
	public function is_wp_compatible() {
		if ( version_compare( $GLOBALS['wp_version'], $this->get_minimum_wp_version(), '<' ) ) {
			return false;
		}

	    return true;
	}

	/**
	 * Is the plugin compatible with the current PHP version?.
	 *
	 * @since   1.0.0
	 * @access	public
	 *
	 * return array List of required plugins.
	 */
	public function is_php_compatible() {
		if ( version_compare( phpversion(), $this->get_required_php_version(), '<' ) ) {
			return false;
		}

	    return true;
	}

	/**
	 * Are the required dependent plugins are installed and active?
	 *
	 * @since   1.0.0
	 * @access	public
	 *
	 * return bool true if required plugins are installed and activated, false otherwise.
	 */
	public function has_required_plugins() {
		if ( $this->get_required_plugins() ) {
			// Check if plugins are active here.
			return true;
		}

	    return true;
	}

	/**
	 * Preliminary Compatibility Check before anything else
	 *
	 * This is a preliminary check only for front-end users. For some reasons, even if the plugin was compatible when firstly activated,
	 * it could be suddenly incompatible if the website was moved to another server, if the server was updated, if WordPress was updated
	 * or if the plugin itself has been automatically updated. We don't want the website to break for normal visitors, so that preliminary
	 * check will prevent the plugin from loading if it finds out that the PHP version or WordPress version aren't compatible with the plugin
	 * requirements.
	 *
	 * @param file $plugin_file Plugin file.
	 */
	public static function is_pre_compatible( $plugin_file = null ) {
		// No preliminary check needed if wp-admin.
		if ( ! is_admin() ) {
			$pre_compatibilizer = new self( $plugin_file );

			// Check all compatibility requirements are met.
			if ( ! $pre_compatibilizer->is_wp_compatible() ) {
				return false;
			}

			if ( ! $pre_compatibilizer->is_php_compatible() ) {
				return false;
			}
		}

		return true;
	}
}
