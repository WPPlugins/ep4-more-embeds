<?php
/**
 * Handle Facebook embed.
 *
 * @link       https://ep4.com
 * @since      1.0.0
 *
 * @package    EP4_More_Embeds
 * @subpackage EP4_More_Embeds/includes/embeds
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handle Facebook Embed.
 *
 * @since      1.0.0
 * @package    EP4_More_Embeds
 * @subpackage EP4_More_Embeds/includes/embeds
 * @author     Dave Lavoie <dave.lavoie@ep4.com>
 */
class Facebook_Embedder extends EP4_Embedder {

	/**
	 * Embed ID.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string   $embed_id		The embed ID.
	 */
	public $embed_id = 'facebook';

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
	 * @var      array    $pattern		An associative array of RegEx patterns and endpoint scheme types
	 */
	public $pattern = array(
		'#https?://www\.facebook\.com/video.php.*#i'					=> 'video',
		'#https?://www\.facebook\.com/.*/videos/.*#i'					=> 'video',
		'#https?://www\.facebook\.com/.*/posts/.*#i'    				=> 'post',	// https://www.facebook.com/{page-name}/posts/{post-id} & /{username}/posts/{post-id}
		'#https?://www\.facebook\.com/.*/activity/.*#i'     			=> 'post',	// https://www.facebook.com/{username}/activity/{activity-id}
		'#https?://www\.facebook\.com/photo(s/|.php).*#i'   			=> 'post',  // https://www.facebook.com/photos/{photo-id} & /photo.php?fbid={photo-id}
		'#https?://www\.facebook\.com/permalink.php\?story_fbid=.*#i'   => 'post',  // https://www.facebook.com/permalink.php?story_fbid={post-id}
		'#https?://www\.facebook\.com/media/.*#i'           			=> 'post',  // https://www.facebook.com/media/{media-id}
		'#https?://www\.facebook\.com/media/set/?\?set=.*#i' 			=> 'post',  // https://www.facebook.com/media/set?set={set-id} & /media/set/?set={set-id}
		'#https?://www\.facebook\.com/questions/.*#i'       			=> 'post',	// https://www.facebook.com/questions/{question-id}
		'#https?://www\.facebook\.com/notes/.*#i'           			=> 'post',  // (?) https://www.facebook.com/notes/{username}/{note-url}/{note-id} | Not validated.
	);

	/**
	 * Endpoint URLs for each endpoint scheme type.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      array    $endpoint		An associative array of endpoint scheme types and endpoints URL
	 */
	public $endpoint = array(
		'video'	=> 'https://www.facebook.com/plugins/video/oembed.json/',
		'post'	=> 'https://www.facebook.com/plugins/post/oembed.json/',
	);

}
