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

	private $settings;

	/**
	 * The pages.
	 */
	private $pages = array();

	/**
	 * Hook into WP.
	 */
	public function __construct() {

		if ( empty( $this->pages ) ) {
			$this->pages = new Pages();
		}
		add_action( 'admin_init', array( &$this, 'register' ), 11, 0 ); // Priority lower than Pages().
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

		// Temp solution to test output. Will need to hook into DB table.
		$this->echo_fields_page_meta();

		submit_button( 'Save' );

		// DEBUG.
		$this->debug();
	}

	// DEBUG
	public function debug() {
		if ( is_admin() ) {
			$debug = '';
			// $debug .= '<pre style="z-index:9999;background:#fff;position:fixed;top:20px;left:0;max-height:80vh;max-width:50%;overflow:scroll;padding:0.5rem;border:solid;font-size:0.7rem;">';
			// $debug .= print_r( $this->pages->providers, true );
			// $debug .= '</pre>';
			$debug .= '<pre style="z-index:9999;background:#fff;position:fixed;top:20px;right:0;max-height:80vh;max-width:50%;overflow:scroll;padding:0.5rem;border:solid;font-size:0.7rem;">';
			$debug .= print_r( $this->pages->map, true );
			$debug .= '</pre>';

			echo $debug;
		}
	}
	// DEBUG

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
	 * Register meta settings section and fields.
	 */
	private function register_section_meta() {
		$section = 'meta_settings';
		add_settings_section( $section, 'Meta', array( $this, 'echo_section_intro_meta' ), self::PAGE );

		add_settings_field( 'generate_title_tags', 'Generate title tags', array( &$this, 'echo_field_enable_plugin_title_tags' ), self::PAGE, $section );
	}


	/**
	 * Output meta section intro.
	 */
	public function echo_section_intro_meta() {
		echo '<p>Meta settings.</p>';
	}


	/**
	 * Output option to enable plugin title tags.
	 */
	public function echo_field_enable_plugin_title_tags() {
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
	 * Generate settings fields for every page we want to set metadata for.
	 */
	private function echo_fields_page_meta() {

		foreach ( $this->pages->map as $type => $data ) {

			// Decode prefixes for post and tax types.
			$sub_type = '';
			if ( preg_match( '/post__.*/', $type ) ) {
				$sub_type = str_replace( 'post__', '', $type );
				$type     = 'post';
			} elseif ( preg_match( '/tax__.*/', $type ) ) {
				$sub_type = str_replace( 'tax__', '', $type );
				$type     = 'tax';
			}

			$this->echo_inline_title( $data['label'] );

			switch ( $type ) {

				case 'front_page':
				case 'blog_index':
					$this->echo_field_page_title_tag(
						array(
							'field_id' => $type,
							'label'    => $data['label'],
						)
					);
					break;

				case 'page':
				case 'post':
				case 'post_archive':
				case 'author':
					foreach ( $data['pages'] as $key => $page ) {
						$this->echo_field_page_title_tag(
							array(
								'field_id' => $key,
								'label'    => $page['name'],
							)
						);
					}
					break;

				default:
					error_log( "Bigup SEO: Page type {$type} not found." );
					break;
			}
		}
	}


	/**
	 * Output inline title.
	 */
	public function echo_inline_title( $title ) {
		printf(
			'<h4>%s</h4>',
			$title
		);
	}


	/**
	 * Output page title tag field.
	 */
	public function echo_field_page_title_tag( $page ) {
		printf(
			'<label>%s<br><input class="regular-text" type="text" value="%s" id="%s" name="%s" maxlength="70" /></label><br>',
			$page['label'],
			$page['label'],
			$page['field_id'],
			$page['field_id'],
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
