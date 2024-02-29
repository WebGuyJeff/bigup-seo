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
	 * Populate the class properties.
	 */
	public function __construct() {

		// Hook into init late, so CPTs are registered.
		add_action( 'init', array( $this, 'set_providers' ), 10, 99 );

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
				$post_types[] = $post_type->name;
			}
		}
		return $post_types;
	}


	/**
	 * Get providers.
	 */
	public function set_providers() {

		$this->providers = array(
			'taxonomies' => $this->get_taxonomies_with_terms(),
			'users'      => $this->get_users(),
			'post_types' => $this->get_post_types(),
		);

		/*
		// DEBUG.
		echo '<pre style="z-index:50;background:#fff;position:fixed;right:0;max-height:80vh;overflow-y:scroll;">';
		var_dump( $providers );
		echo '</pre>';
		*/
	}
}
