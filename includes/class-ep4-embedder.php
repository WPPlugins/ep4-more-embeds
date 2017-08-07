<?php
/**
 * Core Embedder API Class
 *
 * @link       https://ep4.com
 * @since      1.0.0
 *
 * @package    EP4_More_Embeds
 * @subpackage EP4_More_Embeds/includes
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handle the embedding of custom providers
 *
 * @since      1.0.0
 * @package    EP4_More_Embeds
 * @subpackage EP4_More_Embeds/includes
 * @author     Dave Lavoie <dave.lavoie@ep4.com>
 */
class EP4_Embedder {

	/**
	 * Embed ID. Required.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string		$embed_id        The embed ID.
	 */
	public $embed_id;

	/**
	 * Embed type. Required.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string		$embed_type      The embed type.
	 */
	public $embed_type;

	/**
	 * RegEx pattern for this embed. Required.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      mixed		$pattern   Either a string of a single RegEx pattern, or an array of RegEx patterns.
	 *										If single pattern, all capturing group MUST be named with the same IDs to be
	 *										found in the endpoint, using ?P<key> at the beginning of each capture group.
	 */
	public $pattern;

	/**
	 * The endpoint URL for generating this embed. Must include {keys} of expected matches. Required.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      mixed		$endpoint  Either a string of a single endpoint URL, or an array of
	 *                                      endpoint URLs for oEmbed embed type. URLs can include {id}
	 *                                      of expected matches.
	 */
	public $endpoint;

	/**
	 * The settings for this embed. Optional.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      array      $settings		The settings for this embed.
	 */
	public $settings = array();

	/**
	 * Allow caching of the embed?
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @var      bool       $use_cache		Whether caching is enabled or not for this embed.
	 */
	public $use_cache = false;

	/**
	 * Allow Custom Shortcode. Optional.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      mixed      $shortcode		True if custom shortcode are allowed, false otherwise.
	 *										Instead of true, a string representing the custom shortcode ID can be used,
	 *										or else the $embed_id will be used.
	 */
	public $shortcode = false;

	/**
	 * A list of embedded items for the current page. Populated automatically.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      array      $embedded_items A list of embedded items for the current page
	 */
	protected $embedded_items = array();

	/**
	 * Should contain saved options pulled from the database, or else default options.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array      $options        Options.
	 */
	protected $options;

	/**
	 * Initialize the class, set its properties and enable custom feature based on properties.
	 *
	 * @since    1.0.0
	 * @param    array $args {
	 *                          An array of parameters for customizing the current class.
	 *                              @type array    $options Optional. Saved options from the database, if any.
	 *                              @type array    $helper  Optional. The Helper needed for generating the embed src, if needed.
	 *                 }
	 */
	public function __construct( $args = array() ) {
		// Extract supported args. Fallback to default.
		$options = isset( $args['options'] ) ? $args['options'] : array();
		$helper  = isset( $args['helper'] )  ? $args['helper'] : false;

		// Define Properties.
		$this->settings(); // Define $this->settings.
		$this->options = wp_parse_args( $options, $this->get_default_options() ); // Set $this->options.
		$this->helper = $helper; // Set $this->helper.

		// Enable custom [shortcode] if allowed for the current provider.
		if ( ! empty( $this->shortcode ) ) {
			$shortcode_id = is_string( $this->shortcode ) ? $this->shortcode : $this->embed_id;
			add_shortcode( $shortcode_id, array( $this, 'shortcode' ) );
		}
	}

	/**
	 * Public methods usually called from external hooks.
	 */

	/**
	 * Register New Handler for this Embed.
	 *
	 * If the embed type is 'oembed', it uses oembed_add_provider() method for calling WordPress wp_oembed_add_provider function.
	 * Else, it uses WordPress wp_embed_register_handler native method for registering the handler for this embed.
	 * Usually triggered by the parent plugin object with the after_setup_theme action hook.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @see     $this::oembed_add_provider() Used if embed type is "oembed".
	 * @see     $this::_generate_embed()     Generate Embedded Items for custom type of provider.
	 * @uses    wp_embed_register_handler()  Register a custom embed handler.
	 *
	 * @return  void
	 */
	public function register_handler() {
		if ( ! $this->embed_type || 'oembed' === $this->embed_type ) {
			$this->oembed_add_provider();
		} else {
			wp_embed_register_handler( $this->embed_id, $this->pattern, array( $this, '_generate_embed' ) );
		}
	}

