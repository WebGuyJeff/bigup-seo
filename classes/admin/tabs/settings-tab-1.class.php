<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Settings Tab 1.
 *
 * @package bigup-seo
 */
class Settings_Tab_1 {

	public const PAGE   = 'bigupseo_page_tab_1';
	public const GROUP  = 'bigupseo_group_tab_1';
	public const OPTION = 'bigupseo_settings_general';

	public $settings;


	/**
	 * Output the content for this tab.
	 */
	public static function output_tab() {
		self::output_theme_template_title_tag_status();
		settings_fields( self::GROUP );
		do_settings_sections( self::PAGE );
		submit_button( 'Save' );
	}


	/**
	 * Register the settings.
	 */
	public function init() {

		$this->settings = get_option( self::OPTION );

		// A single serialised array holds all tab settings.
		register_setting(
			self::GROUP,               // option_group.
			self::OPTION,              // option_name.
			array( $this, 'sanitise' ) // sanitise_callback.
		);

		$this->register_section_general();
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


	// ================================================================== general settings section ====/


	/**
	 * Register general settings section.
	 *
	 * This calls functions to register a section and all fields within it.
	 */
	private function register_section_general() {
		$section = 'general_settings';
		add_settings_section( $section, 'General', array( $this, 'echo_section_intro_general' ), self::PAGE );

		add_settings_field( 'generate_title_tags', 'Generate title tags', array( &$this, 'echo_field_generate_title_tags' ), self::PAGE, $section );
	}


	/**
	 * Output general section intro.
	 */
	public function echo_section_intro_general() {
		echo '<p>General plugin settings.</p>';
	}


	/**
	 * Output generate title tags field.
	 */
	public function echo_field_generate_title_tags() {
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
	 * Sanitise all settings in an array.
	 */
	public function sanitise( $input ) {

		$sanitised = array();

		if ( isset( $input['generate_title_tags'] ) ) {
			$sanitised['generate_title_tags'] = Sanitise_Setting::checkbox( $input['generate_title_tags'] );
		}

		return $sanitised;
	}
}
