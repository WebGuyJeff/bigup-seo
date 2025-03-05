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
	private array $settings;

	/**
	 * Meta data retrieved from the DB.
	 */
	private object|null $db_meta;

	/**
	 * Class to generate head markup.
	 */
	private Head $head;

	/**
	 * Hook the setup method.
	 */
	public function __construct() {
		$settings  = get_option( Settings_Page_Meta::OPTION );

		if ( isset( $settings ) && is_array( $settings ) ) {
			$this->settings = $settings;
		} else {
			$this->settings = array();
		}

		add_action( 'template_redirect', array( $this, 'do_all_tags' ), 10, 0 );
	}


	/**
	 * Hook into wp_head to add meta and modify title tags.
	 *
	 * Head must be instantiated after the wp query and before the 'wp_head' hook.
	 *
	 * Removing theme support 'title-tag' and 'wp_head' title action in order to implement our own
	 * was unreliable, so we're filtering the WP core document_title_parts instead.
	 */
	public function do_all_tags() {

		// 1. Get the current page type/key index.
		[ $type, $key ] = $this->get_current_page_index();

		// 2. Get metadata from DB if any is saved.
		$this->db_meta = Meta_Table::get_row( $type, $key );
		if ( $this->db_meta === null ) {
			return;
		}

		// Hook the title tag to override WP (Do not use 'wp_title' hook!).
		add_filter( 'document_title_parts', array( &$this, 'filter_title' ), 10, 1 );

		// Hook into wp_head to stop WP generating a canonical link tag.
		remove_action( 'wp_head', 'rel_canonical' );

		// Hook into wp_head and do all other meta tags.
		$this->head = new Head( $this->db_meta );
		add_action( 'wp_head', array( &$this, 'print_meta_markup' ), 2, 0 );
	}


	/**
	 * Filter Meta Title.
	 *
	 * @param array $title_parts The meta title parts.
	 */
	public function filter_title( $title_parts ) {

		// TODO: Add option to prepend/append site title and separator.

		if ( $this->db_meta->meta_title ) {
			$title_parts['title'] = $this->db_meta->meta_title;

			// We want complete control so we empty the other parts.
			$title_parts['tagline'] = '';
			$title_parts['site']    = '';
		}
		return $title_parts;
	}


	/**
	 * Print meta markup.
	 */
	public function print_meta_markup() {
		Escape::head( $this->head->markup );
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
