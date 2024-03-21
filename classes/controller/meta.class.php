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
	 * Content providers for which WP will generate pages.
	 */
	private $providers = array();

	/**
	 * The page types we want to expose.
	 */
	private const PAGE_TYPES = array( 'home', 'blog', 'posts', 'archives' );

	/**
	 * The pages.
	 */
	private $pages = array();


	/**
	 * Hook the setup method.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'setup' ), 10, 0 );
		add_action( 'template_redirect', array( $this, 'do_head_meta' ), 1, 0 );
	}


	/**
	 * Setup SEO metadata functionality.
	 *
	 * # Step 1: Get a list of all website pages that a user may want to set SEO meta on.
	 *
	 * Pages types:
	 *
	 * - Front Page
	 * - Blog Index
	 * - Posts ( page/post/CPT)
	 * - Archives ( post, category, taxonomy, tag, user )
	 *
	 * # Step 2: Generate settings for every page for user to set metadata.
	 *
	 * # Step 3: Hook the metadata into the head of each page on load.
	 */
	public function setup() {

		$this->providers = array(
			'taxonomies' => $this->get_taxonomies_with_terms(),
			'post_types' => $this->get_post_types(),
			'users'      => $this->get_users(),
		);

		// DEBUG.
		if ( is_admin() ) {
			echo '<pre style="z-index:9999;background:#fff;position:fixed;right:0;max-height:80vh;overflow-y:scroll;padding:0.5rem;border:solid;font-size:0.7rem;">';
			var_dump( $this->providers );
			echo '</pre>';
		}

		foreach ( self::PAGE_TYPES as $type ) {

			$pages = array();
			switch ( $type ) {

				case 'home':
					break;

				case 'blog':
					break;

				case 'posts':
					break;

				case 'archives':
					break;

				default:
					error_log( "Bigup SEO: Page type {$type} not found." );
					break;
			}

			$this->pages[ $type ] = $pages;
		}

		// DEBUG.
		if ( is_admin() ) {
			echo '<pre style="z-index:9999;background:#fff;position:fixed;left:0;max-height:80vh;overflow-y:scroll;padding:0.5rem;border:solid;font-size:0.7rem;">';
			// var_dump( $this->providers );
			echo '</pre>';
		}

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
		$Head = new Head();
		add_filter( 'document_title', array( &$Head, 'get_title_tag_text' ), 1 );
		add_action( 'wp_head', array( &$Head, 'print_markup' ), 2, 0 );


		// 1. Get the current page.
		$this->get_current_page_type();

		// 2. Check for a saved title in setting.

		// 3. Apply the title filter.

	}


	/**
	 * Get viewable taxonomies.
	 */
	public function get_taxonomies() {
		$taxonomies = get_taxonomies(
			array(
				'public'             => true,
				'publicly_queryable' => true,
			),
			'names'
		);
		return $taxonomies;
	}


	/**
	 * Get non-empty taxonomy terms.
	 */
	public function get_terms( $taxonomy ) {
		$args     = array(
			'taxonomy'               => $taxonomy,
			'orderby'                => 'term_order',
			'hide_empty'             => true,
			'hierarchical'           => false,
			'update_term_meta_cache' => false,
			'fields'                 => 'all',
		);
		$wp_terms = new get_terms( $args );
		return $wp_terms;
	}


	/**
	 * Get taxonomies with terms.
	 */
	public function get_taxonomies_with_terms() {
		$taxonomies = $this->get_taxonomies();
		foreach ( $taxonomies as $taxonomy ) {
			$taxonomies[ $taxonomy ] = array( 'terms' => array() );

			$wp_terms = get_terms( $taxonomy );
			foreach ( $wp_terms as $term ) {

				$taxonomies[ $taxonomy ]['terms'][ $term->name ] = array(
					'id' => $term->term_taxonomy_id,
				);
			}

			// Exclude taxonomy if it has no terms.
			if ( empty( $taxonomies[ $taxonomy ]['terms'] ) ) {
				unset( $taxonomies[ $taxonomy ] );
			}
		}
		return $taxonomies;
	}


	/**
	 * Get users with published posts.
	 */
	public function get_users() {
		$public_post_types = get_post_types( array( 'public' => true ) );

		// We only want authors of post type 'post' and CPTs.
		unset( $public_post_types['attachment'] );
		unset( $public_post_types['page'] );

		$args     = array( 'has_published_posts' => array_keys( $public_post_types ) );
		$wp_users = get_users( $args );
		$users    = array();
		foreach ( $wp_users as $user ) {
			$users[ $user->display_name ] = array(
				'id' => $user->ID,
			);
		}
		return $users;
	}


	/**
	 * Get viewable post types.
	 */
	public function get_post_types() {
		$wp_post_types = get_post_types( array( 'public' => true ), 'objects' );
		unset( $wp_post_types['attachment'] );

		$post_types = array();
		foreach ( $wp_post_types as $post_type ) {

			// Check this type has published posts.
			$posts = get_posts(
				array(
					'post_type'   => $post_type->name,
					'post_status' => 'publish',
					'numberposts' => 1,
				)
			);
			if ( empty( $posts ) ) {
				continue;
			}

			// Filter 'viewable' post types.
			if ( $post_type->publicly_queryable || ( $post_type->_builtin && $post_type->public ) ) {
				$post_types[ $post_type->name ] = array(
					'has_archive' => $post_type->has_archive,
				);
			}
		}
		return $post_types;
	}


	/**
	 * Get the current page type.
	 */
	private function get_current_page_type() {
		if ( is_post_type_archive() ) {
			return 'post_archive';
		} elseif ( is_tax() ) {
			if ( is_category() ) {
				return 'category';
			} elseif ( is_tag() ) {
				return 'tag';
			} else {
				return 'custom-taxonomy';
			}
		} elseif ( is_author() ) {
			return 'author';
		} elseif ( is_single() ) {
			return 'post';
		} elseif ( is_page() ) {
			return 'page';
		} elseif ( is_front_page() ) {
			return 'front-page';
		} elseif ( is_home() ) {
			return 'blog-index';
		}
	}
}
