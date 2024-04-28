<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Plugin Name: Bigup Web: Bigup SEO
 * Plugin URI: https://jeffersonreal.uk
 * Description: Bigup your SEO game.
 * Version: 0.1.1
 * Author: Jefferson Real
 * Author URI: https://jeffersonreal.uk
 * License: GPL3+
 *
 * @package bigup-seo
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2024, Jefferson Real
 * @license GPL3+
 * @link https://jeffersonreal.uk
 */

$enable_debug = true;

// Set global constants.
define( 'BIGUPSEO_DEBUG', $enable_debug );
define( 'BIGUPSEO_PATH', trailingslashit( __DIR__ ) );
define( 'BIGUPSEO_URL', trailingslashit( get_site_url( null, strstr( __DIR__, '/wp-content/' ) ) ) );

// Register namespaced autoloader.
$namespace = 'BigupWeb\\Bigup_Seo\\';
$root      = BIGUPSEO_PATH . 'classes/';
require_once $root . 'autoload.php';

// Setup the plugin.
$Init = new Init();
$Init->setup();





/****************************************
 * TAX TESTING - DELETE WHEN DEBUG DONE!
 */
add_action(
	'init',
	function () {
		// Add new "Locations" taxonomy to Posts
		register_taxonomy(
			'location',
			'post',
			array(
				// Hierarchical taxonomy (like categories)
				'hierarchical' => true,
				// This array of options controls the labels displayed in the WordPress Admin UI
				'labels'       => array(
					'name'              => _x( 'Locations', 'taxonomy general name' ),
					'singular_name'     => _x( 'Location', 'taxonomy singular name' ),
					'search_items'      => __( 'Search Locations' ),
					'all_items'         => __( 'All Locations' ),
					'parent_item'       => __( 'Parent Location' ),
					'parent_item_colon' => __( 'Parent Location:' ),
					'edit_item'         => __( 'Edit Location' ),
					'update_item'       => __( 'Update Location' ),
					'add_new_item'      => __( 'Add New Location' ),
					'new_item_name'     => __( 'New Location Name' ),
					'menu_name'         => __( 'Locations' ),
				),
				// Control the slugs used for this taxonomy
				'rewrite'      => array(
					'slug'         => 'locations', // This controls the base slug that will display before each term
					'with_front'   => false, // Don't display the category base before "/locations/"
					'hierarchical' => true, // This will allow URL's like "/locations/boston/cambridge/"
				),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_in_nav_menus'  => true,
				'show_in_rest'       => true,
			)
		);
	},
	0
);
