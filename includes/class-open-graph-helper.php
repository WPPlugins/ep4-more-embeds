<?php
/**
 * Open Graph Helper Class
 *
 * @link       https://ep4.com
 * @since      1.0.0
 *
 * @package    EP4_More_Embeds
 * @subpackage EP4_More_Embeds/includes
 */

/**
 * Copyright 2010 Scott MacVicar
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 *	Original can be found at {@link https://github.com/scottmac/opengraph/blob/master/OpenGraph.php}.
 *  Fork used for the current class can be found at {@link https://github.com/AramZS/opengraph}.
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Open Graph Helper Class
 *
 * The current class is a direct implementation of {@link https://github.com/AramZS/opengraph}.
 * It has been adapted to use WordPress native functions when possible, such as in the fetch() method.
 * The current file has been passed through PHP CodeSniffer with WordPress coding standards ruleset and
 * has been adapted consequently.
 *
 * @since      1.0.0
 * @package    EP4_More_Embeds
 * @subpackage EP4_More_Embeds/includes
 * @author     Dave Lavoie <dave.lavoie@ep4.com>
 */
class Open_Graph_Helper implements Iterator {
	/**
	 * Schema Types.
	 *
	 * There are base schema's based on type, this is just
	 * a map so that the schema can be obtained.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      array  $schema_types Schema Types.
	 */
	public static $schema_types = array(
		'activity' => array( 'activity', 'sport' ),
		'business' => array( 'bar', 'company', 'cafe', 'hotel', 'restaurant' ),
		'group' => array( 'cause', 'sports_league', 'sports_team' ),
		'organization' => array( 'band', 'government', 'non_profit', 'school', 'university' ),
		'person' => array( 'actor', 'athlete', 'author', 'director', 'musician', 'politician', 'public_figure' ),
		'place' => array( 'city', 'country', 'landmark', 'state_province' ),
		'product' => array( 'album', 'book', 'drink', 'food', 'game', 'movie', 'product', 'song', 'tv_show' ),
		'website' => array( 'blog', 'website' ),
	);

	/**
	 * Open Graph Values.
	 *
	 * Holds all the Open Graph values we've parsed from a page.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      array  $og_values    Open Graph Values.
	 */
	private $og_values = array();

	/**
	 * Helper method to access attributes directly.
	 * Example: $graph->title
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param string $key Key to fetch from the lookup.
	 */
	public function __get( $key ) {
		if ( array_key_exists( $key, $this->og_values ) ) {
			return $this->og_values[ $key ];
		}

		if ( 'schema' === $key ) {
			foreach ( self::$schema_types as $schema => $types ) {
				if ( array_search( $this->og_values['type'], $types, true ) ) {
					return $schema;
				}
			}
		}
	}

	/**
	 * Helper method to access attributes with an URL.
	 * Example: $graph->title
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param string $key   Key to fetch from the lookup.
	 * @param string $args  Args to fetch from the lookup. Expecting an URL for $args[0].
	 */
	public function __call( $key, $args ) {
		$html = $this->fetch( $args[0] );

		if ( array_key_exists( $key, $this->og_values ) ) {
			return $this->og_values[ $key ];
		}

		if ( 'schema' === $key ) {
			foreach ( self::$schema_types as $schema => $types ) {
				if ( array_search( $this->og_values['type'], $types, true ) ) {
					return $schema;
				}
			}
		}
	}

	/**
	 * Helper method to check if an attribute exists.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param string $key Attribute key.
	 * @return bool       True if the attribute exists, false otherwise.
	 */
	public function __isset( $key ) {
		return array_key_exists( $key, $this->og_values );
	}

	/**
	 * Helper method to check if an attribute exists.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return string Array of values encoded in a JSON string.
	 */
	public function __toString() {
		if ( ! empty( $this->og_values ) ) {
			return wp_json_encode( $this->og_values );
		}
		return wp_json_encode( array() );
	}

	/**
	 * Iterator code Implementation.
	 *
	 * The following methods relate to the Iterator implementation.
	 * Not actually sure if it's needed at all. Could probably be
	 * removed if it's not used.
	 */

