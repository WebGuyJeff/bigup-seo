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
	 * Meta data retrieved from the DB.
	 */
	private $tags;

	/**
	 * Hook the setup method.
	 */
	public function __construct() {

		$this->settings = get_option( Settings_Page_Meta::OPTION );

		add_action( 'template_redirect', array( $this, 'do_head_meta' ), 10, 0 );
	}


	/**
	 * Hook into wp_head to add meta and modify title tags.
	 *
	 * Head must be instantiated after the wp query and before the 'wp_head' hook.
	 *
	 * Removing theme support 'title-tag' and 'wp_head' title action in order to implement our own
	 * was unreliable, so we're filtering the WP core document_title instead.
	 */
	public function do_head_meta() {

		// ORIGINAL FUNCTIONALITY.

		$Head = new Head();
		add_filter( 'document_title', array( &$Head, 'get_title_tag_text' ), 1 );
		add_action( 'wp_head', array( &$Head, 'print_markup' ), 2, 0 );

		// NEW TITLE FUNCTIONALITY.

		// DEBUG.
		error_log( '###' );

		// 1. Get the current page.
		[ $type, $key ] = $this->get_current_page_index();

		error_log( '$type: ' . $type );
		error_log( '$key: ' . $key );


		$this->tags = Meta_Table::get_meta( $type, $key );

		error_log( json_encode( $this->tags ) );

		// 3. Apply the title filter.
	}


	/**
	 * Get the current page index (type and key).
	 */
	private function get_current_page_index() {
		$type = null;
		$key  = null;

		if ( is_front_page() ) {
			$type = 'site_index';
			$key  = 'home';
		} elseif ( is_home() ) {
			$type = 'site_index';
			$key  = 'blog_index';
		} elseif ( is_page() ) {
			$type = 'page';
			$key  = get_queried_object_id();
		} elseif ( is_single() ) {
			global $post;
			$type = 'post__' . $post->post_type;
			$key  = $post->ID;
		} elseif ( is_post_type_archive() ) {
			$type = 'post_archive';
			$key  = get_query_var( 'post_type' );
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$obj  = get_queried_object();
			$type = 'tax__' . $obj->taxonomy;
			$key  = $obj->term_id;
		} elseif ( is_author() ) {
			$type = 'author';
			$key  = get_the_author_meta( 'ID' );
		}
		$page_index = $type && $key ? array( $type, $key ) : false;

		return $page_index;
	}
}
