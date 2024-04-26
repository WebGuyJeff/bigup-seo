<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Install.
 *
 * Handle all functionality which needs to be installed on plugin activation.
 *
 * @package bigup-seo
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2024, Jefferson Real
 * @license GPL3+
 * @link https://jeffersonreal.uk
 */

class Install {

	public const PLUGIN_PATH = BIGUPSEO_PATH . 'plugin-entry.php';

	/**
	 * Setup a new instance.
	 */
	public function __construct() {
		register_activation_hook( self::PLUGIN_PATH, array( $this, 'all' ) );
	}


	/**
	 * Install all plugin requirements callback.
	 *
	 * This should be called by `register_activation_hook()` as a single handler for all plugin
	 * activation tasks.
	 */
	public function all() {
		$this->create_meta_db_tables();
	}


	/**
	 * Create the database tables for the plugin.
	 *
	 * @see https://codex.wordpress.org/Creating_Tables_with_Plugins#Creating_or_Updating_the_Table
	 *
	 * You must put each field on its own line in your SQL statement.
	 * You must have two spaces between the words PRIMARY KEY and the definition of your primary key.
	 * You must use the key word KEY rather than its synonym INDEX and you must include at least one KEY.
	 * KEY must be followed by a SINGLE SPACE then the key name then a space then open parenthesis with the field name then a closed parenthesis.
	 * You must not use any apostrophes or backticks around field names.
	 * Field types must be all lowercase.
	 * SQL keywords, like CREATE TABLE and UPDATE, must be uppercase.
	 * You must specify the length of all fields that accept a length parameter. int(11), for example.
	 */
	public function create_meta_db_tables() {

		global $wpdb;

		$table_name      = $wpdb->prefix . 'bigup_seo_meta';
		$charset_collate = $wpdb->get_charset_collate();

		/*
		 * SEO Meta table.
		 *
		 * Column page_type is restricted to 20 chars for performance, and some of the type labels
		 * are created by me where they are not default WP types, e.g. 'site_index'. If a new type
		 * is created or edited and something breaks, check you haven't exceeded this limit.
		 *
		 * Column page_type_key will always be an ID number or a post type slug. The slug has a
		 * limit of 20 chars set by WordPress, so our DB column is also limited to 20 characters.
		 */
		$create_meta_table = "
			CREATE TABLE $table_name
			(
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				page_type varchar(20) NOT NULL,
				page_type_key varchar(20) NOT NULL,
				seo_title tinytext,
				seo_description tinytext,
				seo_canonical varchar(1855),
				PRIMARY KEY  (id),
				CONSTRAINT seo_page_index UNIQUE (page_type, page_type_key)
			)
			$charset_collate;
		";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$create_meta_table_result = dbDelta( $create_meta_table );
	}
}