	/**
	 * Filters the returned HTML to make the embed responsive.
	 *
	 * Automatically applied via the oembed_dataparse filter for embeds of type 'oembed'.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @param  string $return The returned oEmbed HTML.
	 * @param  mixed  $data   A data object result from an oEmbed provider, or a string containing the provider name.
	 */
	public function make_responsive( $return, $data = false ) {
		// Define $provider_name.
		if ( false === $data ) {
			$provider_name = $this->embed_id;
		} else {
			$provider_name = isset( $data->provider_name ) ? $data->provider_name : '';
		}

		// Make responsive.
		if ( strtolower( $provider_name ) === $this->embed_id ) {
			// Wrap the html inside a div container.
			$html  = '<div class="responsive-embed-container">';
			$html .= $return;
			$html .= '</div>';

			return $html;
		}

		return $return;
	}

	/**
	 * Public methods meant to be overriden by a child class.
	 */

	/**
	 * Method that can be overriden by child class for registering custom embed settings.
	 *
	 * @since   1.0.0
	 * @access  public
	 */
	public function settings() {
		$this->settings = array();
	}

	/**
	 * Allow the use of [bandcamp] shortcode.
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @param array  $attr Shortcode attributes.
	 * @param string $url  Content found between shortcode opening and closing tag. Expecting an URL here.
	 *
	 * @return string $html	The embedded item.
	 */
	public function shortcode( $attr, $url = '' ) {
		// Merge shortcode attributes with default options.
		$attr = shortcode_atts( $this->options, $attr );

		// This way we can easily know if the current embedded item was generated from a shortcode or a link.
		$attr['is_shortcode'] = 'true';

		// Generate the html by running the [embed] shortcode through the whole embed process.
		global $wp_embed;
		$html = $wp_embed->run_shortcode( "[embed {$attr}]{$url}[/embed]" );

		return $html; // Any changes to the current embedded item properties should be done overriding this function in a child class.
	}

	/**
	 * Method that can be overriden by child class for editing embedded items properties before generating the embed for the first time.
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @param  array $embedded_item	Properties of the current embedded item.
	 * @return array $embedded_item	The embedded item, altered or not.
	 */
	public function pre_embed( $embedded_item ) {
		return $embedded_item; // Any changes to the current embedded item properties should be done overriding this function in a child class.
	}

	/**
	 * Method that can be overriden by child class for customizing the view of embedded items.
	 *
	 * There are two ways to use and override this method in a child class. Either use it for generating the
	 * correct HTML for the embed, and returning it. In that case, Embedder::embed() parent method will never run.
	 * Or use the embed method for customizing an embedded item by manipulating the embed settings. In that case,
	 * nothing should be returned by the child::embed() method (or it should return false), and no HTML should be generated
	 * by it. The Embedder:embed() method will then be executed right after the child::embed() method in order to
	 * generate the HTML correctly.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @see     Box_Embedder::embed()   Example of using this method with no return value
	 *
	 * @param array  $matches	A list of regex matches for the URL.
	 * @param array  $attr  	Attributes for the embed (such as height and width).
	 * @param string $url   	The URL to be replaced with the embed.
	 * @param array  $rawattr	Raw attributes.
	 *
	 * @return string		    The HTML for an embedded item
	 */
	public function embed( $matches, $attr, $url, $rawattr ) {
		switch ( $this->embed_type ) {
			case 'iframe':
				$html = $this->generate_iframe();
				break;
			case 'javascript':
			case 'oembed':
			case 'default':
				$html = '';
				break;
		}

		return $html;
	}

	/**
	 * Private and protected methods NOT meant to be overriden by any child class
	 */

