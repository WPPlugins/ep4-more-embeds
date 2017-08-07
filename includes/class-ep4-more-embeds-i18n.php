<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://ep4.com
 * @since      1.0.0
 *
 * @package    EP4_More_Embeds
 * @subpackage EP4_More_Embeds/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    EP4_More_Embeds
 * @subpackage EP4_More_Embeds/includes
 * @author     Dave Lavoie <dave.lavoie@ep4.com>
 */
class EP4_More_Embeds_I18n {
	/**
	 * The text domain of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $text_domain		The string used to uniquely identify the text domain of the plugin.
	 */
	protected $text_domain = '';

	/**
	 * The plugin directory where to find the language file
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $lang_dir    	The plugin directory where to find the language file
	 */
	protected $lang_dir = 'languages';


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 * @access	public
	 *
	 * @param   string $text_domain     The string used to uniquely identify the text domain of the plugin.
	 * @param   string $lang_directory  Optional. The languages directory. Default to 'languages'.
	 */
	public function __construct( $text_domain = '', $lang_directory = '' ) {
		// Inherited Properties.
		if ( ! empty( $text_domain ) ) {
			$this->text_domain = $text_domain;
		}

		if ( ! empty( $lang_directory ) ) {
			$this->lang_dir = $lang_directory;
		}

	}

	/**
	 * Return the text domain if class object is echoed
	 *
	 * @since   1.0.0
	 * @access	public
	 */
	public function __toString() {
		return $this->get_text_domain();
	}

	/**
	 * Get the custom text domain for this plugin.
	 *
	 * Get the custom text domain for this plugin. If no text domain exists, automatically
	 * generate one based on the plugin slug, since the text domain should match the plugin
	 * slug anyway. The plugin slug itself is based on the plugin main directory name.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function get_text_domain() {
		return ( ! empty( $this->text_domain ) ) ? $this->text_domain : $this->get_plugin_slug();
	}


	/**
	 * Get the plugin slug, which is actually the plugin main directory name.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function get_plugin_slug() {
		$current_file_path	= plugin_basename( ( __FILE__ ) ); // Fetch the current file path, starting from the plugin base directory. Ex: plugin-name/directory/subdir/file.php
		$plugin_slug		= strtok( $current_file_path, '/' ); // Only keep the plugin base directory. Ex: plugin-name.

		return $plugin_slug;
	}


	/**
	 * Get the directory where the languges files can be found
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function get_lang_dir() {
		return $this->lang_dir;
	}


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since   1.0.0
	 * @access	public
	 *
	 * @uses	load_plugin_textdomain( $domain, $abs_rel_path, $plugin_rel_path )	Load plugin language file
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			$this->get_text_domain(),
			false,
			$this->get_plugin_slug() . '/' . $this->get_lang_dir()
		);
	}


	/**
	 * Load custom localisation in WordPress Languages directory if any
	 *
	 * @since	1.0.0
	 * @access	public
	 *
	 * @uses	get_locale()						Get language
	 * @uses	load_textdomain( $domain, $mofile )	Load language file
	 */
	public function load_custom_localisation() {
		$locale = apply_filters( 'plugin_locale', get_locale(), $this->get_text_domain() );
		$custom_language_file_path = WP_LANG_DIR . '/plugins/' . $this->get_text_domain() . '-' . $locale . '.mo';

		load_textdomain( $this->get_text_domain(), $custom_language_file_path );

	}

}
