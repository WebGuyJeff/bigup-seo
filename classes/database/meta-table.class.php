<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Bigup SEO - Meta Table DB Dandler.
 *
 * @package bigup-forms
 * @author Jefferson Real <jeff@webguyjeff.com>
 * @copyright Copyright (c) 2024, Jefferson Real
 * @license GPL3+
 * @link https://webguyjeff.com
 */
class Meta_Table {

	const TABLE_SUFFIX = 'bigup_seo_meta';


	/**
	 * Create the database table.
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
	public static function create_table() {

		global $wpdb;

		$table_name      = $wpdb->prefix . self::TABLE_SUFFIX;
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
		$create_meta_table_query = "
			CREATE TABLE $table_name
			(
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				page_type varchar(20) NOT NULL,
				page_type_key varchar(20) NOT NULL,
				meta_title tinytext,
				meta_description tinytext,
				meta_canonical varchar(1855),
				PRIMARY KEY  (id),
				CONSTRAINT page_index UNIQUE (page_type, page_type_key)
			)
			$charset_collate;
		";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$create_table_result = dbDelta( $create_meta_table_query );

		return $create_table_result;
	}


	/**
	 * Upsert a row into the table .
	 *
	 * @param array $data Key/values to upsert.
	 */
	public static function upsert( $data ) {

		global $wpdb;

		// Process form data into SQL-ready strings.
		$reset              = false;
		$page_type          = '';
		$page_type_key      = '';
		$columns_and_values = '';
		$columns            = '';
		$values             = '';
		if ( array_key_exists( 'seo_reset_flag', $data ) ) {
			// Grab the reset flag if present and sepearate it from the table data.
			if ( $data['seo_reset_flag'] ) {
				$reset = true;
			}
			unset( $data['seo_reset_flag'] );
		}
		foreach ( $data as $key => $value ) {
			if ( 'page_type' === $key || 'page_type_key' === $key ) {
				$columns .= $wpdb->prepare( '%i, ', $key );
				$values  .= $wpdb->prepare( '%s, ', $value );
			} else {
				if ( $reset || '' === $value ) {
					/*
					 * $wpdb->prepare() currently doesn't support null values, so we use this
					 * string placeholder which will then be replaced before passing to dbDelta().
					 */
					$value        = 'NULL';
					$data[ $key ] = null; // We use $data to test against the DB after upsert.
				}
				$columns_and_values .= $wpdb->prepare( '%i = %s, ', $key, $value );
				$columns            .= $wpdb->prepare( '%i, ', $key );
				$values             .= $wpdb->prepare( '%s, ', $value );
			}
		}
		$sql_columns_and_values = preg_replace( '/, $/', '', $columns_and_values );
		$sql_columns            = preg_replace( '/, $/', '', $columns );
		$sql_values             = preg_replace( '/, $/', '', $values );

		/*
		 * Update the DB table.
		 *
		 * Note dbDelta() only returns message strings or empty array, so errors should be handled
		 * with another method.
		 */
		$table_name   = $wpdb->prefix . self::TABLE_SUFFIX;
		$upsert_query = "
			INSERT INTO $table_name ( $sql_columns )
			VALUES ( $sql_values )
			ON DUPLICATE KEY UPDATE $sql_columns_and_values;
		";
		// Replace placeholders with nulls.
		$upsert_query_nulled = preg_replace( "/'NULL'/", 'NULL', $upsert_query );
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		/*
		 * Messages are supposed to be returned by dbDelta(), but the array is almost always empty
		 * even on failure. These aren't reliable for error feedback. To check for errors we
		 * manually query the database to confirm the newly added data is present (see below).
		 */
		dbDelta( $upsert_query_nulled );

		// Check for upsert success/fail by testing if the retrieved row data matches our input data.
		$db_data = self::get_row( $data['page_type'], $data['page_type_key'] );
		$data_ok = true;
		if ( empty( $db_data ) ) {
			$data_ok = false;
		} else {
			foreach ( $data as $key => $value ) {
				$test_value = ( empty( $value ) ) ? null : $value;
				if ( $db_data->$key !== $test_value ) {
					$data_ok = false;
				}
			}
		}

		return $data_ok;
	}


	/**
	 * Get all meta rows.
	 */
	public static function get_all_rows() {

		global $wpdb;

		$table_name         = $wpdb->prefix . self::TABLE_SUFFIX;
		$get_all_rows_query = $wpdb->prepare( 'SELECT * FROM %i', $table_name );

		// $wpdb->get_results( $query, OBJECT ) returns an array of object rows.
		$results = $wpdb->get_results( $get_all_rows_query, OBJECT );

		// Structure the data.
		$meta = new \stdClass();
		foreach ( $results as $row ) {
			$type = $row->page_type;
			$key  = $row->page_type_key;
			if ( ! property_exists( $meta, $type ) ) {
				$meta->{$type} = new \stdClass();
			}
			if ( ! property_exists( $meta->{$type}, $key ) ) {
				$meta->{$type}->{$key} = new \stdClass();
			}
			foreach ( $row as $column => $value ) {
				$meta->{$type}->{$key}->{$column} = $value;
			}
		}

		return $meta;
	}


	/**
	 * Get meta row by index.
	 */
	public static function get_row( $page_type, $page_type_key ) {

		global $wpdb;

		$table_name    = $wpdb->prefix . self::TABLE_SUFFIX;
		$get_row_query = $wpdb->prepare(
			'SELECT * FROM %i WHERE (%i = %s AND %i = %s)',
			$table_name,
			'page_type',
			$page_type,
			'page_type_key',
			$page_type_key
		);

		// $wpdb->get_results( $query, OBJECT ) returns an array of object rows.
		$results = $wpdb->get_results( $get_row_query, OBJECT );
		$row     = $results[0] ?? null;

		return $row;
	}
}