	/**
	 * Register embed of type "oembed".
	 *
	 * Use wp_oembed_add_provider() WordPress function for registering oEmbed providers.
	 * Can be overriden by the child class if needed.
	 *
	 * @since   1.0.0
	 * @access  private
	 * @see     $this::register_handler()                               Registers New Handler for this Embed.
	 * @uses    wp_oembed_add_provider( $format, $provider, $regex )    Registers an oEmbed provider with WordPress and adds it to the whitelist.
	 *
	 * @return  void
	 */
	private function oembed_add_provider() {
		if ( is_string( $this->pattern ) && is_string( $this->endpoint ) ) {                    // If pattern and endpoint are simple strings
			wp_oembed_add_provider( $this->pattern, $this->endpoint, true );                    // There's only one pattern and one endpoint which are obviously linked together
		} elseif ( is_string( $this->endpoint ) && is_int( key( $this->pattern ) ) ) {          // If endpoint is a string, but pattern is an array without key
			foreach ( $this->pattern as $pattern ) {                                            // There is one or many patterns
				wp_oembed_add_provider( $pattern, $this->endpoint, true );                      // And they are all using the same endpoint.
			}
		} else {                                                                                // Else, pattern and endpoint are both associative arrays linked by endpoint scheme type
			foreach ( $this->pattern as $pattern => $endpoint_scheme ) {                        // This is an associative array of "pattern => endpoint scheme type" and an associative
				wp_oembed_add_provider( $pattern, $this->endpoint[ $endpoint_scheme ], true );  // array of "endpoint scheme type => endpoint URL", converted to "pattern => endpoint URL".
			}
	    }
	}

	/**
	 * Generate Embedded Items for custom type of provider
	 *
	 * This method generates the embed for all types of provider but oEmbed.
	 * This method is not meant to be overriden by child classes! Use $this::embed() instead.
	 *
	 * @since   1.0.0
	 * @access  private         Not officially private since WP doesn't permit the use of private methods for callback,
	 *                          but should be considered as private from a developer point of view.
	 * @see     $this::embed()  Method that can be overriden by child classes for customizing embedded items.
	 *
	 * @param array  $matches   A list of regex matches for the URL.
	 * @param array  $attr      Attributes for the embed (such as height and width).
	 * @param string $url       The URL to be replaced with the embed.
	 * @param array  $rawattr   Raw attributes.
	 *
	 * @return string HTML embed for registered handler.
	 */
	function _generate_embed( $matches, $attr, $url, $rawattr ) {
		$id = $url; // The URL is the best unique identifier for this embedded item.

		$this->embedded_items[ $id ] = array(
										'matches'   => $matches,
										'attr'      => $attr,
										'url'       => $url,
										'rawattr'   => $rawattr,
									);

		// Let's get embed options, merge them with default $attr values.
		$this->embedded_items[ $id ]['options'] = wp_parse_args( $this->options, $this->embedded_items[ $id ]['attr'] );

		// Fetch cached embed.
		$cached_embed = $this->get_cached_embed( $this->embedded_items[ $id ] );

		if ( ! empty( $cached_embed ) ) {
			// Serve cached version.
			$this->embedded_items[ $id ]['html'] = $cached_embed;
		} else {
			// Allow any child class to edit properties of the current embedded item before generating the embed.
			$this->embedded_items[ $id ] = $this->pre_embed( $this->embedded_items[ $id ] );

			// If there's no cache, first let's resolve the endpoint based on matches.
			if ( ! isset( $this->embedded_items[ $id ]['src'] ) || ( isset( $this->embedded_items[ $id ]['src'] ) && ! is_string( $this->embedded_items[ $id ]['src'] ) ) ) {
				$this->embedded_items[ $id ]['src'] = $this->resolve_endpoint( $this->embedded_items[ $id ]['matches'] );
			}

			// Second, let's use the custom embed method. If it returns false or nothing, the embed will be generated below based on its type.
			$html = $this->embed( $this->embedded_items[ $id ]['matches'], $this->embedded_items[ $id ]['attr'], $this->embedded_items[ $id ]['url'], $this->embedded_items[ $id ]['rawattr'] );

			// If there's no html outputted in $html variable, also run the Embedder self embed method for generating the html.
			if ( ! is_string( $html ) ) {
				$html = self::embed( $this->embedded_items[ $id ]['matches'], $this->embedded_items[ $id ]['attr'], $this->embedded_items[ $id ]['url'], $this->embedded_items[ $id ]['rawattr'] );
			}

			$this->embedded_items[ $id ]['html'] = $html;

			// Update the cache.
			$this->update_cached_embed( $this->embedded_items[ $id ] );
		}

		/**
		 * 'embed_html' filter.
		 *
		 * Filters the HTML output for embedded items.
		 *
		 * @since 1.0.0
		 *
		 * @param string    $html   The HTML generated for this specific embedded item.
		 * @param array     $embedded_item {
		 *                          An array of parameters used to generate the HTML
		 *                              @type array     $matches    A list of regex matches for the URL
		 *                              @type array     $attr       Attributes for the embed (such as height and width)
		 *                              @type string    $url        The URL to be replaced with the embed
		 *                              @type array     $rawattr    Raw attributes
		 *                              @type string    $src        Resolved URL for the embed based on the endpoint and pattern matches.
		 *                              @type array     $options {
		 *                                                          An array of options for this specific embedded item
		 *                                                             @type string $height
		 *                                                             @type string $width
		 *                                                             @type mixed  $(...)     Any other options registered for this embed
		 *                                              }
		 *                              @type string    $html       The original HTML generated for this specific embedded item.
		 *                  }
		 * @param string    $id     The ID of the current embedded item.
		 */
		$html = apply_filters( 'embed_html', $this->embedded_items[ $id ]['html'], $this->embedded_items[ $id ], $id );

		/**
		 * Dynamic 'embed_html_{$embed_id}' filter
		 *
		 * Filters the HTML output for embedded items of a specific $embed_id.
		 *
		 * @since 1.0.0
		 * @see 'embed_html' Filter applied just before this one using the same parameters
		 *
		 * @param string    $html           The HTML generated for this specific embedded item.
		 * @param array     $embedded_item  An array of parameters used to generate the HTML.
		 * @param string    $id             The ID of the current embedded item.
		 */
		$html = apply_filters( "embed_html_{$this->embed_id}", $html, $this->embedded_items[ $id ], $id );

		return $html;
	}

