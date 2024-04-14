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
		'taxonomy',
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
	 * Compile a list of all pages that we want to set SEO meta on.
	 */
	public function setup() {
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
			'objects'
		);
		return $taxonomies;
	}


	/**
	 * Get non-empty taxonomy terms.
	 */
	public function get_taxonomy_terms( $taxonomy ) {
		$args  = array(
			'taxonomy'               => $taxonomy,
			'orderby'                => 'term_order',
			'hide_empty'             => true,
			'hierarchical'           => false,
			'update_term_meta_cache' => false,
			'fields'                 => 'id=>name',
		);
		$terms = array();
		foreach ( get_terms( $args ) as $id => $name ) {
			$terms[ $id ] = array(
				'name' => $name,
			);
		}
		return $terms;
	}


	/**
	 * Get taxonomies with terms.
	 */
	public function get_taxonomies_with_terms() {
		foreach ( $this->get_taxonomies() as $taxonomy_name => $taxonomy ) {

			$terms = $this->get_taxonomy_terms( $taxonomy_name );
			if ( empty( $terms ) ) {
				continue;
			}

			$taxonomies[] = array(
				'name'  => $taxonomy_name,
				'label' => $taxonomy->label,
				'ids'   => $terms,
			);
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
			$users[ $user->ID ] = array(
				'name' => $user->display_name,
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
	 *
	 * Post and taxonomy types are prefixed `post__` and `tax__` respectively as an alternative to
	 * nesting arrays to make processing simpler.
	 */
	private function get_page_map() {

		// Get all providers of WordPress generated pages.
		$this->providers = array(
			'taxonomies' => $this->get_taxonomies_with_terms(),
			'post_types' => $this->get_post_types(),
			'users'      => $this->get_users(),
		);

		// Build a map of all generated site pages.
		$map = array();
		foreach ( self::TYPES as $type ) {
			switch ( $type ) {

				case 'front_page':
					$map['front_page'] = array(
						'key_type' => 'anon',
						'label'    => __( 'Home', 'bigup-seo' ),
					);
					break;

				case 'blog_index':
					$map['blog_index'] = array(
						'key_type' => 'anon',
						'label'    => __( 'Blog Index', 'bigup-seo' ),
					);
					break;

				case 'page':
					$exclusions = array(
						get_option( 'page_on_front' ),
						get_option( 'page_for_posts' ),
					);
					$args       = array(
						'hierarchical' => false,
						'sort_order'   => 'DESC',
						'sort_column'  => 'post_date',
						'exclude'      => $exclusions,
					);
					$wp_pages   = get_pages( $args );
					$pages      = array();
					foreach ( $wp_pages as $page ) {
						$pages[ $page->ID ] = array(
							'name' => $page->post_title,

						);
					}
					$map['page'] = array(
						'label'    => get_post_type_object( $type )->labels->name,
						'key_type' => 'id',
						'pages'    => $pages,
					);
					break;

				case 'post':
					foreach ( $this->providers['post_types'] as $post_type ) {
						if ( 'page' === $post_type['name'] ) {
							// Don't process page post type again.
							continue;
						}
						$args     = array(
							'post_type' => $post_type['name'],
							'order'     => 'DESC',
							'orderby'   => 'date',
						);
						$wp_posts = get_posts( $args );
						$posts    = array();
						foreach ( $wp_posts as $post ) {
							$posts[ $post->ID ] = array(
								'name' => $post->post_title,
							);
						}
						$map[ 'post__' . $post_type['name'] ] = array(
							'label'    => $post_type['label'],
							'key_type' => 'id',
							'pages'    => $posts,
						);
					}
					break;

				case 'post_archive':
					$slugs = array();
					foreach ( $this->providers['post_types'] as $post_type ) {
						if ( false !== $post_type['has_archive'] ) {
							$slug           = ( 'string' === gettype( $post_type['has_archive'] ) )
								? $post_type['has_archive']
								: $post_type['slug'];
							$slugs[ $slug ] = array(
								'name' => $slug,
							);
						}
					}
					if ( ! empty( $slugs ) ) {
						$map['post_archive'] = array(
							'label'    => __( 'Post Archives' ),
							'key_type' => 'slug',
							'pages'    => $slugs,
						);
					}
					break;

				case 'taxonomy':
					foreach ( $this->providers['taxonomies'] as $tax ) {
						$map[ 'tax__' . $tax['name'] ] = array(
							'label'    => $tax['label'],
							'key_type' => 'id',
							'pages'    => $tax['ids'],
						);
					}
					break;

				case 'author':
					$map['author'] = array(
						'label'    => __( 'Authors' ),
						'key_type' => 'id',
						'pages'    => $this->providers['users'],
					);
					break;

				default:
					error_log( "Bigup SEO: Page type {$type} not found." );
					break;
			}
		}

		return $map;
	}
}
