<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Sitemap
 *
 * @package bigup-seo
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2024, Jefferson Real
 * @license GPL3+
 * @link https://jeffersonreal.uk
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
	 * Populate the class properties.
	 */
	public function __construct() {
		$this->settings = get_option( self::OPTION );
		add_filter( 'init', array( $this, 'set_http_headers' ) );
	}


	/**
	 * Set http headers.
	 *
	 * Using wp_headers hook didn't seem to work in admin.
	 */
	public function set_http_headers( $headers ) {
		// Check if we're in this plugin's admin pages.
		$request_uri      = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
		$plugin_admin_uri = '/wp-admin/admin.php?page=bigup-seo-settings';
		if ( str_contains( $request_uri, $plugin_admin_uri ) ) {
			// With the CSP header we restrict iframe sources to this site only.
			$url = get_site_url();
			header( 'Content-Security-Policy: frame-src ' . $url );
		}
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
		add_filter( 'wp_sitemaps_add_provider', function ( $provider, $name ) {
				return ( $name == 'users' ) ? false : $provider;
		}, 10, 2 );
	}


	/**
	 * Remove tags taxonomy from sitemap.
	 */
	public function remove_tags() {
		add_filter( 'wp_sitemaps_taxonomies', function ( $taxonomies ) {
			unset( $taxonomies['post_tag'] );
			return $taxonomies;
		} );
	}


	/**
	 * Remove categories taxonomy from sitemap.
	 */
	public function remove_categories() {
		add_filter( 'wp_sitemaps_taxonomies', function ( $taxonomies ) {
			unset( $taxonomies['category'] );
			return $taxonomies;
		} );
	}


	/**
	 * Display an iframe of the live sitemap to see changes.
	 */
	public static function output_live_sitemap_viewer() {
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
}