	/**
	 * Helper for generating iframe embed
	 *
	 * This method generates the HTML for displaying an embed item within an iframe
	 *
	 * @since   1.0.0
	 * @access  protected
	 *
	 * @param string $url           Optional. The URL to use as src for the iframe. If false,
	 *                              the URL will be generated dynamically.
	 * @param array  $current_item  Optional. Array containing data for the current item.
	 *
	 * @return string HTML generated for displaying the iframe.
	 */
	protected function generate_iframe( $url = false, $current_item = array() ) {
		if ( empty( $current_item ) ) {
			$current_item = end( $this->embedded_items ); // At this point, the current item is forcefully the last item inserted.
		}

		if ( ! $url ) {
			$url = $current_item['url'];
		}

		// Define values of iframe attributes.
		$url = esc_url( $url );
		$src = esc_url( $current_item['src'] );
		$class = esc_attr( "embed-iframe embed-{$this->embed_id}" );
		$width = (int) ( isset( $current_item['options']['width'] ) ? $current_item['options']['width'] : $this->options['width'] );
		$height = (int) ( isset( $current_item['options']['height'] ) ? $current_item['options']['height'] : $this->options['height'] );
		$allowfullscreen = "allowfullscreen='true' webkitallowfullscreen='true' mozallowfullscreen='true' oallowfullscreen='true' msallowfullscreen='true'";

		// Generate the string for 'style' attribute based on $style.
		$style = '';
		if ( isset( $current_item['styles'] ) && is_array( $current_item['styles'] ) ) {
			foreach ( $current_item['styles'] as $css_property => $value ) {
				$style .= "{$css_property}:{$value};";
			}
			$style = esc_attr( $style );
		}

		// Generating iframe HTML markup.
		$iframe  = "<!-- Starting $this->embed_id iframe embed for $url -->";
		$iframe .= "<iframe src='$src' class='$class' width='$width' height='$height' data-url='$url' frameborder='0' style='$style' $allowfullscreen >";
		$iframe .= $this->get_error_message();
		$iframe .= '<span class="error-link"><a href="' . $url . '" target="_blank">' . __( 'Open link in a new tab' ) . '.</a></span>';
		$iframe .= '</iframe>';
		$iframe .= "<!-- Ending $this->embed_id iframe embed for $url -->";

		return $iframe;
	}

