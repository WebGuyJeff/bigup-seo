<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Settings Tab Two.
 *
 * @package bigup-seo
 */
class Settings_Tab_Two {

	public const PAGE   = 'bigupseo_page_tab_two';
	public const GROUP  = 'bigupseo_group_tab_two';
	public const OPTION = 'bigupseo_settings_developer';

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

		// A single serialsed array holds all plugin settings.
		register_setting(
			self::GROUP,               // option_group.
			self::OPTION,              // option_name.
			array( $this, 'sanitise' ) // sanitise_callback.
		);

		$this->register_section_testing();
	}


	/**
	 * Register testing settings section.
	 *
	 * This calls functions to register a section and all fields within it.
	 */
	private function register_section_testing() {
		$section = 'testing';
		add_settings_section( $section, 'Testing', array( $this, 'echo_section_intro_testing' ), self::PAGE );

		add_settings_field( 'output_meta', 'Output meta', array( &$this, 'echo_field_output_meta' ), self::PAGE, $section );
	}


	/**
	 * Output testing section intro.
	 */
	public function echo_section_intro_testing() {
		echo '<p>Options for site-builders and developers to test SEO generation.</p>';
	}


	/**
	 * Output generate title tags field.
	 */
	public function echo_field_output_meta() {
		$setting = self::OPTION . '[output_meta]';
		printf(
			'<input type="checkbox" value="1" id="%s" name="%s" %s><label for="%s">%s</label>',
			$setting,
			$setting,
			isset( $this->settings['output_meta'] ) ? checked( '1', $this->settings['output_meta'], false ) : '',
			$setting,
			'Display meta viewer on the front-end website. Warning: all logged-in admin users will see this!'
		);
	}


	/**
	 * Sanitise all settings in an array.
	 */
	public function sanitise( $input ) {

		$sanitised = array();

		if ( isset( $input['output_meta'] ) ) {
			$sanitised['output_meta'] = self::sanitise_checkbox( $input['output_meta'] );
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
