<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Settings Tab 4.
 *
 * @package bigup-seo
 */
class Settings_Page_Developer {

	public const PAGE   = 'bigupseo_page_developer';
	public const GROUP  = 'bigupseo_group_developer';
	public const OPTION = 'bigupseo_settings_developer';

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

		// A single serialised array holds all settings.
		register_setting(
			self::GROUP,               // option_group.
			self::OPTION,              // option_name.
			array( $this, 'sanitise' ) // sanitise_callback.
		);

		$this->register_section_testing();
	}


	/**
	 * Output the content for this tab.
	 */
	public function output() {
		?>
			<form method="post" action="options.php">
				<?php
					settings_fields( self::GROUP );
					do_settings_sections( self::PAGE );
					submit_button( 'Save' );
				?>
			</form>
		<?php
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
	 * Output meta viewer field.
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
	 * Sanitise all settings in the array.
	 */
	public function sanitise( $input ) {

		$sanitised = array();

		if ( isset( $input['output_meta'] ) ) {
			$sanitised['output_meta'] = Sanitise_Setting::checkbox( $input['output_meta'] );
		}

		return $sanitised;
	}
}
