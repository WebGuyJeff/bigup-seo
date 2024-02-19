<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Settings Tab One.
 *
 * @package bigup-seo
 */
class Settings_Tab_One {

	public const PAGE   = 'bigupseo_page_tab_one';
	public const GROUP  = 'bigupseo_group_tab_one';
	public const OPTION = 'bigupseo_settings_general';

	public $settings;


	/**
	 * Output the content for this tab.
	 */
	public static function output_tab() {
		settings_fields( self::GROUP );
		do_settings_sections( self::PAGE );
		submit_button( 'Save' );
	}


	/**
	 * Register the settings.
	 */
	public function init() {

		$this->settings = get_option( self::OPTION );

		// A single serialsed array holds all tab settings.
		register_setting(
			self::GROUP,               // option_group.
			self::OPTION,              // option_name.
			array( $this, 'sanitise' ) // sanitise_callback.
		);

		$this->register_section_general();
	}


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
			$sanitised['generate_title_tags'] = self::sanitise_checkbox( $input['generate_title_tags'] );
		}

		return $sanitised;
	}


	/**
	 * Sanitise a checkbox.
	 */
	private static function sanitise_checkbox( $checkbox ) {
		$bool_checkbox = (bool) $checkbox;
		return $bool_checkbox;
	}
}
