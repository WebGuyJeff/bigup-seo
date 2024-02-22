<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Robots
 *
 * @package bigup-seo
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2024, Jefferson Real
 * @license GPL3+
 * @link https://jeffersonreal.uk
 */
class Robots {

	/**
	 * Settings retrieved from the DB.
	 */
	private $settings;

	/**
	 * Settings key.
	 */
	private const OPTION = 'bigupseo_settings_robots';

	/**
	 * Path of robots.txt.
	 */
	private const ROBOTSPATH = ABSPATH . 'robots.txt';

	/**
	 * Default robots.txt contents.
	 */
	private $default_contents;

	/**
	 * Populate the class properties.
	 */
	public function __construct() {
		$this->settings         = get_option( self::OPTION );
		$url                    = trailingslashit( get_site_url() );
		$this->default_contents = "User-agent: *\nAllow: /\nDisallow: /wp-admin/\nDisallow: /wp-includes/\nAllow: /wp-admin/admin-ajax.php\nSitemap: " . $url . 'sitemap.xml';
	}


	/**
	 * Create robots.txt according to user specified options.
	 */
	public function apply_options() {
		$settings = $this->settings;

		if ( isset( $settings ) && false !== $settings ) {

			if ( array_key_exists( 'enable_robots', $settings ) && true === $settings['enable_robots'] ) {
				$this->ensure_robots_txt_file_exists();
			}
		}
	}


	/**
	 * Check for and write a robots.txt file if it doesn't already exist.
	 */
	public function ensure_robots_txt_file_exists() {

		if ( file_exists( self::ROBOTSPATH ) ) {
			// File exists.

		} else {
			// If file doesn't exist, create it with default contents.
			$robots_txt = fopen( self::ROBOTSPATH, 'w' );
			if ( ! $robots_txt ) {
				error_log( 'Unable to open ' . self::ROBOTSPATH );
				return;
			}
			fwrite( $robots_txt, $this->default_contents );
			fclose( $robots_txt );
		}
	}


	/**
	 * Display an iframe of the live sitemap to see changes.
	 */
	public static function output_robots_txt_editor() {
		if ( file_exists( self::ROBOTSPATH ) ) {
			$robots_contents = Util::get_contents( self::ROBOTSPATH );
			?>
				<div class="robotsTxtViewer">
					<header>
						<div class="robotsTxtViewer_title">
							<h2><?php echo esc_html( __( 'Robots.txt editor', 'bigup-seo' ) ); ?></2>
						</div>
					</header>
					<pre class="robotsTxt"><?php echo $robots_contents; ?></pre>
				</div>
			<?php
		}
	}
}
