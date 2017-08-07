<?php
/**
 * Handle VEVO embed.
 *
 * @link       https://ep4.com
 * @since      1.0.0
 *
 * @package    EP4_More_Embeds
 * @subpackage EP4_More_Embeds
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handle VEVO Embed.
 *
 * @since      1.0.0
 * @package    EP4_More_Embeds
 * @subpackage EP4_More_Embeds/includes
 * @author     Dave Lavoie <dave.lavoie@ep4.com>
 */
class Vevo_Embedder extends EP4_Embedder {
	/**
	 * Embed ID.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string   $embed_id		The embed ID.
	 */
	public $embed_id = 'vevo';

	/**
	 * Embed Type.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string   $embed_type	The embed type.
	 */
	public $embed_type = 'iframe';

	/**
	 * RegEx pattern for this embed.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string   $pattern		RegEx pattern for this embed.
	 */
	public $pattern = '<https?://www\.vevo\.com/watch/(?:[^/]+/)?(?:[^/]+/)?(?P<video_id>\w+)>i'; // Handles http://www.vevo.com/* & https://www.vevo.com/* schemes.

	/**
	 * Endpoint URL for this embed.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string   $endpoint		An endpoint URL.
	 */
	public $endpoint = 'https://scache.vevo.com/assets/html/embed.html?video={video_id}';

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
	 * @return string $html
	 */
	public function embed( $matches, $attr, $url, $rawattr ) {
		// The URL is the best unique identifier for this embedded item.
		$id = $url;

		// Defining the width and height so we have a 16:9 ratio.
		$options = array(
			'width'  => 640,
			'height' => 360,
		);

		// Update current embedded item options. Mainly used for updating width and height options for the iframe.
		$this->update_embedded_item( $id, 'options', $options );

		// Generate the iframe.
		$html = $this->generate_iframe();
		$html = $this->make_responsive( $html );

		// By returning an HTML string, we're bypassing the parent class embed method.
		return $html;
	}
}