	/**
	 * Get error message to display if the embed couldn't be generated.
	 *
	 * @since   1.0.0
	 * @access  protected
	 *
	 * @return string HTML string containing the error message.
	 */
	protected function get_error_message() {
		$error_message = '<strong>' . esc_html__( 'HTML Embed' ) . ': ' . esc_html__( 'An unidentified error has occurred.' ) . '</strong>';
		$error_message .= '<span class="error-message">';
		switch ( $this->embed_type ) {
			case 'iframe':
				$error_message .= esc_html__( 'This feature requires inline frames. You have iframes disabled or your browser does not support them.' ); // Translated string from /wp-includes/script-loader.php.
				break;
			case 'oembed':
			case 'javascript':
				$error_message .= esc_html__( 'JavaScript must be enabled to use this feature.' );
				break;
			case 'default':
				$error_message .= esc_html__( 'This link is not live-previewable.' );
				break;
		}
		$error_message .= '</span>';

		return $error_message;
	}

	/**
	 * Helper for resolving the endpoint based on pattern matches
	 *
	 * This method replaces the {ids} found in the endpoint URL with the matches found based on the pattern.
	 *
	 * @since   1.0.0
	 * @access  protected
	 *
	 * @param array $pattern_matches Array of matches based on embed regex pattern.
	 *
	 * @return string Resolved URL for the embed based on the endpoint and pattern matches.
	 */
	protected function resolve_endpoint( $pattern_matches ) {
		if ( ! is_string( $this->endpoint ) ) {   // If for any reason, the endpoint isn't a string, we shouldn't be here at all.
			return $pattern_matches[0];           // In that case, let's return the full URL as a fallback.
		}

		$matches = array();
		foreach ( $pattern_matches as $match_id => $match_value ) {
			if ( is_string( $match_id ) ) {
				$matches[ "{{$match_id}}" ] = $match_value; // Create an associative array of type '{match_id}' => 'match_value'.
			}
		}

		// Replace {match_id} occurences with their corresponding match_value.
		$resolved_url = str_replace( array_keys( $matches ), array_values( $matches ), $this->endpoint );

		return $resolved_url;
	}

	/**
	 * Get a cached version of the embed, if cache is used and if it exists.
	 *
	 * We handle the cache more or less the same way WP does with oembed cache.
	 *
	 * @since   1.0.0
	 * @access  protected
	 *
	 * @param  array $embedded_item	Properties of the current embedded item.
	 *
	 * @return string|bool The cached embed string, or false if no cached embed could be retrieved.
	 */
	protected function get_cached_embed( $embedded_item ) {
		if ( ! $this->use_cache ) {
			return false;
		}

		$post = get_post();

		$post_id = ( ! empty( $post->ID ) ) ? $post->ID : null;

		if ( ! $post_id ) {
			return false; // Need to develop a fallback later.
		}

		// Check for a cached result (stored in the post meta).
		$key_suffix = md5( $embedded_item['url'] . wp_json_encode( $embedded_item['rawattr'] ) );
		$cachekey = "_embed_{$this->embed_id}_{$key_suffix}";
		$cachekey_time = "_embed_{$this->embed_id}_time_{$key_suffix}";

		/**
		 * Filters the embed TTL value (time to live).
		 *
		 * @since 1.0.0
		 *
		 * @param int    $time           Time to live (in seconds).
		 * @param string $embedded_item  Properties of the current embedded item.
		 * @param int    $post_id        Post ID.
		 */
		$ttl = apply_filters( 'cached_embed_ttl', WEEK_IN_SECONDS + ( MINUTE_IN_SECONDS * mt_rand( 1, 1440 ) ), $embedded_item, $post_id );

		$cache = get_post_meta( $post_id, $cachekey, true );
		$cache_time = get_post_meta( $post_id, $cachekey_time, true );

		if ( ! $cache_time ) {
			$cache_time = 0;
		}

		$cached_recently = ( time() - $cache_time ) < $ttl;

		if ( $cached_recently ) {
			if ( ! empty( $cache ) ) {
				$cache .= '<!-- Cached embed generated on ' . date( 'F jS Y, h:i:s A', $cache_time ) . ') -->';
				/**
				 * Filters the cached oEmbed HTML.
				 *
				 * @since 1.0.0
				 *
				 * @see WP_Embed::shortcode()
				 *
				 * @param mixed  $cache   The cached HTML result, stored in post meta.
				 * @param string $embedded_item  Properties of the current embedded item.
				 * @param int    $post_id Post ID.
				 */
				return apply_filters( 'cached_embed_html', $cache, $embedded_item, $post_id );
			}
		}

		return false;
	}

