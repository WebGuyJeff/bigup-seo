<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Settings Tab 3.
 *
 * @package bigup-seo
 */
class Settings_Page_Robots {

	public const PAGE   = 'bigupseo_page_robots';
	public const GROUP  = 'bigupseo_group_robots';
	public const OPTION = 'bigupseo_settings_robots';

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

		$this->register_section_setup();
	}


	/**
	 * Output the content for this tab.
	 */
	public function output() {
		self::output_tab_intro();
		$exists = self::output_robots_txt_file_controls();

		if ( $exists ) {
			settings_fields( self::GROUP );
			do_settings_sections( self::PAGE );
			submit_button( 'Save' );
		}
	}


	/**
	 * Output the intro for the tab.
	 */
	public static function output_tab_intro() {
		?>
			<div class="copyWidth">
				<h2>Configure a robots.txt File.</h2>
				<p>Here you can publish and customise a robots.txt file.</p>
				<details>
					<summary>What is a robots.txt file?</summary>
					<p>As Google explains: "A robots.txt file tells search engine crawlers which
						URLs the crawler can access on your site. This is used mainly to avoid
						overloading your site with requests; it is not a mechanism for keeping a web
						page out of Google."</p>
					<p>By default, WordPress generates a virtual robots.txt as apposed to a physical
						file. The parameters are basic and only tell bots about the sitemap
						and how it should handle WordPress admin pages.</p>
				</details>
				<details>
					<summary>How do I use it?</summary>
					<p>The generated robots.txt file can be accessed at
						<a target="_blank" href="/robots.txt">/robots.txt</a>.
						The full URL to the robots.txt file can be submitted to search engines and
						other services that crawl your site to inform them where their crawler bots
						should crawl and where they shouldn't.</p>
				</details>
			</div>
		<?php
	}


	/**
	 * Output robots.txt exists status.
	 */
	public static function output_robots_txt_file_controls() {
		$exists = Robots::file_exists();
		?>
			<div class="copyWidth">
				<p>Status of robots.txt file: <span class="inlineStatusOutput"><?php echo $exists ? '✅ file exists' : '❌ none detected'; ?></span></p>
				<input type="button" data-action="create" class="button robotsFile" value="Create file" <?php echo $exists ? 'disabled' : ''; ?>>
				<input type="button" data-action="delete" class="button button-delete robotsFile" value="Delete file" <?php echo ( ! $exists ) ? 'disabled' : ''; ?>>
			</div>
		<?php
		return $exists;
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
		add_settings_field( 'robots_contents', 'Robots.txt contents', array( &$this, 'echo_field_robots_contents' ), self::PAGE, $section );
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
	 * Output robots.txt editor field.
	 */
	public function echo_field_robots_contents() {
		$setting   = self::OPTION . '[robots_contents]';
		$contents  = Robots::get_contents();
		printf(
			'<div class="robotsTxtViewer">' .
				'<header>' .
					'<div class="robotsTxtViewer_title">' .
						'<h2>%s</2>' .
					'</div>' .
					'<div class="robotsTxtViewer_controls">' .
						'<input type="button" class="button" value="Enable Editing" onclick="(()=>document.querySelector(\'.robotsTxt\').disabled = false)()">' .
					'</div>' .
				'</header>' .
				'<textarea id="%s" name="%s" class="robotsTxt" rows="12" disabled>%s</textarea>' .
			'</div>',
			$title = __( 'Robots.txt editor', 'bigup-seo' ),
			$setting,
			$setting,
			$contents,
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

		if ( isset( $input['robots_contents'] ) ) {
			$sanitised['robots_contents'] = Sanitise_Setting::robotstxt( $input['robots_contents'] );

			// Write the file here.
			// apply_options() TODO: Apply options at this stage, maybe not in plugin init.
			Robots::write_file( $sanitised['robots_contents'] );
		}

		return $sanitised;
	}
}
