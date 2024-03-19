<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Settings Tab 2.
 *
 * @package bigup-seo
 */
class Settings_Page_Sitemap {

	public const PAGE   = 'bigupseo_page_sitemap';
	public const GROUP  = 'bigupseo_group_sitemap';
	public const OPTION = 'bigupseo_settings_sitemap';

	public $settings;


	/**
	 * Hook into WP.
	 */
	public function __construct() {
		add_action( 'admin_init', array( &$this, 'set_http_header_to_restrict_frame_source' ), 10, 0 );
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

		$this->register_section_toggles();
	}


	/**
	 * Output the content for this tab.
	 */
	public function output() {
		$this->output_tab_intro();
		settings_fields( self::GROUP );
		do_settings_sections( self::PAGE );
		submit_button( 'Save' );
		$this->output_live_sitemap_viewer();
	}


	/**
	 * Output the intro for the tab.
	 */
	public function output_tab_intro() {
		?>
			<div class="copyWidth">
				<h2>Configure a Sitemap</h2>
				<p>Here you can customise the sitemap so that only URLs to the pages you want search
					engines to index are included.</p>
				<details>
					<summary>What is a sitemap?</summary>
					<p>As Google explains: "A sitemap is a file where you provide information about the pages,
						videos, and other files on your site, and the relationships between them. Search engines
						like Google read this file to crawl your site more efficiently."</p>
					<p>By default, WordPress generates a sitemap of all generated pages. This is a great start,
						but often the default inclusions aren't appropriate for the website type.</p>
				</details>
				<details>
					<summary>How do I use it?</summary>
					<p>The generated sitemap can be accessed at
						<a target="_blank" href="/wp-sitemap.xml">/wp-sitemap.xml</a>.
						The full URL to the sitemap can be submitted to search engines and other
						services that you want to employ that have the ability to read a sitemap.
						For exmaple, you can submit your sitemap to Google Search via a Google
						Search Console account.</p>
				</details>
			</div>
		<?php
	}


	/**
	 * Register testing settings section.
	 *
	 * This calls functions to register a section and all fields within it.
	 */
	private function register_section_toggles() {
		$section = 'toggles';
		add_settings_section( $section, 'Page exclusions', array( $this, 'echo_section_intro_toggles' ), self::PAGE );

		add_settings_field( 'remove_users', 'User pages', array( &$this, 'echo_field_remove_users' ), self::PAGE, $section );
		add_settings_field( 'remove_tags', 'Tag pages', array( &$this, 'echo_field_remove_tags' ), self::PAGE, $section );
		add_settings_field( 'remove_categories', 'Category pages', array( &$this, 'echo_field_remove_categories' ), self::PAGE, $section );
	}


	/**
	 * Output toggles section intro.
	 */
	public function echo_section_intro_toggles() {
		echo '<p>Exclude default page-types from the sitemap.</p>';
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
			'Check to exclude all user pages'
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
			'Check to exclude all tag taxonomy pages'
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
			'Check to exclude all category taxonomy pages'
		);
	}


	/**
	 * Sanitise all settings in the array.
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


	/**
	 * Display an iframe of the live sitemap to see changes.
	 */
	public function output_live_sitemap_viewer() {
		?>
			<div class="sitemapViewer">
				<header>
					<div class="sitemapViewer_title">
						<h2><?php echo esc_html( __( 'Live Sitemap Viewer', 'bigup-seo' ) ); ?></h2>
					</div>
					<div class="sitemapViewer_controls">
						<input type="button" class="button" value="Back" onclick="sitemapIframe.history.back()">
					</div>
				</header>
				<iframe name="sitemapIframe" src="/wp-sitemap.xml" title="WP Sitemap"></iframe>
			</div>
		<?php
	}


	/**
	 * Restrict iframe sources to this site only.
	 * 
	 * Must be called before headers are sent.
	 */
	public function set_http_header_to_restrict_frame_source() {
		add_action( 'current_screen',
			function( $current_screen ) {
				if( str_contains( $current_screen->base, Admin_Settings::SETTINGSLUG ) ) {
					$url = get_site_url();
					header( 'Content-Security-Policy: frame-src ' . $url );
				};
			},
			10,
			1
		);
	}
}
