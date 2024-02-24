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
		self::output_tab_intro();
		self::output_robots_txt_exists_status();

		// if ( file_exists( Robots::ROBOTSPATH ) ) {
			settings_fields( self::GROUP );
			do_settings_sections( self::PAGE );
			submit_button( 'Save' );
		// }
	}


	/**
	 * Register the settings.
	 */
	public function init() {

		$this->settings = get_option( self::OPTION );
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
	public static function output_robots_txt_exists_status() {
		echo '<div class="copyWidth">';
		$exists = Robots::robots_txt_exists();
		echo '<p>Status of robots.txt file: <span class="inlineStatusOutput">' . ( $exists ? '✅ file exists' : '❌ none detected' ) . '</span></p>';
		echo '</div>';
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
		$robots_contents = '';
		$settings        = get_option( self::OPTION );
		$url             = trailingslashit( get_site_url() );

		// Contents from database.
		if ( $settings && isset( $settings['robots_contents'] ) && 0 !== strlen( $settings['robots_contents'] ) ) {
			$file_contents = $settings['robots_contents'];

		// Contents from existing file.
		} elseif ( Robots::robots_txt_exists() ) {
			$file_contents = Util::get_contents( Robots::ROBOTSPATH );

		// Contents from virtual robots.txt.
		} elseif ( is_string( Util::get_contents( $url . 'robots.txt' ) ) && 0 !== strlen( Util::get_contents( $url . 'robots.txt' ) ) ) {
			$file_contents = Util::get_contents( $url . 'robots.txt' );

		// Contents from Robots class fallback.
		} else {
			$Robots        = new Robots();
			$file_contents = $Robots->default_contents;
		}

		$setting = self::OPTION . '[robots_contents]';
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
			$file_contents,
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
			Robots::write_robots_txt( $sanitised['robots_contents'] );
		}

		return $sanitised;
	}
}
