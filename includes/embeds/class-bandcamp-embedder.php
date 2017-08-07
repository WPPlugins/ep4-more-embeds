<?php
/**
 * Handle Bandcamp Embed.
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
 * Handle Bandcamp Embed.
 *
 * @since      1.0.0
 * @package    EP4_More_Embeds
 * @subpackage EP4_More_Embeds/includes
 * @author     Dave Lavoie <dave.lavoie@ep4.com>
 */
class Bandcamp_Embedder extends EP4_Embedder {
	/**
	 * Embed ID.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $embed_id	The embed ID.
	 */
	public $embed_id = 'bandcamp';

	/**
	 * Embed type.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $embed_type	The embed type.
	 */
	public $embed_type = 'iframe';

	/**
	 * RegEx pattern for this embed.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $pattern     The RegEx pattern for this embed.
	 */
	public $pattern = "{https?://(?'band_id'\w+)\.bandcamp\.com/?(?:album/(?'album_id'[\w-]+))?(?:/?track/(?'track_id'[\w-]+))?}";

	/**
	 * The endpoint URL for this embed.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $endpoint    The endpoint URL for generating this embed. Should include {ids} of expected matches.
	 */
	public $endpoint = 'https://bandcamp.com/EmbeddedPlayer/band={band_id}/album={album_id}/track={track_id}/'; // https://bandcamp.com/EmbeddedPlayer/album=12345/ & https://bandcamp.com/EmbeddedPlayer/track=67890/ .

	/**
	 * The settings for this embed.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @var      array     $settings	The settings for this embed.
	 * @see		 $this::settings()		Method where the $settings are defined.
	 */
	public $settings = array();

	/**
	 * Allow caching of the embed?
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @var      bool $use_cache        Whether caching is enabled or not for this embed.
	 */
	public $use_cache = true;

	/**
	 * Allow Custom Shortcode.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      bool      $shortcode   True if custom shortcode are allowed, false otherwise.
	 */
	public $shortcode = true;

	/**
	 * Saved options pulled from the database, or else default options.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array           $options   Options.
	 */
	protected $options = array();

	/**
	 * Child method used for registering custom embed settings.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function settings() {
		$this->settings = array(
		'layout' => array(
							'id' 			=> 'layout',
							'label'			=> __( 'Layout', 'ep4-more-embeds' ),
							'description'	=> __( 'The layout of the embed.', 'ep4-more-embeds' ),
							'type'			=> 'radio',
							'options'		=> array(
														'slim'     => __( 'Slim', 'ep4-more-embeds' ),
														'artwork'  => __( 'Artwork Only', 'ep4-more-embeds' ),
														'standard' => __( 'Standard', 'ep4-more-embeds' ),
													),
							'default'		=> 'standard',
						),
		'artwork' => array(
							'id' 			=> 'artwork',
							'label'			=> __( 'Show Album Artwork', 'ep4-more-embeds' ),
							'description'	=> __( 'Select a size if you want to show album artwork.', 'ep4-more-embeds' ),
							'type'			=> 'radio',
							'default'		=> 'small',
							'options'		=> array(
														'big'   => __( 'Big', 'ep4-more-embeds' ),
														'small' => __( 'Small', 'ep4-more-embeds' ),
														'none' => __( 'None', 'ep4-more-embeds' ),
													),
						),
		'tracklist' => array(
							'id' 			=> 'tracklist',
							'label'			=> __( 'Show Tracklist', 'ep4-more-embeds' ),
							'description'	=> __( 'If checked, the album tracklist will be displayed.', 'ep4-more-embeds' ),
							'type'			=> 'checkbox',
							'default'		=> '',
						),
		'bgcol' => array(
							'id' 			=> 'bgcol',
							'label'			=> __( 'Theme', 'ep4-more-embeds' ),
							'description'	=> __( 'The color theme of the widget.', 'ep4-more-embeds' ),
							'type'			=> 'radio',
							'options'		=> array(
														'#ffffff' => __( 'Light', 'ep4-more-embeds' ),
														'#333333' => __( 'Dark', 'ep4-more-embeds' ),
													),
							'default'		=> '#ffffff',
						),
		'linkcol' => array(
							'id' 			=> 'linkcol',
							'label'			=> __( 'Link color', 'ep4-more-embeds' ),
							'description'	=> __( 'Choose a link color.', 'ep4-more-embeds' ),
							'type'			=> 'color',
							'default'		=> '#0687F5',
						),
		'width' => array(
							'id' 			=> 'width',
							'label'			=> __( 'Width', 'ep4-more-embeds' ),
							'description'	=> __( 'px', 'ep4-more-embeds' ) . ' <span>' . __( 'square', 'ep4-more-embeds' ) . '</span>',
							'type'			=> 'number',
							'default'		=> '350',
							'attributes'    => array(
														'min' => '170',
														'max' => '700',
													),
						),
		'height' => array(
							'id' 			=> 'height',
							'label'			=> __( 'Height', 'ep4-more-embeds' ),
							'description'	=> __( 'px', 'ep4-more-embeds' ),
							'type'			=> 'number',
							'default'		=> '470',
							'attributes'    => array(
														'min' => '312',
														'max' => '960',
													),
						),
		);
	}

	/**
	 * Allow the use of [bandcamp] shortcode.
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @param array $attr    Shortcode attributes.
	 * @param array $content Shortcode content. Should be empty.

	 * @return string $html	The embedded item.
	 */
	public function shortcode( $attr, $content = '' ) {
		// We're faking the URL and the subdomain since the URL is used internally as a unique identifier.
		$url = 'https://' . md5( wp_json_encode( $attr ) ) . '.bandcamp.com/';

		if ( isset( $attr['album'] ) && is_numeric( $attr['album'] ) ) {
			$url .= "album/{$attr['album']}/";
		}

		if ( isset( $attr['video'] ) && is_numeric( $attr['video'] ) ) {
		 	$url .= "track/{$attr['video']}/";
		} elseif ( isset( $attr['track'] ) && is_numeric( $attr['track'] ) ) {
			$url .= "track/{$attr['track']}/";
		}

		if ( false === strpos( $url, 'album' ) && false === strpos( $url, 'track' ) ) {
			return  __( "[bandcamp: shortcode must include 'track', 'album', or 'video' param]", 'ep4-more-embeds' );
		}

		// Set the layout based on specific param. I know....
	    if ( isset( $attr['video'] ) && is_numeric( $attr['video'] ) ) {
			$attr['layout'] = 'video';
		} elseif ( isset( $attr['minimal'] ) && 'true' === $attr['minimal'] ) {
			$attr['layout'] = 'artwork';
			unset( $attr['minimal'] );
		} elseif ( isset( $attr['size'] ) && 'small' === $attr['size'] ) {
			$attr['layout'] = 'slim';
			unset( $attr['size'] );
		}

		// Add an attribute for identifying this embed as a shortcode.
		$attr['is_shortcode'] = 'true';

		$html_attr = '';
		foreach ( $attr as $attr_key => $attr_value ) {
			$html_attr .= "{$attr_key}='{$attr_value}' ";
		}

		// Generate the html by running the [embed] shortcode through the whole embed process.
		global $wp_embed;
		$html = $wp_embed->run_shortcode( "[embed {$html_attr}]{$url}[/embed]" );

		return $html;
	}

