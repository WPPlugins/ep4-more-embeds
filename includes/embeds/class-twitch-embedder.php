<?php
/**
 * Handle Twitch embed.
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
 * Handle Twitch Embed.
 *
 * @since      1.0.0
 * @package    EP4_More_Embeds
 * @subpackage EP4_More_Embeds/includes
 * @author     Dave Lavoie <dave.lavoie@ep4.com>
 */
class Twitch_Embedder extends EP4_Embedder {
	/**
	 * Embed ID.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string   $embed_id		The embed ID.
	 */
	public $embed_id = 'twitch';

	/**
	 * Embed Type.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string   $embed_type	The embed type.
	 */
	public $embed_type = 'oembed';

	/**
	 * RegEx patterns for this embed.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      array    $pattern		An associative array of RegEx patterns and endpoint scheme types.
	 */
	public $pattern = array(
		'#https?://(clips\.|www\.)?twitch\.tv/.*#i', // Handles clips.twitch.tv/* & www.twitch.tv/* & twitch.tv/* (including both http and https protocols).
	);

	/**
	 * Endpoint URL for this embed.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string   $endpoint		An endpoint URL.
	 */
	public $endpoint = 'https://api.twitch.tv/v4/oembed';

}
