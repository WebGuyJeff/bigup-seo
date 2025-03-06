<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Sitemap
 *
 * @package bigup-seo
 * @author Jefferson Real <jeff@webguyjeff.com>
 * @copyright Copyright (c) 2024, Jefferson Real
 * @license GPL3+
 * @link https://webguyjeff.com
 */
class Sitemap {

	/**
	 * Settings retrieved from the DB.
	 */
	private $settings;

	/**
	 * Settings key.
	 */
	private const OPTION = 'bigupseo_settings_sitemap';

	/**
	 * Relative path of the sitemap.
	 */
	public const SITEMAPURI = 'wp-sitemap.xml';


	/**
	 * Populate the class properties.
	 */
	public function __construct() {
		$this->settings = get_option( self::OPTION );
	}


	/**
	 * Get sitemap path.
	 */
	public static function get_url() {
		$url = trailingslashit( get_site_url() );
		return $url . self::SITEMAPURI;
	}


	/**
	 * Modify the sitemap according to user specified options.
	 */
	public function apply_options() {
		$settings = $this->settings;

		if ( isset( $settings ) && false !== $settings ) {

			if ( array_key_exists( 'remove_users', $settings ) && true === $settings['remove_users'] ) {
				$this->remove_users();
			}

			if ( array_key_exists( 'remove_tags', $settings ) && true === $settings['remove_tags'] ) {
				$this->remove_tags();
			}

			if ( array_key_exists( 'remove_categories', $settings ) && true === $settings['remove_categories'] ) {
				$this->remove_categories();
			}
		}
	}


	/**
	 * Remove users (aka authors) from sitemap.
	 */
	public function remove_users() {
		add_filter(
			'wp_sitemaps_add_provider',
			function ( $provider, $name ) {
				return ( $name == 'users' ) ? false : $provider;
			},
			10,
			2
		);
	}


	/**
	 * Remove tags taxonomy from sitemap.
	 */
	public function remove_tags() {
		add_filter(
			'wp_sitemaps_taxonomies',
			function ( $taxonomies ) {
				unset( $taxonomies['post_tag'] );
				return $taxonomies;
			},
			10,
			1
		);
	}


	/**
	 * Remove categories taxonomy from sitemap.
	 */
	public function remove_categories() {
		add_filter(
			'wp_sitemaps_taxonomies',
			function ( $taxonomies ) {
				unset( $taxonomies['category'] );
				return $taxonomies;
			},
			10,
			1
		);
	}
}
