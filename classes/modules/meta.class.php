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

		add_action( 'template_redirect', array( $this, 'do_head_meta' ), 1, 0 );
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

		// 1. Get the current page.
		$page_index = $this->get_current_page_index();

		// 2. Check for a saved title in setting.
		$this->tags = Meta_Table::get_meta( 'page', '1493' );

		// DEBUG.
		error_log( json_encode( $this->tags ) );
		error_log( '###' );

		// 3. Apply the title filter.
	}


	/**
	 * Get the current page index (type and key).
	 */
	private function get_current_page_index() {
		$type = '';
		$key  = '';

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
			$type = 'post__' . get_post_type();
			$key  = $post->ID;
		} elseif ( is_post_type_archive() ) {
			$type = 'post_archive';
			$key  = get_query_var( 'post_type' ) );
			
		} elseif ( is_tax() ) {
			if ( is_category() ) {
				$type = 'category';
			} elseif ( is_tag() ) {
				$type = 'post_tag';
			} else {
				$type = 'custom_taxonomy';
			}
		} elseif ( is_author() ) {
			$type = 'author';
		} else {
			$type = false;
		}

		$page_index = array(
			'type' => $type,
			'key'  => $key,
		);

		return $page_index;
	}
}
