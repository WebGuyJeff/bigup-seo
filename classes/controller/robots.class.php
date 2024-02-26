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
	public const ROBOTSPATH = ABSPATH . 'robots.txt';

	/**
	 * Default robots.txt contents.
	 */
	public $default_contents;

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
	 * Robots.txt exists check.
	 */
	public static function file_exists() {
		$exists = file_exists( self::ROBOTSPATH );
		return $exists;
	}


	/**
	 * Write a robots.txt file using default parameters unless they have been passed.
	 */
	public static function write_file( $contents = null ) {
		if ( null === $contents ) {
			$contents = $this->default_contents;
		}
		$robots_txt = fopen( self::ROBOTSPATH, 'w' );
		if ( ! $robots_txt ) {
			error_log( 'Unable to open ' . self::ROBOTSPATH );
			return;
		}
		fwrite( $robots_txt, $contents );
		fclose( $robots_txt );
		$created = file_exists();
		return $created;
	}


	/**
	 * Delete the robots.txt file.
	 */
	public static function delete_file( $contents = null ) {
		$deleted = unlink( self::ROBOTSPATH );
		return $deleted;
	}
}
