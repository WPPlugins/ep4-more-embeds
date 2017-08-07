<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://ep4.com
 * @since      1.0.0
 *
 * @package    EP4_More_Embeds
 * @subpackage EP4_More_Embeds/core
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    EP4_More_Embeds
 * @subpackage EP4_More_Embeds/core
 * @author     Dave Lavoie <dave.lavoie@ep4.com>
 */
class EP4_More_Embeds_Deactivator {

	/**
	 * Run on deactivation hook.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		// Clear all oembed items cached by WP.
		self::clear_oembed_cache();

		// Clear custom embed items.
		self::clear_embed_cache();
	}

	/**
	 * Clear oembed cache when deactivating the plugin.
	 *
	 * @since    1.0.0
	 */
	public static function clear_oembed_cache() {
		// Get a list of post IDs with oembeds.
		$post_ids = self::get_cached_oembed_post_ids();

		if ( is_array( $post_ids ) ) {
			global $wp_embed;
			foreach ( $post_ids as $post_id ) {
				$wp_embed->delete_oembed_caches( $post_id );
			}
		}
	}

	/**
	 * Clear custom embed cache when deactivating the plugin.
	 *
	 * @since    1.0.0
	 */
	public static function clear_embed_cache() {
		// Get a list of post IDs with embeds.
		$post_ids = self::get_cached_embed_post_ids();

		if ( is_array( $post_ids ) ) {
			foreach ( $post_ids as $post_id ) {
				self::delete_embed_caches( $post_id );
			}
		}
	}

	/**
	 * Delete all embed caches for a post.
	 *
	 * This method was taken from WP core and slightly adapted.
	 *
	 * @see WP_Embed::delete_oembed_caches()
	 *
	 * @param int $post_id Post ID to delete the caches for.
	 */
	public static function delete_embed_caches( $post_id ) {
		$post_metas = get_post_custom_keys( $post_id );
		if ( empty( $post_metas ) ) {
			return;
		}

		foreach ( $post_metas as $post_meta_key ) {
			if ( '_embed_' === substr( $post_meta_key, 0, 7 ) ) {
				delete_post_meta( $post_id, $post_meta_key );
			}
		}
	}

	/**
	 * Get a list of post IDs for which oembed elements have been cached.
	 *
	 * @since    1.0.0
	 */
	public static function get_cached_oembed_post_ids() {
		$post_ids = self::get_post_ids_from_meta_key( '_oembed_%' );
		return ( ! empty( $post_ids ) ) ? $post_ids : array();
	}

	/**
	 * Get a list of post IDs for which custom embed elements have been cached.
	 *
	 * @since    1.0.0
	 */
	public static function get_cached_embed_post_ids() {
		$post_ids = self::get_post_ids_from_meta_key( '_embed_%' );
		return ( ! empty( $post_ids ) ) ? $post_ids : array();
	}

	/**
	 * Get a list of post IDs based on the existence of a meta key.
	 *
	 * @since    1.0.0
	 *
	 * @param string $meta_key The meta key.
	 * @return array|bool An array of post IDs, or false.
	 */
	public static function get_post_ids_from_meta_key( $meta_key = false ) {
		if ( ! $meta_key || ! is_string( $meta_key ) ) {
			return false;
		}

		global $wpdb;

		$query = $wpdb->prepare(
			"
			SELECT DISTINCT		post_id
			FROM				$wpdb->postmeta
			WHERE				meta_key LIKE %s
			",
			$meta_key
		);
		$post_ids = $wpdb->get_col( $query ); // WPCS: unprepared SQL OK.

		return $post_ids;
	}
}
