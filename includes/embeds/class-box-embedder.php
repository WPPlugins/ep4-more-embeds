<?php
/**
 * Handle Box.com Embed.
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
 * Handle Box.com Embed.
 *
 * @since      1.0.0
 * @package    EP4_More_Embeds
 * @subpackage EP4_More_Embeds/includes
 * @author     Dave Lavoie <dave.lavoie@ep4.com>
 */
class Box_Embedder extends EP4_Embedder {
	/**
	 * Embed ID.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $embed_id	The embed ID.
	 */
	public $embed_id = 'box';

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
	 * @var      string    $pattern     The RegEx pattern for this embed. Each capture group must be named with the same keys expected in the endpoint URL.
	 */
	public $pattern = '<https?://(?P<subdomain>\w+\.)?(?P<domain>\w+)\.box\.com/(?:embed/preview/|embed_widget/)?(?P<prefix>s|files/0/f)/(?P<id>\w+)>';

	/**
	 * The endpoint URL for this embed.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $endpoint    The endpoint URL for generating this embed. Must include {ids} of expected matches.
	 */
	public $endpoint = 'https://{subdomain}{domain}.box.com/embed_widget/{prefix}/{id}';

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
		'width' => array(
							'id' 			=> 'width',
							'label'			=> __( 'Width', 'ep4-more-embeds' ),
							'description'	=> __( 'px', 'ep4-more-embeds' ),
							'type'			=> 'number',
							'default'		=> '550',
						),
		'height' => array(
							'id' 			=> 'height',
							'label'			=> __( 'Height', 'ep4-more-embeds' ),
							'description'	=> __( 'px', 'ep4-more-embeds' ),
							'type'			=> 'number',
							'default'		=> '400',
						),
		'view' => array(
							'id' 			=> 'view',
							'label'			=> __( 'View', 'ep4-more-embeds' ),
							'description'	=> __( 'The view type for your files or folders.', 'ep4-more-embeds' ),
							'type'			=> 'radio',
							'options'		=> array(
														'list' => __( 'List', 'ep4-more-embeds' ),
														'icon' => __( 'Icons', 'ep4-more-embeds' ),
													),
							'default'		=> 'list',
						),
		'sort' => array(
							'id' 			=> 'sort',
							'label'			=> __( 'Sort', 'ep4-more-embeds' ),
							'description'	=> __( 'The order the files or folders are sorted in.', 'ep4-more-embeds' ),
							'type'			=> 'select',
							'default'		=> 'date',
							'options'		=> array(
														'name' => __( 'Name', 'ep4-more-embeds' ),
														'date' => __( 'Date', 'ep4-more-embeds' ),
														'size' => __( 'Size', 'ep4-more-embeds' ),
													),
						),
		'direction' => array(
							'id' 			=> 'direction',
							'label'			=> __( 'Direction', 'ep4-more-embeds' ),
							'description'	=> __( 'The sort direction of files or folders.', 'ep4-more-embeds' ),
							'type'			=> 'radio',
							'options'		=> array(
														'asc'  => __( 'ASC', 'ep4-more-embeds' ),
														'desc' => __( 'DESC', 'ep4-more-embeds' ),
													),
							'default'		=> 'asc',
						),
		'theme' => array(
							'id' 			=> 'theme',
							'label'			=> __( 'Theme', 'ep4-more-embeds' ),
							'description'	=> __( 'The color theme of the widget.', 'ep4-more-embeds' ),
							'type'			=> 'radio',
							'options'		=> array(
														'blue' => __( 'Blue', 'ep4-more-embeds' ),
														'gray' => __( 'Gray', 'ep4-more-embeds' ),
													),
							'default'		=> 'blue',
						),
		'show_parent_path' => array(
							'id' 			=> 'show_parent_path',
							'label'			=> __( 'Show Parent Path', 'ep4-more-embeds' ),
							'description'	=> __( 'Hide or show the folder path in the header of the frame.', 'ep4-more-embeds' ),
							'type'			=> 'checkbox',
							'default'		=> 'on',
						),
		'show_item_feed_action' => array(
							'id' 			=> 'show_item_feed_action',
							'label'			=> __( 'Show Item Feed Action', 'ep4-more-embeds' ),
							'description'	=> __( 'Hide or show file comments or tasks.', 'ep4-more-embeds' ),
							'type'			=> 'checkbox',
							'default'		=> 'on',
						),
		'view_file_only' => array(
							'id' 			=> 'view_file_only',
							'label'			=> __( 'View File Only', 'ep4-more-embeds' ),
							'description'	=> __( 'User cannot download, print, or see the folder in which the file resides, even if the user has access.', 'ep4-more-embeds' ),
							'type'			=> 'checkbox',
							'default'		=> '',
						),
		);
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
		// The URL is the best unique identifier for this embedded item.
		$id = $url;

		// Get default options for all Box.com embeds.
		$default_options = wp_parse_args( $attr, $this->get_embedded_item( $id, 'options' ) ); // Consider $attr for shortcodes.

		// Get custom options for this specific embed.
		$parsed_url = wp_parse_url( $url );
		if ( ! empty( $parsed_url ) && isset( $parsed_url['query'] ) ) {
			wp_parse_str( $parsed_url['query'],	$custom_options ); // Get custom options that could have been appended to the URL...
		}

		// Merge custom options with default ones AND only keep the options whose keys can be found in $default_options. All other custom options aren't allowed and are removed.
		$options = isset( $custom_options ) ? array_intersect_key( wp_parse_args( $custom_options, $default_options ), $default_options ) : $default_options;

		// Update embedded item options before generating the embedded item.
		$this->update_embedded_item( $id, 'src', add_query_arg( $options, $this->get_embedded_item( $id, 'src' ) ) ); // Replace the embed URL for one including parameters, it's how Box.com Embed Api works.
		if ( isset( $custom_options ) ) {
			$this->update_embedded_item( $id, 'options', $options ); // Update current embedded item options. Mainly used for updating width and height options for the iframe.
		}
	}

}
