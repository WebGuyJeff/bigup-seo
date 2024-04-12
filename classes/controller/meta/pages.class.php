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
class Pages {

	/**
	 * Settings retrieved from the DB.
	 */
	private $settings;

	/**
	 * Content providers for which WP will generate pages.
	 */
	public $providers = array();

	/**
	 * The page types we want to expose.
	 */
	private const TYPES = array(
		'front_page',
		'blog_index',
		'page',
		'post',
		'post_archive',
		'category',
		'tag',
		'custom_taxonomy',
		'author',
	);

	/**
	 * A map of generated pages.
	 *
	 * This will be empty until the 'init' hook.
	 */
	public $map = array();


	/**
	 * Hook and setup the page data.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'setup' ), 10, 0 );
	}


	/**
	 * Setup SEO metadata functionality.
	 */
	public function setup() {

		/**
		 * 1. Get all providers of wordpress generated pages.
		 *
		 * $providers are the sources of all WP generated post and archive pages.
		 */
		$this->providers = array(
			'taxonomies' => $this->get_taxonomies_with_terms(),
			'post_types' => $this->get_post_types(),
			'users'      => $this->get_users(),
		);

		/**
		 * 2: Compile a list of all pages that we want to set SEO meta on.
		 *
		 * $map is an index of all WP generated pages structured for generating input options
		 * fields and storing live data for database manipulation.
		 */
		$this->map = $this->get_page_map( $this->providers );
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
		$args  = array(
			'taxonomy'               => $taxonomy,
			'orderby'                => 'term_order',
			'hide_empty'             => true,
			'hierarchical'           => false,
			'update_term_meta_cache' => false,
			'fields'                 => 'id=>name',
		);
		$terms = new get_terms( $args );
		return $terms;
	}


	/**
	 * Get taxonomies with terms.
	 */
	public function get_taxonomies_with_terms() {
		foreach ( $this->get_taxonomies() as $taxonomy ) {
			$taxonomies[ $taxonomy ] = array();

			$terms = get_terms( $taxonomy );
			foreach ( $terms as $term ) {

				$taxonomies[ $taxonomy ][] = array(
					'name' => $term->name,
					'id' => $term->term_taxonomy_id,
				);
			}

			// Exclude taxonomy if it has no terms.
			if ( empty( $taxonomies[ $taxonomy ] ) ) {
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
			$users[] = array(
				'name' => $user->display_name,
				'id'   => $user->ID,
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
				$post_types[] = array(
					'name'        => $post_type->name,
					'label'       => $post_type->label,
					'has_archive' => $post_type->has_archive, // Can be boolean or string of archive slug, so if truthy, we must also get `slug` in case it differs.
					'slug'        => $post_type->has_archive ? $post_type->rewrite['slug'] : false,
				);
			}
		}
		return $post_types;
	}


	/**
	 * Build array of all pages we want to edit meta for.
	 */
	private function get_page_map() {
		$providers = $this->providers;
		if ( empty( $providers ) ) {
			return;
		}

		$map = array();
		foreach ( self::TYPES as $type ) {

			$pages = array();
			switch ( $type ) {

				case 'front_page':
					$pages['label'] = __( 'Home', 'bigup-seo' );
					break;

				case 'blog_index':
					$pages['label'] = __( 'Blog Index', 'bigup-seo' );
					break;

				case 'page':
					$pages = array(
						'label' => get_post_type_object( $type )->labels->name,
						'ids'   => wp_list_pluck( get_pages(), 'ID' ),
					);
					break;

				case 'post':
					foreach ( $providers['post_types'] as $post_type ) {
						if ( 'page' === $post_type['name'] ) {
							continue;
						}
						$pages[ $post_type['name'] ] = array(
							'label' => $post_type['label'],
							'ids'   => get_posts(
								array(
									'post_type' => $post_type['name'],
									'fields'    => 'ids',
								)
							),
						);
					}
					break;

				case 'post_archive':
					$slugs = array();
					foreach ( $providers['post_types'] as $post_type ) {
						if ( false !== $post_type['has_archive'] ) {
							$slugs[] = $post_type['slug'];
						}
					}
					if ( ! empty( $slugs ) ) {
						$pages = array(
							'label' => __( 'Post Archives' ),
							'slugs' => $slugs,
						);
					}
					break;

				case 'category':
					if ( isset( $providers['taxonomies']['category'] ) ) {
						$pages = $providers['taxonomies']['category'];
					}
					break;

				case 'tag':
					if ( isset( $providers['taxonomies']['post_tag'] ) ) {
						$pages = $providers['taxonomies']['post_tag'];
					}
					break;

				case 'custom_taxonomy':
					$remove            = array( 'category', 'post_tag' );
					$custom_taxonomies = array_diff_key( $providers['taxonomies'], array_flip( $remove ) );
					if ( ! empty( $custom_taxonomies ) ) {
						$pages = $custom_taxonomies;
					}
					break;

				case 'author':
					$pages = $providers['users'];
					break;

				default:
					error_log( "Bigup SEO: Page type {$type} not found." );
					break;
			}

			$map[ $type ] = $pages;
		}

		return $map;
	}
}
