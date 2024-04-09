<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Settings Tab 1.
 *
 * @package bigup-seo
 */
class Settings_Page_Meta {

	public const PAGE   = 'bigupseo_page_meta';
	public const GROUP  = 'bigupseo_group_meta';
	public const OPTION = 'bigupseo_settings_meta';

	public $settings;


	/**
	 * Hook into WP.
	 */
	public function __construct() {
		add_action( 'admin_init', array( &$this, 'register' ), 10, 0 );
	}


	/**
	 * Register the settings.
	 */
	public function register() {

		$this->settings = get_option( self::OPTION );

		// A single serialised array holds all tab settings.
		register_setting(
			self::GROUP,               // option_group.
			self::OPTION,              // option_name.
			array( $this, 'sanitise' ) // sanitise_callback.
		);

		$this->register_section_meta();
	}


	/**
	 * Output the content for this tab.
	 */
	public function output() {
		self::output_theme_template_title_tag_status();
		settings_fields( self::GROUP );
		do_settings_sections( self::PAGE );
		submit_button( 'Save' );
	}


	/**
	 * Theme template title tag check.
	 *
	 * Output a warning if the title tag is detected in theme file markup.
	 */
	public static function output_theme_template_title_tag_status() {
		echo '<div class="copyWidth">';
		$files = Util::theme_files_contain( '<title' );
		if ( $files ) {
			echo '<p>Warning! Your current theme may have the &lt;title&gt; meta tag hard-coded in the following template files:</p>';
			echo '<ul style="list-style: disc; margin-left: 2em;">';
			foreach ( $files as $file ) {
				echo '<li>' . esc_url( $file ) . '</li>';
			}
			echo '</ul>';
			echo '<p>This may cause duplicated tags in your markup, harming your SEO. Please review your theme templates to ensure these tags are not present allowing WordPress to handle their generation instead.</p>';
		} else {
			echo "<p>Great! Your current theme doesn't have the <code>&lt;title&gt;</code> meta tag hard-coded in it's template markup. This plugin can safely manage that for you.</p>";
		}
		$theme_support = get_theme_support( 'title-tag' );
		echo '<p>Title tag theme support status: <span class="inlineStatusOutput">' . ( $theme_support ? '✅ enabled' : '❌ disabled' ) . '</span></p>';
		echo '</div>';
	}


	/**
	 * Register meta settings section.
	 *
	 * This calls functions to register a section and all fields within it.
	 */
	private function register_section_meta() {
		$section = 'meta_settings';
		add_settings_section( $section, 'Meta', array( $this, 'echo_section_intro_meta' ), self::PAGE );

		add_settings_field( 'generate_title_tags', 'Generate title tags', array( &$this, 'echo_field_enable_title_tags' ), self::PAGE, $section );
	}


	/**
	 * Output meta section intro.
	 */
	public function echo_section_intro_meta() {
		echo '<p>Meta settings.</p>';
	}


	/**
	 * Output generate title tags field.
	 */
	public function echo_field_enable_title_tags() {
		$setting = self::OPTION . '[generate_title_tags]';
		printf(
			'<input type="checkbox" value="1" id="%s" name="%s" %s><label for="%s">%s</label>',
			$setting,
			$setting,
			isset( $this->settings['generate_title_tags'] ) ? checked( '1', $this->settings['generate_title_tags'], false ) : '',
			$setting,
			'Enable generation of page meta title tags.'
		);
	}



	/**
	 * Generate and output metadata fields.
	 *
	 * All fields are saved in a single sub-array of this page option.
	 */
	private function echo_fields_page_meta() {
		$providers = Meta::$providers;
		if ( empty( $providers ) ) {
			return;
		}

		foreach ( Meta::PAGE_TYPES as $type ) {

			// FINISH THIS FUNCTION.

			// Restructure the array data to allow simpler creation of fields.
			// Recreating the switch for every process is going to be messy.
			// Each field should be able to be created using the same function.

			$pages = array();
			switch ( $type ) {

				case 'front_page':
					$pages['id'] = get_option( 'page_on_front' );
					break;

				case 'blog_index':
					$pages['id'] = get_option( 'page_for_posts' );
					break;

				case 'page':
					$all_pages    = get_pages();
					$pages['ids'] = wp_list_pluck( $all_pages, 'ID' );
					break;

				case 'post':
					$post_types = array_keys( $providers['post_types'] );
					foreach ( $post_types as $post_type ) {
						if ( 'page' === $post_type ) {
							continue;
						}
						$args                       = array(
							'post_type' => $post_type,
							'fields'    => 'ids',
						);
						$pages[ $post_type ]        = array();
						$pages[ $post_type ]['ids'] = get_posts( $args );
					}
					break;

				case 'post_archive':
					foreach ( $providers['post_types'] as $post_type ) {
						if ( false !== $post_type['has_archive'] ) {
							$pages[] = $post_type['has_archive'];
						}
					}
					break;

				case 'category':
					if ( isset( $providers['taxonomies']['category'] ) ) {
						$pages = $providers['taxonomies']['category'];
					}
					break;

				case 'tag':
					if ( isset( $providers['taxonomies']['tag'] ) ) {
						$pages = $providers['taxonomies']['tag'];
					}
					break;

				case 'custom_taxonomy':
					$remove            = array( 'category', 'tag' );
					$custom_taxonomies = array_diff_key( $providers['taxonomies'], array_flip( $remove ) );
					if ( ! empty( $custom_taxonomies ) ) {
						$pages = $custom_taxonomies;
					}
					break;

				case 'author':
					$pages = $providers['users'];
					break;

				default:
					error_log( "Bigup SEO: Page type {$type} not found." );
					break;
			}

			$site_pages[ $type ] = $pages;
		}

		return $site_pages;
	}




	/**
	 * Output page title tag field.
	 */
	public function echo_field_page_title_tag( $key ) {
		$setting = self::OPTION . '[pages][ $key ]';
		printf(
			'<input type="text" value="%s" id="%s" name="%s" maxlength="70" />',
			$this->settings['generate_title_tags'][ pages ][ $key ],
			$setting,
			$setting,
		);
	}



	/**
	 * Sanitise all settings in the array.
	 */
	public function sanitise( $input ) {

		$sanitised = array();

		if ( isset( $input['generate_title_tags'] ) ) {
			$sanitised['generate_title_tags'] = Sanitise_Setting::checkbox( $input['generate_title_tags'] );
		}

		return $sanitised;
	}
}
