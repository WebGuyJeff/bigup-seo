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
		$this->get_current_page_type();

		// 2. Check for a saved title in setting.

		// 3. Apply the title filter.
	}


	/**
	 * Get the current page type.
	 */
	private function get_current_page_type() {
		if ( is_front_page() ) {
			return 'front_page';
		} elseif ( is_home() ) {
			return 'blog_index';
		} elseif ( is_page() ) {
			return 'page';
		} elseif ( is_single() ) {
			return 'post__' . get_post_type();
		} elseif ( is_post_type_archive() ) {
			return 'post_archive';
		} elseif ( is_tax() ) {
			if ( is_category() ) {
				return 'category';
			} elseif ( is_tag() ) {
				return 'post_tag';
			} else {
				return 'custom_taxonomy';
			}
		} elseif ( is_author() ) {
			return 'author';
		} else {
			return false;
		}
	}
}
