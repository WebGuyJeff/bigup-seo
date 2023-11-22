<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Initialisation.
 *
 * Setup this plugin.
 *
 * @package bigup-seo
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2023, Jefferson Real
 * @license GPL3+
 * @link https://jeffersonreal.uk
 */

class Init {

	/**
	 * Whether we are in admin area.
	 */
	private bool $is_admin = false;


	/**
	 * Populate class props.
	 */
	public function __construct() {
		$this->$is_admin = is_admin();
	}

	/**
	 * Setup the plugin.
	 */
	public function setup() {
		if ( $this->is_admin ) {
			new Admin_Settings();
		}

		// Remove default title meta function.
		remove_action( 'wp_head', '_wp_render_title_tag', 1 );
		remove_theme_support( 'title-tag' );

		// Hook head SEO meta.
		add_action( 'wp_head', array( new Head_Meta(), 'print_head_meta' ), 1, 0 );

		// enqueue admin assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts_and_styles' ), 10, 0 );

		add_filter( 'site_icon_image_sizes', array( $this, 'add_custom_site_icon_sizes' ), 10, 0 );
	}


	/**
	 * Enqueue admin scripts and styles.
	 *
	 * These assets will be automatically enqueued in admin only.
	 */
	public function enqueue_admin_scripts_and_styles() {
		if ( ! wp_script_is( 'bigup_icons', 'registered' ) ) {
			wp_register_style( 'bigup_icons', BIGUPSEO_URL . 'dashicons / css / bigup - icons . css', array(), filemtime( BIGUPSEO_PATH . 'dashicons / css / bigup - icons . css' ), 'all' );
		}
		if ( ! wp_script_is( 'bigup_icons', 'enqueued' ) ) {
			wp_enqueue_style( 'bigup_icons' );
		}
	}


	/**
	 * Add site icon (favicon) sizes.
	 */
	public function add_custom_site_icon_sizes( $sizes ) {
		$sizes[] = 96;
		return $sizes;
	}
}
