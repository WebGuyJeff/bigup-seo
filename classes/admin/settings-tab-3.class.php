<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Settings Tab 3.
 *
 * @package bigup-seo
 */
class Settings_Tab_3 {

	public const PAGE   = 'bigupseo_page_tab_3';
	public const GROUP  = 'bigupseo_group_tab_3';
	public const OPTION = 'bigupseo_settings_robots';

	public $settings;


	/**
	 * Output the content for this tab.
	 */
	public static function output_tab() {
		settings_fields( self::GROUP );
		do_settings_sections( self::PAGE );
		submit_button( 'Save' );
		Robots::output_robots_txt_editor();
	}


	/**
	 * Register the settings.
	 */
	public function init() {

		$this->settings = get_option( self::OPTION );

		// A single serialised array holds all settings.
		register_setting(
			self::GROUP,               // option_group.
			self::OPTION,              // option_name.
			array( $this, 'sanitise' ) // sanitise_callback.
		);

		$this->register_section_setup();
	}


	/**
	 * Register testing settings section.
	 *
	 * This calls functions to register a section and all fields within it.
	 */
	private function register_section_setup() {
		$section = 'setup';
		add_settings_section( $section, 'Basic robots.txt setup', array( $this, 'echo_section_setup' ), self::PAGE );

		add_settings_field( 'enable_robots', 'Create a robots.txt file', array( &$this, 'echo_field_enable_robots' ), self::PAGE, $section );
	}


	/**
	 * Output toggles section intro.
	 */
	public function echo_section_setup() {
		echo '<p>Setup the basic requirements of the robots.txt file.</p>';
	}


	/**
	 * Output enable robots field.
	 */
	public function echo_field_enable_robots() {
		$setting = self::OPTION . '[enable_robots]';
		printf(
			'<input type="checkbox" value="1" id="%s" name="%s" %s><label for="%s">%s</label>',
			$setting,
			$setting,
			isset( $this->settings['enable_robots'] ) ? checked( '1', $this->settings['enable_robots'], false ) : '',
			$setting,
			'Check to create a robots.txt file'
		);
	}


	/**
	 * Sanitise all settings in an array.
	 */
	public function sanitise( $input ) {

		$sanitised = array();

		if ( isset( $input['enable_robots'] ) ) {
			$sanitised['enable_robots'] = Sanitise_Setting::checkbox( $input['enable_robots'] );
		}

		return $sanitised;
	}
}
