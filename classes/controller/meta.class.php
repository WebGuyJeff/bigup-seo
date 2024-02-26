<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Meta Data Handling
 *
 * @package bigup-seo
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2024, Jefferson Real
 * @license GPL3+
 * @link https://jeffersonreal.uk
 */
class Meta {

	/**
	 * Settings retrieved from the DB.
	 */
	private $settings;

	/**
	 * All crawlable web pages.
	 */
	private $crawlables;


	/**
	 * Populate the class properties.
	 */
	public function __construct() {
		$this->crawlables = $this->get_all_crawlable_pages();

	}


	/**
	 * Get all crawlable web pages.
	 *
	 * @see https://wordpress.stackexchange.com/questions/125712/list-all-pages-including-archive
	 */
	public static function get_all_crawlable_pages() {

		error_log( '##### START #####' );

		include_once ABSPATH . 'wp-includes/sitemaps/providers/class-wp-sitemaps-posts.php';
		include_once ABSPATH . 'wp-includes/sitemaps/providers/class-wp-sitemaps-taxonomies.php';
		include_once ABSPATH . 'wp-includes/sitemaps/providers/class-wp-sitemaps-users.php';
		include_once ABSPATH . 'wp-includes/sitemaps/class-wp-sitemaps-registry.php';
		include_once ABSPATH . 'wp-includes/sitemaps/class-wp-sitemaps-provider.php';
		include_once ABSPATH . 'wp-includes/sitemaps/class-wp-sitemaps-renderer.php';
		include_once ABSPATH . 'wp-includes/sitemaps/class-wp-sitemaps.php';

		if (
			! class_exists( 'WP_Sitemaps' )
			|| ! class_exists( 'WP_Sitemaps_Registry' )
			|| ! class_exists( 'WP_Sitemaps_Provider' )
			|| ! class_exists( 'WP_Sitemaps_Renderer' )
			|| ! class_exists( 'WP_Sitemaps_Posts' )
			|| ! class_exists( 'WP_Sitemaps_Taxonomies' )
			|| ! class_exists( 'WP_Sitemaps_Users' )
		) {
			error_log( 'Required class(es) not found.' );
			return false;
		}

		$sitemaps = new \WP_Sitemaps();


		$providers = $sitemaps->registry->get_providers();

		error_log( '$providers var_dumped' );
		var_dump( $providers );


		error_log( '##### END #####' );
/*
		$pages = get_pages();
		foreach ( $pages as $page ) {
			echo $page->post_title . "\n";
		}
*/


		/*
		About
		Community
		Contact
		Home
		newtest
		Partner Inbound Test
		Privacy Policy
		test-rating
		test-tiltomatic
		Vacancies

		*/
	}
}
