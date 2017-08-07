<?php
/**
 * Create an admin settings page.
 *
 * @link       https://ep4.com
 * @since      1.0.0
 * @see        https://github.com/hlashbrooke/WordPress-Plugin-Template
 *
 * @package    EP4_More_Embeds
 * @subpackage EP4_More_Embeds/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create an admin settings page.
 *
 * @link       https://ep4.com
 * @since      1.0.0
 * @see        https://github.com/hlashbrooke/WordPress-Plugin-Template
 *
 * @package    EP4_More_Embeds
 * @subpackage EP4_More_Embeds/includes
 */
class EP4_More_Embeds_Admin_Manager {
	/**
	 * An admin page object.
	 *
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $page = null;

	/**
	 * The settings ID
	 *
	 * @var     array
	 * @access  private
	 * @since   1.0.0
	 */
	private $settings_id = '';

	/**
	 * Available settings for plugin.
	 *
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @access public
	 * @since    1.0.0
	 */
	public function __construct() {
		// Nothing there for the moment.
	}

	/**
	 * Add menu item linking to the settings page to admin menu
	 *
	 * Add settings page to admin menu.
	 * Usually triggered by the parent plugin object using the admin_menu action hook
	 *
	 * @access	public
	 * @since	1.0.0
	 * @see		$this::settings_page()	Callback method used by add_options_page() for displaying the settings page
	 *
	 * @return	void
	 */
	public function add_menu_item() {
		$page = add_options_page( $this->page->page_name, $this->page->page_name, 'manage_options', $this->page->page_id,  array( $this, 'display_settings_page' ) );
	}

	/**
	 * Load settings JS & CSS
	 *
	 * Load JS & CSS for the settings page in wp-admin.
	 * Usually triggered by the parent plugin object using the admin_enqueue_scripts action hook, but can also be called by
	 * admin_print_scripts-(hookname) / admin_print_styles-(hookname) action hooks. If the latter are used, then
	 * the scripts/styles will only be enqueued on the specific admin page having (hookname) as an ID.
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return	void
	 */
	public function load_settings_assets() {
		// We're listing all needed dependencies here.
		$script_dependencies = array(
			'wp-color-picker',
			'jquery',
		);

		$style_dependencies = array(
			'wp-color-picker',
		);

		// We're including the WP media scripts here because they're needed for the image upload field.
		wp_enqueue_media();

		wp_enqueue_script( $this->page->page_id . '-admin-manager', $this->page->assets_url . 'js/admin-manager.js', $script_dependencies );

		wp_enqueue_style( $this->page->page_id . '-admin-manager', $this->page->assets_url . 'css/admin-manager.css', $style_dependencies );

	}

	/**
	 * Add action links to plugin list table
	 *
	 * The default action links for the Network plugins list table include
	 * 'Network Activate', 'Network Deactivate', 'Edit', and 'Delete'.
	 * Usually triggered by the parent plugin object using the plugin_action_links filter.
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @param array  $links 		An array of plugin current action links.
	 * @param string $plugin_file	Path to the plugin file relative to the plugins directory.
	 * @param array  $plugin_data	An array of plugin data.
	 * @param string $context   	The plugin context. Defaults are 'All', 'Active',
	 *                          	'Inactive', 'Recently Activated', 'Upgrade',
	 *                          	'Must-Use', 'Drop-ins', 'Search'.
	 *
	 * @return array $links			Modified links
	 */
	public function add_action_links( $links, $plugin_file, $plugin_data, $context ) {
		// Let's compare root directory of the $plugin_file passed by param against the root directory of the current plugin. This is currently the simplest way to automatize this
		$filtered_plugin_root   = strtok( $plugin_file, '/' ); // Only keep the plugin root directory.
		$plugin_root = strtok( plugin_basename( ( __FILE__ ) ), '/' );

		if ( $filtered_plugin_root === $plugin_root ) {
			$links[ $this->page->page_id ] = '<a href="options-general.php?page=' . $this->page->page_id . '">' . $this->page->page_name . '</a>';
		}

			return $links;
	}

	/**
	 * Register plugin settings
	 *
	 * Register all settings used by the current plugin. Usually triggered by the parent
	 * plugin object with the admin_init action hook.
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return	void
	 */
	public function register_settings() {
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab.
			$current_section = '';
			if ( $this->is_tab() ) {
				$current_section = $this->get_tab();
			}

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section !== $section ) {
					continue;
				}