	/**
	 * Replacing matches with data provided by the API, since that's what is expected when generating the embed.
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @param  array $embedded_item	Properties of the current embedded item.
	 * @return array $embedded_item	The embedded item, altered or not.
	 */
	public function pre_embed( $embedded_item ) {
		// Quick way to check if we're dealing with [bandcamp] shortcode.
		if ( isset( $embedded_item['attr']['is_shortcode'] ) ) {
			return $embedded_item; // Don't touch anything else, we already have the numerical IDs we need.
		}

		$matches = $embedded_item['matches'];
		$link = ( ! empty( $matches['album_id'] ) || ! empty( $matches['track_id'] ) ) ? $embedded_item['url'] : $matches[0]; // If we don't have any match, fallback to the full pattern match.

	    // Use the helper to fetch Open Graph Metadata.
		$og_data = $this->helper->fetch( $link ); // Get OG data.
		$og_url = $og_data->video_secure_url ? $og_data->video_secure_url : $og_data->video;
		wp_parse_str( str_replace( '/', '&', $og_url ), $og_url_params ); // Replace '/' with '&' so we can then parse the whole URL and extract the querystring params.

		// Override slugs with IDs when possible.
		if ( isset( $og_url_params['album'] ) && is_numeric( $og_url_params['album'] ) ) {
			$matches['album_id'] = $og_url_params['album'];
		}

		if ( isset( $og_url_params['track'] ) && is_numeric( $og_url_params['track'] ) ) {
			$matches['track_id'] = $og_url_params['track'];
		}

		// Then we replace slugs for IDs by overriding the initial regex matches.
		$embedded_item['matches'] = $matches;

		return $embedded_item;
	}