	/**
	 * Open Graph Values.
	 *
	 * Holds all the Open Graph values we've parsed from a page.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      array  $og_values    Open Graph Values.
	 */
	private $iterator_position = 0;

	/**
	 * Rewinds the iterator position to 0.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function rewind() {
		reset( $this->og_values );
		$this->iterator_position = 0;
	}

	/**
	 * Returns current Open Graph value.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return string Current Open Graph value.
	 */
	public function current() {
		return current( $this->og_values );
	}

	/**
	 * Returns current Open Graph key.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return string Current Open Graph key.
	 */
	public function key() {
		return key( $this->og_values );
	}

	/**
	 * Increments the iterator position to the next value.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function next() {
		next( $this->og_values );
		++$this->iterator_position;
	}

	/**
	 * Checks if the current iterator position is valid.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return bool True if current iterator position is valid, false otherwise.
	 */
	public function valid() {
		return $this->iterator_position < count( $this->og_values );
	}

	/**
	 * /END Iterator code Implementation.
	 */

	/**
	 * Fetches a URI and parses it for Open Graph data, returns
	 * false on error.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param    string $url          URI to page to parse for Open Graph data.
	 * @return   mixed                Open_Graph_Helper, or false.
	 */
	public function fetch( $url ) {
		$fetched_page = wp_safe_remote_get( $url );
		$html = wp_remote_retrieve_body( $fetched_page );

		if ( ! empty( $html ) ) {
			return $this->parse( $html );
		} else {
			return false;
		}
	}

	/**
	 * Parses HTML and extracts Open Graph data after converting HTML encoding to UTF-8.
	 * This assumes the document is at least well formed.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param string $html HTML to parse.
	 * @return   mixed Open_Graph_Helper, or false.
	 */
	public function parse( $html ) {
		if ( empty( $html ) ) {
			return false;
		}
		$html = mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' );
		$this->og_values = array(); // Reinit the values for every new parse.
		return $this->_parse( $html );
	}

