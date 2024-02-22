<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Settings Tab 2.
 *
 * @package bigup-seo
 */
class Settings_Tab_2 {

	public const PAGE   = 'bigupseo_page_tab_2';
	public const GROUP  = 'bigupseo_group_tab_2';
	public const OPTION = 'bigupseo_settings_sitemap';

	public $settings;


	/**
	 * Output the content for this tab.
	 */
	public static function output_tab() {
		settings_fields( self::GROUP );
		do_settings_sections( self::PAGE );
		submit_button( 'Save' );
		Sitemap::output_live_sitemap_viewer();
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

		$this->register_section_toggles();
	}


	/**
	 * Register testing settings section.
	 *
	 * This calls functions to register a section and all fields within it.
	 */
	private function register_section_toggles() {
		$section = 'toggles';
		add_settings_section( $section, 'Page-type Toggles', array( $this, 'echo_section_intro_toggles' ), self::PAGE );

		add_settings_field( 'remove_users', 'Remove all user pages', array( &$this, 'echo_field_remove_users' ), self::PAGE, $section );
		add_settings_field( 'remove_tags', 'Remove all tag taxonomy pages', array( &$this, 'echo_field_remove_tags' ), self::PAGE, $section );
		add_settings_field( 'remove_categories', 'Remove all category taxonomy pages', array( &$this, 'echo_field_remove_categories' ), self::PAGE, $section );
	}


	/**
	 * Output toggles section intro.
	 */
	public function echo_section_intro_toggles() {
		echo '<p>Enable/disable parts of the sitemap.</p>';
	}


	/**
	 * Output remove users field.
	 */
	public function echo_field_remove_users() {
		$setting = self::OPTION . '[remove_users]';
		printf(
			'<input type="checkbox" value="1" id="%s" name="%s" %s><label for="%s">%s</label>',
			$setting,
			$setting,
			isset( $this->settings['remove_users'] ) ? checked( '1', $this->settings['remove_users'], false ) : '',
			$setting,
			'Check to remove all user pages from the sitemap'
		);
	}


	/**
	 * Output remove tags field.
	 */
	public function echo_field_remove_tags() {
		$setting = self::OPTION . '[remove_tags]';
		printf(
			'<input type="checkbox" value="1" id="%s" name="%s" %s><label for="%s">%s</label>',
			$setting,
			$setting,
			isset( $this->settings['remove_tags'] ) ? checked( '1', $this->settings['remove_tags'], false ) : '',
			$setting,
			'Check to remove all tag taxonomy pages from the sitemap'
		);
	}


	/**
	 * Output remove categories field.
	 */
	public function echo_field_remove_categories() {
		$setting = self::OPTION . '[remove_categories]';
		printf(
			'<input type="checkbox" value="1" id="%s" name="%s" %s><label for="%s">%s</label>',
			$setting,
			$setting,
			isset( $this->settings['remove_categories'] ) ? checked( '1', $this->settings['remove_categories'], false ) : '',
			$setting,
			'Check to remove all tag category pages from the sitemap'
		);
	}


	/**
	 * Sanitise all settings in an array.
	 */
	public function sanitise( $input ) {

		$sanitised = array();

		if ( isset( $input['remove_users'] ) ) {
			$sanitised['remove_users'] = Sanitise_Setting::checkbox( $input['remove_users'] );
		}

		if ( isset( $input['remove_tags'] ) ) {
			$sanitised['remove_tags'] = Sanitise_Setting::checkbox( $input['remove_tags'] );
		}

		if ( isset( $input['remove_categories'] ) ) {
			$sanitised['remove_categories'] = Sanitise_Setting::checkbox( $input['remove_categories'] );
		}

		return $sanitised;
	}
}