	/**
	 * Child Method used for customizing embedded item.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array  $matches	A list of regex matches for the URL.
	 * @param array  $attr  	Attributes for the embed (such as height and width).
	 * @param string $url   	The URL to be replaced with the embed.
	 * @param array  $rawattr	Raw attributes.
	 *
	 * @return void
	 */
	public function embed( $matches, $attr, $url, $rawattr ) {
		$id = $url; // The URL is the best unique identifier for this embedded item.
		$params = array(); // Used as URL parameters for the iframe src.
		$styles = array(); // Used for custom CSS styles.

		// Get default options for all Bandcamp embeds.
		if ( isset( $attr['is_shortcode'] ) ) {
			$options = wp_parse_args( $rawattr ); // Consider $rawattr for shortcodes.
		} else {
		    $options = wp_parse_args( $this->get_embedded_item( $id, 'options' ), $attr );
		}

		// Width.
		if ( isset( $options['width'] ) && '100%' === $options['width'] ) {
			$styles['width'] = '100%'; // 100% isn't a valid value for the width attribute of an iframe object.
		}

		// Layout.
		if ( ! isset( $options['layout'] ) ) {
			$options['layout'] = ''; // Define the fallback property, just in case.
		}
		switch ( $options['layout'] ) {
		    case 'video':
		    	// Deliberately do nothing for now.
			break;

			case 'slim':
				$params['size'] = 'small';
				$params['artwork'] = ( isset( $options['artwork'] ) && 'none' === $options['artwork'] ) ? 'none' : null; // Artwork.
				$options['height'] = '42'; // Override the height.
				$styles['min-width'] = '170px';
				$styles['max-width'] = '100%';
			break;

			case 'artwork':
				$params['minimal'] = 'true'; // No break is intended.
				$params['size'] = 'large';
				$options['height'] = $options['width']; // height should be the same size as width.
			break;

			case 'standard':
			default:
				// Tracklist. Fix tracklist attribute for shortcodes and tracks without album_id.
				if ( ( isset( $options['tracklist'] ) && 'false' === $options['tracklist'] ) || // shortcode provides a 'false' string and not a boolean value.
					 ( isset( $options['notracklist'] ) && 'true' === $options['notracklist'] ) || // notracklist attribue could be used even if it's unlikely.
					 ( isset( $matches['album_id'] ) && ! is_numeric( $matches['album_id'] ) ) // if the album_id isn't set, always set the tracklist to false.
				) {
					$options['tracklist'] = false; // A boolean value is expected, otherwise any string would be interpreted as true.
				}

				$params['size'] = 'large';
				$styles['max-width'] = '700px';
				if ( isset( $options['tracklist'] ) && false !== $options['tracklist'] ) {
					if ( isset( $options['artwork'] ) && 'big' === $options['artwork'] ) {
						$styles['min-height'] = intval( $options['width'] ) < 300 ? ( intval( $options['width'] ) + 172 ) . 'px' : ( intval( $options['width'] ) + 152 ) . 'px';
						$styles['max-height'] = intval( $options['width'] ) < 300 ? ( intval( $options['width'] ) + 456 ) . 'px' : ( intval( $options['width'] ) + 436 ) . 'px';
					} else {
						$styles['min-height'] = '208px';
						$styles['max-height'] = '472px';
					}
				} else {
					$params['tracklist'] = 'false';
					$options['height'] = intval( $options['width'] ) < 300 ? intval( $options['width'] ) + 143 :  intval( $options['width'] ) + 120;
				}

				// Artwork.
				if ( isset( $options['artwork'] ) && 'big' !== $options['artwork'] ) {
					$params['artwork'] = $options['artwork'];
					$options['height'] = ( isset( $options['tracklist'] ) && false !== $options['tracklist'] ) ? $options['height'] : 120;
					$styles['min-width'] = 'small' === $options['artwork'] ? '400px' : '250px'; // Lesser than 400px means no artwork will be shown.
					$styles['max-width'] = intval( $options['width'] ) < 700 ? '100%' : '700px';
				} else {
					$styles['min-width'] = '170px';

					// Package. Only works with [bandcamp] shortcode, and only if artwork is 'big'.
					if ( isset( $options['package'] ) && is_numeric( $options['package'] ) ) {
						$params['package'] = $options['package'];
						$options['height'] = ( (int) $options['height'] ) + 66;
						$styles['min-height'] = '348px';
					}
				}
			break;

		} // End switch().

		// Background Color.
		if ( isset( $options['bgcol'] ) && is_string( $options['bgcol'] ) ) {
			$params['bgcol'] = sanitize_hex_color_no_hash( $options['bgcol'] ); // No # prefix is intended.
			if ( 'ffffff' !== $params['bgcol'] ) {
				$params['transparent'] = 'true';
			}
		}

		// Link Color.
		if ( isset( $options['linkcol'] ) && is_string( $options['linkcol'] ) ) {
			$params['linkcol'] = sanitize_hex_color_no_hash( $options['linkcol'] ); // API doesn't expect #.
		}

		// Generate the src based on $args.
		$params = array_filter( $params ); // Remove all false and null values from the array.
		$src = add_query_arg( $params, $this->get_embedded_item( $id, 'src' ) );

		// Last adjustment if is video. Only works with [bandcamp video=1234567] shortcode.
		if ( 'video' === $options['layout'] ) { // Layout is set to 'video' in shortcode.
			// We must change the src if it's a video since it's not the same embed method that'll be used.
			$src = str_replace( array( '/EmbeddedPlayer/', '/?', "band={$matches['band_id']}/", "album={$matches['album_id']}/" ), array( '/VideoEmbed?', '&' ), $src );
		} else {
			$src = str_replace( array( '&', '?', '/track={track_id}', "/band={$matches['band_id']}" ), array( '/' ), $src ); // We don't need an empty track id or the band id.
		}

		// Update embedded item before generating it.
		$this->update_embedded_item( $id, 'src', $src ); // Replace the embed URL for one including parameters, it's how Bandcamp API works.
		$this->update_embedded_item( $id, 'options', $options ); // Update current embedded item options. Mainly used for updating width and height options used when generating the iframe.
		$this->update_embedded_item( $id, 'styles', $styles );
	}
}