				// Add section to page.
				add_settings_section( $section, $data['title'], array( $this, 'add_settings_section' ), $this->page->page_id );

				// Register setting.
				register_setting( $this->page->page_id, $this->get_settings_id(), array( $this, 'validate_settings' ) );

				foreach ( $data['fields'] as $field ) {
					// Add field to page.
					add_settings_field( $field['id'], $field['label'], array( $this, 'display_field' ), $this->page->page_id, $section,  $field );
				}

				if ( ! $current_section ) {
					break;
				}
			}
		}
	}

	/**
	 * Callback for add_settings_section function
	 *
	 * @access	public
	 * @since	1.0.0
	 * @see		$this::register_settings() Method where this callback is called
	 *
	 * @param	array $section  Values defined during the add_settings_section call,
	 *							including the following parameters: id, title, callback.
	 * @return	void
	 */
	public function add_settings_section( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>';
		echo $html; // WPCS: XSS OK.
	}

	/**
	 * Validate submitted data passed by parameter.
	 *
	 * Validate submitted data passed by parameter. Used as a callback for register_setting function.
	 *
	 * @todo	Eventually exclude the validation logic for each data type from this method,
	 *			and move the validation logic for each type to specific methods.
	 * @todo	Implement a better validation for checkbox_multi, select_multi, text_secret, hidden, password, image, color.
	 *
	 * @access	public
	 * @since	1.0.0
	 * @see		$this::register_settings() Method where this callback is called.
	 *
	 * @param  array $submitted_values   Array of values submitted by the user.
	 * @return string	$returned_values	Array of validated values for saving to the databse.
	 */
	public function validate_settings( $submitted_values = array() ) {
		if ( $this->is_tab() ) {
			$current_tab = $this->get_tab(); // So we can get the original fields to compare with.
		} else {
			reset( $this->settings );
			$current_tab = key( $this->settings ); // If there's no current tab then let's assume it's the first one.
		}

		$fields = $this->settings[ $current_tab ]['fields'];
		$current_options = $this->get_option();
		$returned_values = array();

		foreach ( $fields as $field_id => $field ) {
			switch ( $field['type'] ) {
				case 'text':
					$returned_values[ $field_id ] = is_string( $submitted_values[ $field_id ] ) ? sanitize_text_field( $submitted_values[ $field_id ] ) : $current_options[ $field_id ];
					break;
				case 'url':
					$returned_values[ $field_id ] = is_string( $submitted_values[ $field_id ] ) ? esc_url( $submitted_values[ $field_id ] ) : $current_options[ $field_id ];
					break;
				case 'email':
					$returned_values[ $field_id ] = is_email( $submitted_values[ $field_id ] ) ? sanitize_email( $submitted_values[ $field_id ] ) : $current_options[ $field_id ];
					break;
				case 'number':
					$returned_values[ $field_id ] = is_numeric( $submitted_values[ $field_id ] ) ? (int) $submitted_values[ $field_id ] : $current_options[ $field_id ];
					break;
				case 'textarea':
					$returned_values[ $field_id ] = is_string( $submitted_values[ $field_id ] ) ? wp_kses_post( $submitted_values[ $field_id ] ) : $current_options[ $field_id ];
					break;
				case 'checkbox':
					$returned_values[ $field_id ] = isset( $submitted_values[ $field_id ] ) && false !== $submitted_values[ $field_id ] ? 'on' : false;
					break;
				case 'radio':
				case 'select':
					$returned_values[ $field_id ] = in_array( (string) $submitted_values[ $field_id ], array_map( 'strval', array_keys( $field['options'] ) ), true ) ? $submitted_values[ $field_id ] : array();
					break;
				case 'checkbox_multi':
				case 'select_multi':
					// Will need better validation.
					$returned_values[ $field_id ] = is_array( $submitted_values[ $field_id ] ) ? $submitted_values[ $field_id ] : array();
					break;
				case 'color':
					$returned_values[ $field_id ] = sanitize_hex_color( $submitted_values[ $field_id ] );
					break;
				case 'text_secret':
				case 'hidden':
				case 'password':
				case 'image': // need to validate.
				default:
					$returned_values[ $field_id ] = seems_utf8( $submitted_values[ $field_id ] ) ? $submitted_values[ $field_id ] : $field['default'];
			}// End switch().
		}// End foreach().

		// Let's merge the validated values with the current settings.
		$returned_values = wp_parse_args( $returned_values, $current_options );

		return $returned_values;
	}

	/**
	 * Load settings page content.
	 *
	 * @access	public
	 * @since	1.0.0
	 * @see		$this::add_menu_item()	Where current method is used as a callback.
	 *
	 * @return void
	 */
	public function display_settings_page() {
		// Build page HTML.
		$html = '<div class="wrap" id="' . $this->page->page_id . '">' . "\n";
		$html .= '<h2>' . $this->page->page_name . '</h2>' . "\n";

		$tab = '';
		if ( $this->is_tab() ) {
			$tab .= $this->get_tab();
		}

		// Show page tabs.
		if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

			$html .= '<h2 class="nav-tab-wrapper">' . "\n";

			$c = 0;
			foreach ( $this->settings as $section => $data ) {

				// Set tab class.
				$class = 'nav-tab';
				if ( ! $this->is_tab() ) {
					if ( 0 === $c ) {
						$class .= ' nav-tab-active';
					}
				} else {
					if ( $this->is_tab() && $section === $this->get_tab() ) {
						$class .= ' nav-tab-active';
					}
				}

				// Set tab link.
				$tab_link = add_query_arg( array(
					'tab' => $section,
				) );

				if ( isset( $_GET['settings-updated'] ) ) { // WPCS: CSRF ok.
					$tab_link = remove_query_arg( 'settings-updated', $tab_link );
				}

				// Output tab.
				$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

				++$c;
			}

			$html .= '</h2>' . "\n";
		} // End if().

		$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";
		// Get settings fields.
		ob_start();
		settings_fields( $this->page->page_id );
		do_settings_sections( $this->page->page_id );
		$html .= ob_get_clean();

		$html .= '<p class="submit">' . "\n";
		$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
		$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Changes' ) ) . '" />' . "\n";
		$html .= '</p>' . "\n";
		$html .= '</form>' . "\n";
		$html .= '</div>' . "\n";

		echo $html; // WPCS: XSS OK.
	}


	/**
	 * Generate HTML for displaying fields.
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @param  array $field Field data.
	 * @return void
	 */
	public function display_field( $field ) {
		// Check for prefix on option name.
		$option = $this->get_option( $field['id'] );
		$option_name = $this->get_settings_id() . '[' . $field['id'] . ']';
		// Get saved data.
		$data = null;
		if ( isset( $option ) ) {
	    	$data = $option;
		}

	   	if ( null === $data && isset( $field['default'] ) ) {
			$data = $field['default'];
		} elseif ( false === $data ) {
			$data = '';
		}

		$disabled = isset( $field['attributes']['disabled'] ) && true === $field['attributes']['disabled'] ? 'disabled="disabled"' : '';

		$html = '';

		switch ( $field['type'] ) {

			case 'text':
			case 'url':
			case 'email':
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . esc_attr( $data ) . '" ' . $disabled . ' />' . "\n";
			break;

			case 'password':
			case 'number':
			case 'hidden':
				$min = '';
				if ( isset( $field['attributes']['min'] ) ) {
					$min = ' min="' . esc_attr( $field['attributes']['min'] ) . '"';
				}

				$max = '';
				if ( isset( $field['attributes']['max'] ) ) {
					$max = ' max="' . esc_attr( $field['attributes']['max'] ) . '"';
				}
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . esc_attr( $data ) . '"' . $min . '' . $max . ' ' . $disabled . ' />' . "\n";
			break;

			case 'text_secret':
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="" ' . $disabled . ' />' . "\n";
			break;

			case 'textarea':
				$html .= '<textarea id="' . esc_attr( $field['id'] ) . '" rows="5" cols="50" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '">' . esc_textarea( $data ) . '</textarea><br/>' . "\n";
			break;

			case 'checkbox':
				$checked = '';
				if ( $data && 'on' === $data ) {
					$checked = 'checked="checked"';
				}
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" ' . $checked . ' ' . $disabled . ' />' . "\n";
			break;

			case 'checkbox_multi':
				$html .= '<div class="checkbox-multi">';
				foreach ( $field['options'] as $k => $v ) {
					$checked = false;
					if ( is_array( $data ) && in_array( $k, $data, true ) ) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '" class="checkbox_multi"><input type="checkbox" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '[]" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" ' . $disabled . ' />' . $v . '</label> ';
				}
				$html .= '</div>';
			break;

			case 'radio':
				$html .= '<div class="radio">';
				foreach ( $field['options'] as $k => $v ) {
					$checked = false;
					if ( (string) $k === $data ) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '"><input type="radio" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" ' . $disabled . ' /> ' . $v . '</label> ';
				}
				$html .= '</div>';
			break;

			case 'select':
				$html .= '<select name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $field['id'] ) . '" ' . $disabled . '>';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( $k === $data ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
			break;

			case 'select_multi':
				$html .= '<div class="select-multi">';
				$html .= '<select name="' . esc_attr( $option_name ) . '[]" id="' . esc_attr( $field['id'] ) . '" multiple="multiple" ' . $disabled . '>';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( is_array( $data ) && in_array( $k, $data, true ) ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
				$html .= '</div>';
			break;

			case 'image':
				$image_thumb = '';
				if ( $data ) {
					$image_thumb = wp_get_attachment_thumb_url( $data );
				}
				$html .= '<img id="' . $option_name . '_preview" class="image_preview" src="' . $image_thumb . '" /><br/>' . "\n";
				$html .= '<input id="' . $option_name . '_button" type="button" data-uploader_title="' . __( 'Upload an image' , 'wordpress-plugin-template' ) . '" data-uploader_button_text="' . __( 'Use image' , 'wordpress-plugin-template' ) . '" class="image_upload_button button" value="' . __( 'Upload new image' , 'wordpress-plugin-template' ) . '" ' . $disabled . ' />' . "\n";
				$html .= '<input id="' . $option_name . '_delete" type="button" class="image_delete_button button" value="' . __( 'Remove image' , 'wordpress-plugin-template' ) . '" ' . $disabled . '/>' . "\n";
				$html .= '<input id="' . $option_name . '" class="image_data_field" type="hidden" name="' . $option_name . '" value="' . $data . '"/><br/>' . "\n";
			break;

			case 'color':
				?>
			        <input type="text" name="<?php echo esc_attr( $option_name ); ?>" class="color-picker" value="<?php echo esc_attr( $data ); ?>" data-default-color="<?php echo esc_attr( $field['default'] ); ?>" data-palettes="true" <?php echo $disabled; // WPCS: XSS ok. ?> />
			    <?php
			break;

		}// End switch().

		$disabled = ! empty( $disabled ) ? 'disabled' : '';

		switch ( $field['type'] ) {

			case 'checkbox_multi':
			case 'radio':
			case 'select_multi':
				$html .= '<span class="description ' . $disabled . '">' . $field['description'] . '</span>';
			break;

			default:
				$html .= '<label for="' . esc_attr( $field['id'] ) . '" class="' . $disabled . '">' . "\n";
				$html .= '<span class="description">' . $field['description'] . '</span>' . "\n";
				$html .= '</label>' . "\n";
			break;
		}

		$html = '<div data-setting-id="' . $field['id'] . '">' . $html . '</div>';

		echo $html; // WPCS: XSS OK.
	}

	/**
	 * Helper functions for interacting with the current admin class from the main plugin class
	 */

	/**
	 * Get the settings id based on current tab viewed
	 *
	 * The settings ID obtained is used to fetch the settings for each tab,
	 * as each tab has its own array of settings in the database
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return string	Settings ID used for storing data in the database
	 */
	public function get_settings_id() {
		if ( ! $this->settings_id ) {
			$this->settings_id = strtok( plugin_basename( ( __FILE__ ) ), '/' ); // if no settings_id is set, build one based on plugin slug.
		}

		if ( $this->is_tab() ) {
			$section = $this->get_tab(); // So we can get the original fields to compare with.
		} else {
			reset( $this->settings );
			$section = key( $this->settings ); // If there's no current tab then let's assume it's the first one.
		}

		return $this->settings_id . '-' . $section;
	}

	/**
	 * Get an array of options saved in the database for the current tab.
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @param string $option_name Optional. Name of a sub-option of the array.
	 *
	 * @return	array|string	An array of options for the current viewed tab, or specific option of the array if $option_name is used.
	 */
	public function get_option( $option_name = null ) {
		$options = get_option( $this->get_settings_id() );

		if ( ! $option_name ) {
			return $options;
		} elseif ( isset( $options[ $option_name ] ) ) {
			return $options[ $option_name ];
		} else {
			return null;
		}
	}

	/**
	 * Get current tab name.
	 *
	 * @access	public
	 * @since   1.0.0
	 *
	 * @return	string|bool		The current tab name, or false if no tab name is found.
	 */
	public function get_tab() {
		if ( isset( $_GET['tab'] ) ) { // WPCS: CSRF ok.
			return esc_attr( $_GET['tab'] ); // WPCS: CSRF ok.
		} elseif ( isset( $_POST['tab'] ) ) { // WPCS: CSRF ok.
			return esc_attr( $_POST['tab'] ); // WPCS: CSRF ok.
		} else {
			return false;
		}
	}

	/**
	 * Check if the current tab is defined
	 *
	 * @access	public
	 * @since   1.0.0
	 *
	 * @return	bool	True if a tab is defined on the current admin page, false otherwise.
	 */
	public function is_tab() {
		$tab = $this->get_tab();
		if ( ( isset( $_GET['tab'] ) || isset( $_POST['tab'] ) ) && ! empty( $tab ) ) { // WPCS: CSRF ok.
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get data for a specific field
	 *
	 * @access	public
	 * @since   1.0.0
	 *
	 * @param   string $tab_slug           The tab slug where the field is supposed to be found.
	 * @param	string $field_id           The unique ID for this field.
	 * @param	string $property_name      Optional. Property of specific field, i.e. 'description', 'default', 'label'.
	 *
	 * @return	array|string    An array with all field properties, or a string for a specific field property if $property_name is set.
	 */
	public function get_field( $tab_slug, $field_id, $property_name = null ) {
		if ( ! $tab_slug || ! $field_id ) {
			return false;
		}

		if ( is_null( $property_name ) ) {
			return $this->settings[ $tab_slug ]['fields'][ $field_id ];
		}

		return $this->settings[ $tab_slug ]['fields'][ $field_id ][ $property_name ];
	}

	/**
	 * Register an admin page.
	 *
	 * @todo	Handle the add_page process better, since currently only one page can be registred.
	 *
	 * @access	public
	 * @since   1.0.0
	 *
	 * @param   string $page_id            The page ID.
	 * @param	string $page_name          The page name.
	 * @param	string $assets_url         The URL used for enqueuing assets.
	 * @param	string $settings_id        The settings ID.
	 *
	 * @return	void
	 */
	public function add_page( $page_id, $page_name, $assets_url, $settings_id ) {
		$this->page = new stdClass();
		$this->page->page_id = $page_id;
		$this->page->assets_url = $assets_url;
		$this->page->page_name = $page_name;
		$this->settings_id = $settings_id;
	}

	/**
	 * Register a tab section for an admin page.
	 *
	 * @todo	This function will need to be updated once add_page is modified,
	 *			since we'll need the page_id to which we'll add a tab.
	 *
	 * @access	public
	 * @since   1.0.0
	 *
	 * @param   string $tab_slug           The tab unique slug.
	 * @param	string $tab_title          The tab title.
	 * @param	string $tab_description    The tab description.
	 * @param	array  $fields             The fields to add to this tab.
	 *
	 * @return	void
	 */
	public function add_tab( $tab_slug = null, $tab_title = null, $tab_description = '', $fields = null ) {
		if ( ! $tab_slug ) {
			return;
		}

		if ( ! $tab_title ) {
			$tab_title = ucfirst( $tab_slug );
		}

		$this->settings[ $tab_slug ] = array(
			'title'			=> $tab_title,
			'description'	=> $tab_description,
			'fields'		=> $fields ? $fields : array(),
		);
	}

	/**
	 * Deregister a tab section for an admin page.
	 *
	 * @access	public
	 * @since   1.0.0
	 *
	 * @param   string $tab_slug           The tab unique slug.
	 *
	 * @return	void
	 */
	public function remove_tab( $tab_slug = null ) {
		if ( ! $tab_slug ) {
			return;
		}

		unset( $this->settings[ $tab_slug ] );
	}

	/**
	 * Edit the settings of a registered tab.
	 *
	 * @access	public
	 * @since   1.0.0
	 *
	 * @param   string       $tab_slug           The tab slug.
	 * @param	string       $property_name      Name of the property to be edited.
	 * @param	string|array $property_value     The new value for the property.
	 *
	 * @return	void
	 */
	public function edit_tab( $tab_slug = null, $property_name = null, $property_value = '' ) {
		if ( ! $tab_slug || ! $property_name ) {
			return;
		}

		$this->settings[ $tab_slug ][ $property_name ] = $property_value;
	}

	/**
	 * Add a new field to the admin tab.
	 *
	 * @since   1.0.0
	 * @access  private
	 * @param   string       $tab_slug           The tab slug to which this field should be added.
	 * @param	string       $field_id           The unique ID for this field.
	 * @param	string       $type               The field type.
	 * @param	string       $label              The Field Label.
	 * @param	string       $description        The Field Description.
	 * @param	string|array $default            The default value. Must be an array if the field type needs options, such as select.
	 * @param   string       $placeholder        The text to be used as a placeholder for the field.
	 * @param	array        $options            The options for select and checkbox field types.
	 * @param	array        $attributes         Other custom attributes. Supported: min, max, disabled.
	 */
	public function add_field( $tab_slug = null, $field_id = null, $type = null, $label = null, $description = '', $default = '', $placeholder = '', $options = null, $attributes = array() ) {
		if ( ! $tab_slug || ! $field_id || ! $type ) {
			return;
		}

		$this->settings[ $tab_slug ]['fields'][ $field_id ]['id'] 			= $field_id;
		$this->settings[ $tab_slug ]['fields'][ $field_id ]['type'] 		= is_string( $type ) ? $type : 'text';
		$this->settings[ $tab_slug ]['fields'][ $field_id ]['label'] 		= $label ? $label : ucfirst( $field_id );
		$this->settings[ $tab_slug ]['fields'][ $field_id ]['description'] 	= $description;
		$this->settings[ $tab_slug ]['fields'][ $field_id ]['default'] 		= $default;
		$this->settings[ $tab_slug ]['fields'][ $field_id ]['placeholder'] 	= $placeholder;
		$this->settings[ $tab_slug ]['fields'][ $field_id ]['options'] 	    = is_array( $options ) ? $options : array( '' );
		$this->settings[ $tab_slug ]['fields'][ $field_id ]['attributes'] 	= is_array( $attributes ) ? $attributes : array();
	}

	/**
	 * Deregister a field for a specific tab.
	 *
	 * @access	public
	 * @since   1.0.0
	 *
	 * @param   string $tab_slug           The tab slug to which this field should be removed.
	 * @param	string $field_id           The unique ID for this field.
	 *
	 * @return	void
	 */
	public function remove_field( $tab_slug = null, $field_id = null ) {
		if ( ! $tab_slug || ! $field_id ) {
			return;
		}

		unset( $this->settings[ $tab_slug ]['fields'][ $field_id ] );
	}

	/**
	 * Edit a field for a specific tab.
	 *
	 * @access public
	 * @since  1.0.0
	 *
	 * @param  string $tab_slug           The tab slug to which this field should be edited.
	 * @param  string $field_id           The unique ID for this field.
	 * @param  string $property_name      The property name to edit.
	 * @param  mixed  $property_value     The value of the property to be replaced with.
	 *
	 * @return	void
	 */
	public function edit_field( $tab_slug = null, $field_id = null, $property_name = null, $property_value = '' ) {
		if ( ! $tab_slug || ! $field_id || ! $property_name ) {
			return;
		}

		$this->settings[ $tab_slug ]['fields'][ $field_id ][ $property_name ] = $property_value;
	}

	/**
	 * Add new fields in batch to the admin tab.
	 *
	 * @since  1.0.0
	 * @access private
	 *
	 * @param string $tab_slug The tab slug to which this field should be added.
	 * @param array  $fields   Array of fields to be added. @see {Admin_Manager:add_field} for a list of parameters available for each field.
	 */
	public function add_fields( $tab_slug = null, $fields = null ) {
		if ( ! $tab_slug || ! is_array( $fields ) ) {
			return;
		}

		foreach ( $fields as $field_id => $field_args ) {
			$this->add_field(
				$tab_slug,
				is_string( $field_id )              ? $field_id                  : $field_args['id'],
				isset( $field_args['type'] )        ? $field_args['type']        : null,
				isset( $field_args['label'] )       ? $field_args['label']       : null,
				isset( $field_args['description'] ) ? $field_args['description'] : '',
				isset( $field_args['default'] )     ? $field_args['default']     : '',
				isset( $field_args['placeholder'] ) ? $field_args['placeholder'] : '',
				isset( $field_args['options'] )     ? $field_args['options']     : null,
				isset( $field_args['attributes'] )  ? $field_args['attributes']  : false
			);
		}
	}
}
