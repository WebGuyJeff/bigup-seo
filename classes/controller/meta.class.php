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
	private const PAGE_TYPES = array(
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

		$this->pages = $this->get_all_page_ids( $this->providers );

		// DEBUG.
		if ( is_admin() ) {
			echo '<pre style="z-index:9999;background:#fff;position:fixed;right:0;max-height:80vh;overflow-y:scroll;padding:0.5rem;border:solid;font-size:0.7rem;">';
			var_dump( $this->providers );
			echo '</pre>';
		}

		// DEBUG.
		if ( is_admin() ) {
			echo '<pre style="z-index:9999;background:#fff;position:fixed;left:0;max-height:80vh;overflow-y:scroll;padding:0.5rem;border:solid;font-size:0.7rem;">';
			var_dump( $this->pages );
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
			'fields'                 => 'all',
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

				$taxonomies[ $taxonomy ][ $term->name ] = array(
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
	 * Get IDs or slugs for all pages.
	 */
	private function get_all_page_ids( $providers ) {

		$site_pages = array();
		foreach ( self::PAGE_TYPES as $type ) {

			$pages = array();
			switch ( $type ) {

				case 'front_page':
					$pages['id'] = get_option( 'page_on_front' );
					break;

				case 'blog_index':
					$pages['id'] = get_option( 'page_for_posts' );
					break;

				case 'page':
					$all_pages    = get_pages();
					$pages['ids'] = wp_list_pluck( $all_pages, 'ID' );
					break;

				case 'post':
					$post_types = array_keys( $providers['post_types'] );
					foreach ( $post_types as $post_type ) {
						if ( 'page' === $post_type ) {
							continue;
						}
						$args                       = array(
							'post_type' => $post_type,
							'fields'    => 'ids',
						);
						$pages[ $post_type ]        = array();
						$pages[ $post_type ]['ids'] = get_posts( $args );
					}
					break;

				case 'post_archive':
					foreach ( $providers['post_types'] as $post_type ) {
						if ( false !== $post_type['has_archive'] ) {
							$pages[] = $post_type['has_archive'];
						}
					}
					break;

				case 'category':
					if ( isset( $providers['taxonomies']['category'] ) ) {
						$pages = $providers['taxonomies']['category'];
					}
					break;

				case 'tag':
					if ( isset( $providers['taxonomies']['tag'] ) ) {
						$pages = $providers['taxonomies']['tag'];
					}
					break;

				case 'custom_taxonomy':
					$remove            = array( 'category', 'tag' );
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

			$site_pages[ $type ] = $pages;
		}

		return $site_pages;
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
			return 'post';
		} elseif ( is_post_type_archive() ) {
			return 'post_archive';
		} elseif ( is_tax() ) {
			if ( is_category() ) {
				return 'category';
			} elseif ( is_tag() ) {
				return 'tag';
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