	/**
	 * Update the cached version of the embed, if the cache is used.
	 *
	 * @since   1.0.0
	 * @access  protected
	 *
	 * @param  array $embedded_item	Properties of the current embedded item.
	 */
	protected function update_cached_embed( $embedded_item ) {
		if ( ! $this->use_cache ) {
			return;
		}

		$post = get_post();

		$post_id = ( ! empty( $post->ID ) ) ? $post->ID : null;

		if ( ! $post_id ) {
			return;
		}

		// Check for a cached result (stored in the post meta).
		$key_suffix = md5( $embedded_item['url'] . wp_json_encode( $embedded_item['rawattr'] ) );
		$cachekey = "_embed_{$this->embed_id}_{$key_suffix}";
		$cachekey_time = "_embed_{$this->embed_id}_time_{$key_suffix}";

		if ( isset( $embedded_item['html'] ) ) {
			update_post_meta( $post_id, $cachekey, $embedded_item['html'] );
			update_post_meta( $post_id, $cachekey_time, time() );
		}
	}

	/**
	 * Get and set methods for interacting within the current and child classes
	 */

	/**
	 * Shorthand for getting all default options at once for current embed
	 *
	 * @since   1.0.0
	 * @access  protected
	 *
	 * @return  string|array           Array with all default options for this embed.
	 */
	protected function get_default_options() {
		return $this->get_default_option();
	}

	/**
	 * Get default options for current embed.
	 *
	 * @since   1.0.0
	 * @access  protected
	 *
	 * @param   string $option_name    Optional. Name of default option to retrieve.
	 *
	 * @return  string|array           A string containing the default value for $option_name if it's defined,
	 *                                 else an array with all default options for this embed.
	 */
	protected function get_default_option( $option_name = null ) {
		if ( null !== $option_name && is_string( $option_name ) ) {
			$default_options = $this->settings[ $option_name ]['default'];
		} else {
			$default_options = array();
			foreach ( $this->settings as $setting ) {
				$default_options[ $setting['id'] ] = $setting['default'];
			}
		}
		return $default_options;
	}

	/**
	 * Get embedded item from an ID
	 *
	 * Try to get an embedded item based on its ID. If no ID is provided, then it'll try
	 * to return the current embedded item, or the last embedded item.
	 *
	 * @since   1.0.0
	 * @access  protected
	 *
	 * @param string $id       Optional. ID of the embedded item to get. The ID is also the original URL to replace with an embedded item.
	 * @param string $property Optional. Specific property of the embedded item to get.
	 *
	 * @return  array|string   An array of data for the requested embedded item, or
	 *                         an array of data for the current embedded item if no ID was provided, or,
	 *                         if $property is defined, a string or an array containing the value of the
	 *                         property for the requested or current embedded item.
	 */
	protected function get_embedded_item( $id = '', $property = null ) {
		if ( empty( $id ) ) {
			return false;
		}

		if ( is_null( $property ) ) {
			return $this->embedded_items[ $id ];
		} else {
			return $this->embedded_items[ $id ][ $property ];
		}
	}

	/**
	 * Set methods
	 */

	/**
	 * Update the property of an embedded item based on its ID
	 *
	 * @since   1.0.0
	 * @access  protected
	 *
	 * @param string $id       Required. ID of the embedded item to update.
	 * @param string $property Required. The specific property of the embedded item that should be updated.
	 * @param mixed  $value    Required. The value set for the specific property of the embedded item to update.
	 *                                   The value can't be null, but it can be false or empty.
	 * @return void|bool       False is updating the property failed, nothing otherwise.
	 */
	protected function update_embedded_item( $id = null, $property = null, $value = null ) {
		if ( is_null( $id ) || is_null( $property ) || is_null( $value ) ) {
			return false;
		}

		$this->embedded_items[ $id ][ $property ] = $value;
	}

}
