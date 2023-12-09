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
	 * Setup the plugin.
	 */
	public function setup() {
		$Admin_Settings_Parent = new Admin_Settings_Parent();
		add_action( 'admin_menu', array( &$Admin_Settings_Parent, 'register_admin_menu' ), 1, 0 );
		$Admin_Settings = new Admin_Settings();
		add_action( 'bigup_settings_dashboard_entry', array( &$Admin_Settings, 'echo_plugin_settings_link' ), 10, 0 );
		add_action( 'admin_menu', array( &$Admin_Settings, 'register_admin_menu' ), 99 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts_and_styles' ), 10, 0 );
		add_filter( 'site_icon_image_sizes', array( $this, 'add_custom_site_icon_sizes' ), 10, 0 );
		add_theme_support( 'title-tag' );
		add_action( 'template_redirect', array( $this, 'do_head_meta_before_wp_head' ), 1, 0 );
	}


	/**
	 * Hook into wp_head to add meta and modify title tags.
	 *
	 * Head_Meta must be instantiated between the wp query and 'wp_head' hook.
	 *
	 * Removing theme sopport 'title-tag' and wp_head title action was unreliable, so now we're
	 * filtering the core document_title instead.
	 */
	public function do_head_meta_before_wp_head() {
		$Head_Meta = new Head_Meta();
		add_filter( 'document_title', array( &$Head_Meta, 'get_title_tag_text' ), 1 );
		add_action( 'wp_head', array( &$Head_Meta, 'print_markup' ), 2, 0 );
	}


	/**
	 * Enqueue admin scripts and styles.
	 *
	 * These assets will be automatically enqueued in admin only.
	 */
	public function enqueue_admin_scripts_and_styles() {
		if ( ! wp_script_is( 'bigup_icons', 'registered' ) ) {
			wp_register_style( 'bigup_icons', BIGUPSEO_URL . 'dashicons/css/bigup-icons.css', array(), filemtime( BIGUPSEO_PATH . 'dashicons/css/bigup-icons.css' ), 'all' );
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
