<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Initialisation.
 *
 * Setup this plugin.
 *
 * @package bigup-seo
 * @author Jefferson Real <jeff@webguyjeff.com>
 * @copyright Copyright (c) 2024, Jefferson Real
 * @license GPL3+
 * @link https://webguyjeff.com
 */

class Init {

	/**
	 * Settings pages for this plugin.
	 */
	public $admin_settings;

	/**
	 * Saved settings for this plugin.
	 */
	private $meta_settings;

	/**
	 * Bigup sitemap.
	 */
	public $sitemap;

	/**
	 * Bigup robots.txt file.
	 */
	public $robots;

	/**
	 * Bigup page meta.
	 */
	public $meta;


	/**
	 * Populate the class properties.
	 */
	public function __construct() {

		new Install();

		$this->sitemap       = new Sitemap();

		$this->meta_settings = get_option( Settings_Page_Meta::OPTION );
		if ( isset( $this->meta_settings['generate_title_tags'] ) && $this->meta_settings['generate_title_tags'] ) {
			$this->meta = new Meta();
		}

		if ( is_admin() ) {
			// The robots class only handles admin configuration of the robots.txt static file.
			$this->robots = new Robots();
		}
	}


	/**
	 * Setup the plugin.
	 */
	public function setup() {

		add_filter( 'site_icon_image_sizes', array( $this, 'add_custom_site_icon_sizes' ), 10, 0 );
		add_action( 'after_setup_theme', array( $this, 'ensure_title_tag_theme_support' ), 1, 0 );
		add_action( 'rest_api_init', array( $this, 'register_rest_api_routes' ), 10, 0 );
		add_action( 'widgets_init', array( $this, 'setup_for_logged_in_admin' ), 999, 0 );

		/*
		Future enhancement: Add options to the meta settings tab to enable/disable the
		following features. This way we can perform an early check to see if any further
		processing needs to take place.
		*/

		// Customise the generated sitemap on all pages.
		$this->sitemap->apply_options();

		if ( is_admin() ) {

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts_and_styles' ), 10, 0 );

			// The robots class only handles admin configuration of the robots.txt static file.
			$this->robots->apply_options();
		}
	}


	/**
	 * Setup logged-in admin users.
	 *
	 * Do not call before 'init' hook otherwise current_user_can() will not be available.
	 * Do not call after 'widgets_init' otherwise admin pages will not be loaded.
	 */
	public function setup_for_logged_in_admin() {
		if ( current_user_can( 'manage_options' ) ) {

			$this->admin_settings = new Admin_Settings();
			$this->admin_settings->register();

			$dev_settings = get_option( 'bigupseo_settings_developer' );
			if ( $dev_settings && $dev_settings['output_meta'] ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'front_end_scripts_and_styles' ), 10, 0 );
			}
		}
	}


	/**
	 * Ensure title tag theme support.
	 */
	public function ensure_title_tag_theme_support() {
		add_theme_support( 'title-tag' );
	}


	/**
	 * Register admin scripts and styles.
	 */
	public function admin_scripts_and_styles() {
		if ( ! wp_script_is( 'bigup_icons', 'registered' ) ) {
			wp_register_style( 'bigup_icons', BIGUPSEO_URL . 'dashicons/css/bigup-icons.css', array(), filemtime( BIGUPSEO_PATH . 'dashicons/css/bigup-icons.css' ), 'all' );
		}
		if ( ! wp_script_is( 'bigup_icons', 'enqueued' ) ) {
			wp_enqueue_style( 'bigup_icons' );
		}
		wp_enqueue_style( 'bigup_seo_admin_css', BIGUPSEO_URL . 'build/css/bigup-seo-admin.css', array(), filemtime( BIGUPSEO_PATH . 'build/css/bigup-seo-admin.css' ), 'all' );
		wp_enqueue_script( 'bigup_seo_admin_js', BIGUPSEO_URL . 'build/js/bigup-seo-admin.js', array(), filemtime( BIGUPSEO_PATH . 'build/js/bigup-seo-admin.js' ), true );
		wp_add_inline_script(
			'bigup_seo_admin_js',
			'bigupSeoWpInlinedScript' . ' = ' . wp_json_encode(
				array(
					'restRobotsURL'  => get_rest_url( null, 'bigup/seo/v1/robots' ),
					'restSeoMetaURL' => get_rest_url( null, 'bigup/seo/v1/seo-meta' ),
					'restNonce'      => wp_create_nonce( 'wp_rest' ),
				)
			),
			'before'
		);
	}


	/**
	 * Register front end scripts and styles.
	 */
	public function front_end_scripts_and_styles() {
		wp_enqueue_script( 'bigup_seo_js', BIGUPSEO_URL . 'build/js/bigup-seo.js', array(), filemtime( BIGUPSEO_PATH . 'build/js/bigup-seo.js' ), true );
		wp_enqueue_style( 'bigup_seo_css', BIGUPSEO_URL . 'build/css/bigup-seo.css', array(), filemtime( BIGUPSEO_PATH . 'build/css/bigup-seo.css' ), 'all' );
	}


	/**
	 * Add site icon (favicon) sizes.
	 */
	public function add_custom_site_icon_sizes( $sizes ) {
		$sizes[] = 96;
		return $sizes;
	}


	/**
	 * Register rest api routes.
	 *
	 * @link https://developer.wordpress.org/reference/functions/register_rest_route/
	 */
	public function register_rest_api_routes() {

		// Robots.txt endpoint.
		register_rest_route(
			'bigup/seo/v1',
			'/robots',
			array(
				'methods'             => 'POST',
				'callback'            => array( new Robots_File_Controller(), 'receive_requests' ),
				'permission_callback' => '__return_true',
			)
		);

		// SEO meta endpoint.
		register_rest_route(
			'bigup/seo/v1',
			'/seo-meta',
			array(
				'methods'             => 'POST',
				'callback'            => array( new Seo_Meta_Controller(), 'receive_requests' ),
				'permission_callback' => '__return_true',
			)
		);
	}
}
