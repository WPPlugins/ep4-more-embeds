<?php
/**
 * The file that defines the Core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://ep4.com
 * @since      1.0.0
 *
 * @package    EP4_More_Embeds
 * @subpackage EP4_More_Embeds/Core
 */

/**
 * The Core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    EP4_More_Embeds
 * @subpackage EP4_More_Embeds/Core
 * @author     Dave Lavoie <dave.lavoie@ep4.com>
 */
class EP4_More_Embeds {

	/**
	 * The unique identifier of this plugin. Also used as text domain for the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	public $plugin_name = 'ep4-more-embeds';

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	public $version = '1.0.0';

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Loader   $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The locale i18n object.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      I18n     $i18n      The locale i18n object.
	 */
	protected $i18n;

	/**
	 * Array of helper classes.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array   $helpers    Associative array of helper_id => helper_class_name/class object.
	 */
	protected $helpers = array(
		'og' => 'Open_Graph_Helper',
	);

	/**
	 * List of registered embeds.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $embeds    List of registered embeds.
	 */
	private $embeds;

	/**
	 * Should contain saved options pulled from the database.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array   $options    Array of options freshly pulled from the database.
	 */
	private $options;

	/**
	 * The admin class.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Object  $admin      Contains an instance of the admin class used to power admin.
	 */
	private $admin;

	/**
	 * Create the plugin instance and run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public static function run() {
		$plugin = new self();
		$plugin->loader->run();
	}

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		// Core Dependencies.
		$this->load_dependencies();
		$this->init_loader(); // Set $this->loader. Must be loaded first.
		$this->set_locale(); // Set $this->i18n. Disabled and replaced by translate.wordpress.org.

		// Load options.
		$this->load_options(); // Set $this->options.

		// Will launch public for both front-end and wp-admin.
		$this->register_embeds(); // Set $this->embeds.

		// Will launch admin only if in wp-admin.
		$this->launch_admin(); // Set $this-admin if is admin.

		// Plugin Hooks.
		$this->define_public_hooks();
		$this->define_admin_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ep4-more-embeds-loader.php';

		/**
		 * The class responsible for defining internationalization functionality of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ep4-more-embeds-i18n.php';

		/**
		 * The class responsible for managing plugin settings.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ep4-more-embeds-admin-manager.php';

		/**
		 * Utility classes.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-open-graph-helper.php';

		/**
		 * Classes for handling embeds.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ep4-embedder.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/embeds/class-box-embedder.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/embeds/class-twitch-embedder.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/embeds/class-vevo-embedder.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/embeds/class-bandcamp-embedder.php';
	}

	/**
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function init_loader() {
		$this->loader = new EP4_More_Embeds_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$text_domain = $this->plugin_name;
		$this->i18n = new EP4_More_Embeds_I18n( $text_domain );

		// first try to load from wp-content/languages/plugins/ directory.
		$this->loader->add_action( 'plugins_loaded', $this->i18n, 'load_custom_localisation', 11 );

		// then load the default file from plugin/languages/ directory as a fallback.
		$this->loader->add_action( 'plugins_loaded', $this->i18n, 'load_plugin_textdomain', 12 );
	}

	/**
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_options() {
		// Load status of providers (enabled = 'on', disabled = false).
		$this->options['providers'] = get_option( "{$this->plugin_name}-providers", array() );

		// Get options for box embed if enabled.
		$this->options['box']       = isset( $this->options['providers']['box'] ) && $this->options['providers']['box'] ?
										get_option( "{$this->plugin_name}-box", array() ) :
										array();

		// Get options for Bandcamp embed if enabled.
		$this->options['bandcamp']  = isset( $this->options['providers']['bandcamp'] ) && $this->options['providers']['bandcamp'] ?
										get_option( "{$this->plugin_name}-bandcamp", array() ) :
										array();
	}

	/**
	 * Load the classes responsible for generating the embeds.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function register_embeds() {
		$providers = array_filter( $this->options['providers'] ); // Only keep a list of enabled providers.

		/**
		 * Only define embed instance for enabled providers on the front-end, or for all providers in wp-admin.
		 */
		// Defining Bandcamp embed.
		if ( is_admin() || isset( $providers['bandcamp'] ) ) {
			$args = array(
				'options' => $this->options['bandcamp'], // We're injecting the options since customization is allowed for Bandcamp.
				'helper'  => $this->helper( 'og' ), // The Open Graph Helper is needed too.
			);
			$this->embeds['Bandcamp'] = new Bandcamp_Embedder( $args );
		}

		// Defining Box.com Embed.
		if ( is_admin() || isset( $providers['box'] ) ) {
			$args = array(
				'options' => $this->options['box'], // We're injecting the options since customization is allowed for Box.
			);
			$this->embeds['Box.com'] = new Box_Embedder( $args );
		}

		// Define Twitch Embed.
		$this->embeds['Twitch'] = is_admin() || isset( $providers['twitch'] ) ? new Twitch_Embedder() : false;

		// Define VEVO Embed.
		$this->embeds['VEVO']   = is_admin() || isset( $providers['vevo'] )   ? new Vevo_Embedder()   : false;