	/**
	 * Parses HTML and extracts Open Graph data, this assumes
	 * the document is at least well formed.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @param string $html HTML to parse.
	 * @return OpenGraph
	 */
	private function _parse( $html ) {

		$old_libxml_error = libxml_use_internal_errors( true );

		$doc = new DOMDocument();
		$doc->loadHTML( $html );

		libxml_use_internal_errors( $old_libxml_error );

		$tags = $doc->getElementsByTagName( 'meta' );
		if ( ! $tags || 0 === $tags->length ) {
			return false;
		}

		$non_og_description = null;

		foreach ( $tags as $tag ) {
			if ( $tag->hasAttribute( 'property' ) && strpos( $tag->getAttribute( 'property' ), 'og:' ) === 0 ) {
				$key = str_replace( array( '-', ':' ), array( '_', '_' ), substr( $tag->getAttribute( 'property' ), 3 ) );

		        if ( array_key_exists( $key, $this->og_values ) ) {
					if ( ! array_key_exists( $key . '_additional', $this->og_values ) ) {
						$this->og_values[ $key . '_additional' ] = array();
					}
		        	$this->og_values[ $key . '_additional' ][] = $tag->getAttribute( 'content' );
		        } else {
		        	$this->og_values[ $key ] = $tag->getAttribute( 'content' );
		        }
			}

			// Added this if loop to retrieve description values from sites like the New York Times who have malformed it.
			if ( $tag->hasAttribute( 'value' ) && $tag->hasAttribute( 'property' ) &&
			    strpos( $tag->getAttribute( 'property' ), 'og:' ) === 0 ) {
				$key = strtr( substr( $tag->getAttribute( 'property' ), 3 ), '-', '_' );
				$this->og_values[ $key ] = $tag->getAttribute( 'value' );
			}
			// Based on modifications at https://github.com/bashofmann/opengraph/blob/master/src/OpenGraph/OpenGraph.php.
			if ( $tag->hasAttribute( 'name' ) && $tag->getAttribute( 'name' ) === 'description' ) {
				$non_og_description = $tag->getAttribute( 'content' );
			}

			if ( $tag->hasAttribute( 'property' ) &&
			    strpos( $tag->getAttribute( 'property' ), 'twitter:' ) === 0 ) {
				$key = strtr( $tag->getAttribute( 'property' ), '-:', '__' );
				$this->og_values[ $key ] = $tag->getAttribute( 'content' );
			}

			if ( $tag->hasAttribute( 'name' ) &&
				strpos( $tag->getAttribute( 'name' ), 'twitter:' ) === 0 ) {
				$key = strtr( $tag->getAttribute( 'name' ), '-:', '__' );
				if ( array_key_exists( $key, $this->og_values ) ) {
					if ( ! array_key_exists( $key . '_additional', $this->og_values ) ) {
						$this->og_values[ $key . '_additional' ] = array();
					}
					$this->og_values[ $key . '_additional' ][] = $tag->getAttribute( 'content' );
				} else {
					$this->og_values[ $key ] = $tag->getAttribute( 'content' );
				}
			}

			// Notably this will not work if you declare type after you declare type values on a page.
			if ( array_key_exists( 'type', $this->og_values ) ) {
				$meta_key = $this->og_values['type'] . ':';
				if ( $tag->hasAttribute( 'property' ) && strpos( $tag->getAttribute( 'property' ), $meta_key ) === 0 ) {
					$meta_key_len = strlen( $meta_key );
					$key = strtr( substr( $tag->getAttribute( 'property' ), $meta_key_len ), '-', '_' );
					$key = $this->og_values['type'] . '_' . $key;

					if ( array_key_exists( $key, $this->og_values ) ) {
						if ( ! array_key_exists( $key . '_additional', $this->og_values ) ) {
							$this->og_values[ $key . '_additional' ] = array();
						}
						$this->og_values[ $key . '_additional' ][] = $tag->getAttribute( 'content' );
					} else {
						$this->og_values[ $key ] = $tag->getAttribute( 'content' );
					}
				}
			}
		}// End foreach().

		// Based on modifications at https://github.com/bashofmann/opengraph/blob/master/src/OpenGraph/OpenGraph.php.
		if ( ! isset( $this->og_values['title'] ) ) {
			$titles = $doc->getElementsByTagName( 'title' );
			if ( $titles->length > 0 ) {
				$this->og_values['title'] = $titles->item( 0 )->textContent;
			}
		}
		if ( ! isset( $this->og_values['description'] ) && $non_og_description ) {
			$this->og_values['description'] = $non_og_description;
		}

		// Fallback to use image_src if ogp::image isn't set.
		if ( ! isset( $this->og_values['image'] ) ) {
			$domxpath = new DOMXPath( $doc );
			$elements = $domxpath->query( "//link[@rel='image_src']" );

			if ( $elements->length > 0 ) {
				$domattr = $elements->item( 0 )->attributes->getNamedItem( 'href' );
				if ( $domattr ) {
					$this->og_values['image'] = $domattr->value;
					$this->og_values['image_src'] = $domattr->value;
				}
			} elseif ( ! empty( $this->og_values['twitter_image'] ) ) {
				$this->og_values['image'] = $this->og_values['twitter_image'];
			} else {
				$elements = $doc->getElementsByTagName( 'img' );
				foreach ( $elements as $tag ) {
					if ( $tag->hasAttribute( 'width' ) && ( ( $tag->getAttribute( 'width' ) > 300 ) || ( $tag->getAttribute( 'width' ) === '100%' ) ) ) {
						$this->og_values['image'] = $tag->getAttribute( 'src' );
						break;
					}
				}
			}
		}

		if ( empty( $this->og_values ) ) { return false; }

		return $this;
	}

	/**
	 * Return all the keys found on the page.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return array Open Graph values.
	 */
	public function keys() {
		return array_keys( $this->og_values );
	}

	/**
	 * Will return true if the page has location data embedded.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return boolean True if the page has location data, false otherwise.
	 */
	public function has_location() {
		if ( array_key_exists( 'latitude', $this->og_values ) && array_key_exists( 'longitude', $this->og_values ) ) {
			return true;
		}

		$address_keys = array( 'street_address', 'locality', 'region', 'postal_code', 'country_name' );
		$valid_address = true;
		foreach ( $address_keys as $key ) {
			$valid_address = ($valid_address && array_key_exists( $key, $this->og_values ));
		}
		return $valid_address;
	}
}
