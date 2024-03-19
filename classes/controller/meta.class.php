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
	 * The content types for which WP will generate pages.
	 */
	private $providers = array();


	/**
	 * Hook the setup method.
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'setup' ), 10, 0 );
	}


	/**
	 * Setup SEO metadata functionality.
	 */
	public function setup() {

		/*
			# Goal 1: Get a list of all website pages that a user may want to set SEO meta on.

			Pages types:

			- Archive Pages
				- Post
				- Category
				- Taxonomy
				- Tag
			- Post pages ( page/post/CPT)
			- User Pages
			- Front Page
			- Blog Index

			# Goal 2: Generate settings for every page where user can set metadata.

			# Goal 3: Hook the metadata into the head of each page on load.
		*/

		$this->providers = $this->get_providers();

		// DEBUG.
		if ( is_admin() ) {
			echo '<pre style="z-index:9999;background:#fff;position:fixed;right:0;max-height:80vh;overflow-y:scroll;padding:0.5rem;border:solid;font-size:0.7rem;">';
			var_dump( $this->providers );
			echo '</pre>';
		}
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
	 * Get providers.
	 */
	public function get_providers() {
		return array(
			'taxonomies' => $this->get_taxonomies_with_terms(),
			'users'      => $this->get_users(),
			'post_types' => $this->get_post_types(),
		);
	}
}