		// Register embed handlers for enabled providers.
		$embed_names = array_keys( array_filter( $this->embeds ) ); // Only keep a list of enabled providers.
		foreach ( $embed_names as $embed_name ) {
			if ( in_array( $this->embeds[ $embed_name ]->embed_id, array_keys( $providers ), true ) ) {
				// Register handler for every enabled provider.
				$this->loader->add_action( 'after_setup_theme', $this->embeds[ $embed_name ], 'register_handler' );

				// Make the embed responsive if the provider is 'oembed'.
				if ( 'oembed' === $this->embeds[ $embed_name ]->embed_type ) {
					$this->loader->add_action( 'oembed_dataparse', $this->embeds[ $embed_name ], 'make_responsive', 10, 2 );
				}
			}
		}
	}

	/**
	 * Launch the admin and register internal hooks and settings.
	 *
	 * Loading the admin only for wp-admin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function launch_admin() {
		if ( ! is_admin() ) {
			$this->admin = false; // Bails out if is not admin.
			return;
		}

		$this->admin = new EP4_More_Embeds_Admin_Manager();
		$this->loader->add_action( 'admin_init', $this->admin, 'register_settings' );
		$this->loader->add_action( 'admin_menu', $this->admin, 'add_menu_item' );
		$this->loader->add_filter( 'plugin_action_links', $this->admin, 'add_action_links', 10, 4 );
		$this->loader->add_action( 'admin_print_styles-settings_page_' . $this->plugin_name, $this->admin, 'load_settings_assets' );
	}

	/**
	 * Register external hooks related to the admin area functionality.
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$this->loader->add_action( '_admin_menu', $this, 'define_admin_settings', 5 );
		$this->loader->add_action( 'admin_print_styles-settings_page_' . $this->plugin_name, $this, 'enqueue_admin_assets' );
	}

	/**
	 * Register all of the hooks related to the front-end part of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		// Enqueue stylesheet.
		$this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_public_assets' );
	}

	/**
	 * Build settings fields.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function define_admin_settings() {
		$providers = $this->options['providers'];

		// Make sure the plugin has been activated correctly by checking if settings can be found in the database. If no settings were found, create them.
		if ( ! EP4_More_Embeds_Activator::is_correctly_installed() ) {
			EP4_More_Embeds_Activator::register_default_settings(); // Normally runs during the activation hook, but will also run if settings are nowhere to be found.
		}

		$this->admin->add_page( $this->plugin_name, __( 'Embeds', 'ep4-more-embeds' ),  plugin_dir_url( dirname( __FILE__ ) ), $this->plugin_name );
		$this->admin->add_tab( 'providers', __( 'Providers', 'ep4-more-embeds' ), __( 'You can activate or deactivate auto-embedding of specific providers here.', 'ep4-more-embeds' ) );

		foreach ( $this->embeds as $embed_name => $embed ) {
			if ( ! isset( $embed->embed_id ) ) {
				continue;
			}

			// Add a checkbox for activating every registered embed providers.
			// Translators: Activate auto-embed for {provider}. Where {provider} can be Facebook, etc.
			$this->admin->add_field( 'providers', $embed->embed_id, 'checkbox', $embed_name, sprintf( __( 'Activate auto-embed for %1$s.', 'ep4-more-embeds' ), $embed_name ), 'on' );

			// Display the embed provider tab if activated and has settings.
			if ( ( empty( $providers ) && ! empty( $embed->settings ) ) || // Basically happens if the plugin was wrongly activated and the providers settings weren't initialized in the database.
				 ( ! empty( $providers ) && array_key_exists( $embed->embed_id, $providers ) && 'on' === $providers[ $embed->embed_id ] && ! empty( $embed->settings ) )
			) {
				$provider_advanced_settings_link = ' <a href="options-general.php?page=' . $this->plugin_name . '&tab=' . $embed->embed_id . '">' . __( 'Edit advanced settings', 'ep4-more-embeds' ) . ' »</a>';
				$this->admin->edit_field( 'providers', $embed->embed_id, 'description', $this->admin->get_field( 'providers', $embed->embed_id, 'description' ) . $provider_advanced_settings_link );

				// Translators: Advanced Settings for {provider} Embed. Where {provider} can be Facebook, etc.
				$this->admin->add_tab( $embed->embed_id, $embed_name, sprintf( __( 'Advanced Settings for %1$s Embed.',  'ep4-more-embeds' ), $embed_name ) );
				$this->admin->add_fields( $embed->embed_id, $embed->settings );
			}
		}
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function enqueue_admin_assets() {
		wp_enqueue_style( "{$this->plugin_name}-admin", plugin_dir_url( dirname( __FILE__ ) ) . 'css/more-embeds-admin.css', array(), $this->version, 'all' );
		wp_enqueue_script( "{$this->plugin_name}-admin", plugin_dir_url( dirname( __FILE__ ) ) . 'js/more-embeds-admin.js', array(), $this->version, 'all' );
	}

	/**
	 * Enqueue public assets.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function enqueue_public_assets() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( dirname( __FILE__ ) ) . 'css/more-embeds.css', array(), $this->version, 'all' );
	}

	/**
	 * Instantiate and return an helper object, or simply return the helper object if it already exists.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param  string $helper_id Helper id.
	 * @return object Helper.
	 */
	private function helper( $helper_id ) {
		if ( isset( $this->helpers[ $helper_id ] ) && is_object( $this->helpers[ $helper_id ] ) ) {
			return $this->helpers[ $helper_id ];
		} elseif ( isset( $this->helpers[ $helper_id ] ) && is_string( $this->helpers[ $helper_id ] ) ) {
			$this->helpers[ $helper_id ] = new $this->helpers[ $helper_id ]; // Transform the string to an object.
			return $this->helpers[ $helper_id ];
		}

		return false;
	}

}
