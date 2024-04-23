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
		register_activation_hook( self::PLUGIN_PATH, array( $this, 'create_db_tables' ) );
	}


	/**
	 * Create the database tables for the plugin.
	 */
	public static function create_db_tables() {

		global $wpdb;

		$table_name = $wpdb->prefix . 'bigup_seo_meta';

		$charset_collate = $wpdb->get_charset_collate();

		// Tables columns:
		// ID | page_type    | page_type_key | seo_title  | seo_description  | seo_canonical
		// 56 | post__review | 1625          | Some Title | Some description | https://blah

		/*
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
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			page_type tinytext NOT NULL,
			page_type_key tinytext NOT NULL,
			seo_title tinytext,
			seo_description tinytext,
			seo_canonical varchar(1855),
			PRIMARY KEY  (id)
		) $charset_collate;
		ALTER TABLE $table_name
			ADD CONSTRAINT SEO_Page UNIQUE (page_type,page_type_key);";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$result = dbDelta( $sql );

		// DEBUG.
		error_log( 'DB table result: ' . json_encode( $result ) );
	}
}
