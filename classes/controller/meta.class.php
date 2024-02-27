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
	public function __construct() {}



	public function get_taxonomies() {
		$public_taxonomies   = get_taxonomies( array( 'public' => true ), 'objects' );
		$viewable_taxonomies = array_filter( $public_taxonomies, 'is_taxonomy_viewable' );
		return $viewable_taxonomies;
	}




	/**
	 * Get all crawlable web pages.
	 *
	 * @see https://wordpress.stackexchange.com/questions/125712/list-all-pages-including-archive
	 */
	public function get_all_crawlable_pages() {

		error_log( '##### START #####' );

/*
		$taxonomies = $this->get_taxonomies();
		error_log( '$taxonomies var_dumped' );
		var_dump( $taxonomies );

*/
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
