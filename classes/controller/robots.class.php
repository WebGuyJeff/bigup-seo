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
	 * Default robots.txt contents.
	 */
	public static $default_contents = "User-agent: *\nAllow: /\nDisallow: /wp-admin/\nDisallow: /wp-includes/\nAllow: /wp-admin/admin-ajax.php\n";

	/**
	 * Relative path of robots.txt.
	 */
	public const ROBOTSRELPATH = 'robots.txt';


	/**
	 * Populate the class properties.
	 */
	public function __construct() {
		$this->settings = get_option( self::OPTION );
	}


	/**
	 * Create robots.txt according to user specified options.
	 */
	public function apply_options() {
		$settings = $this->settings;

		if ( isset( $settings ) && false !== $settings ) {

			if ( array_key_exists( 'enable_robots', $settings ) && true === $settings['enable_robots'] ) {


				/* Convert this setting to enable sitemap in robots.txt */
				$url     = trailingslashit( get_site_url() );
				$sitemap = 'Sitemap: ' . $url . 'sitemap.xml';



				if ( ! self::file_exists() ) {
					self::write_file();
				}
			}
		}
	}


	/**
	 * Get robots.txt path.
	 */
	public static function get_path() {
		return ABSPATH . self::ROBOTSRELPATH;
	}


	/**
	 * Get robots.txt contents.
	 */
	public static function get_contents() {
		$settings = get_option( 'bigupseo_settings_robots' );
		$url      = trailingslashit( get_site_url() );
		$contents = '';

		// From database.
		if ( $settings && isset( $settings['robots_contents'] ) && 0 !== strlen( $settings['robots_contents'] ) ) {
			$contents = $settings['robots_contents'];

		// From existing file.
		} elseif ( self::file_exists() ) {
			$contents = Util::get_contents( self::get_path() );

		// From virtual robots.txt.
		} elseif ( is_string( Util::get_contents( $url . 'robots.txt' ) ) && 0 !== strlen( Util::get_contents( $url . 'robots.txt' ) ) ) {
			$contents = Util::get_contents( $url . 'robots.txt' );

		// From default fallback.
		} else {
			$contents = self::default_contents . Sitemap::get_url();
		}

		return $contents;
	}


	/**
	 * Robots.txt exists check.
	 */
	public static function file_exists() {
		$exists = file_exists( self::get_path() );
		return $exists;
	}


	/**
	 * Write a robots.txt file using default parameters unless they have been passed.
	 */
	public static function write_file( $contents = null ) {
		if ( null === $contents ) {
			$contents = self::get_contents();
		}
		$robots_txt = fopen( self::get_path(), 'w' );
		if ( ! $robots_txt ) {
			error_log( 'Unable to open ' . self::get_path() );
			return;
		}
		fwrite( $robots_txt, $contents );
		fclose( $robots_txt );
		$created = self::file_exists();
		return $created;
	}


	/**
	 * Delete the robots.txt file.
	 */
	public static function delete_file( $contents = null ) {
		$deleted = unlink( self::get_path() );
		return $deleted;
	}
}
