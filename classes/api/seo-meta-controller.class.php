<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Bigup SEO - SEO Meta Controller.
 *
 * Handle manipulation of SEO meta in the custom DB table.
 *
 * @package bigup-forms
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2024, Jefferson Real
 * @license GPL3+
 * @link https://jeffersonreal.uk
 */
class Seo_Meta_Controller {

	/**
	 * Receive API requests.
	 */
	public function receive_requests( \WP_REST_Request $request ) {

		// Check header is multipart/form-data.
		if ( ! str_contains( $request->get_header( 'Content-Type' ), 'multipart/form-data' ) ) {
			$this->send_json_response( array( 405, 'Unexpected payload content-type' ) );
			exit; // Request handlers should exit() when done.
		}

		global $wpdb;

		// Process form data into SQL-ready strings.
		$body               = $request->get_body_params();
		$reset              = false;
		$page_type          = '';
		$page_type_key      = '';
		$columns_and_values = '';
		$columns            = '';
		$values             = '';
		if ( array_key_exists( 'seo_reset_flag', $body ) ) {
			// Grab the reset flag if present and sepearate it from the table data.
			if ( $body['seo_reset_flag'] ) {
				$reset = true;
			}
			unset( $body['seo_reset_flag'] );
		}
		foreach ( $body as $key => $value ) {
			if ( 'page_type' === $key || 'page_type_key' === $key ) {
				$columns .= $wpdb->prepare( '%i, ', $key );
				$values  .= $wpdb->prepare( '%s, ', $value );
			} else {
				if ( $reset || '' === $value ) {
					/*
					 * $wpdb->prepare() currently doesn't support null values, so we use this
					 * string placeholder which will then be replaced before passing to dbDelta().
					 */
					$value = 'NULL';
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
		$table_name   = $wpdb->prefix . 'bigup_seo_meta';
		$upsert_query = "
			INSERT INTO $table_name ( $sql_columns )
			VALUES ( $sql_values )
			ON DUPLICATE KEY UPDATE $sql_columns_and_values;
		";
		// Replace placeholders with nulls.
		$upsert_query_nulled = preg_replace( "/'NULL'/", 'NULL', $upsert_query );
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$messages = dbDelta( $upsert_query_nulled );

		$this->send_json_response(
			// ToDo: Extend to reflect DB errors.
			200,
			$messages, // The MySQL messages for each query passed (normally empty).
		);
		exit; // Request handlers should exit() when done.
	}


	/**
	 * Send JSON response to client.
	 *
	 * Sets the response header to the passed http status code and a
	 * response body containing an array of status code, status text
	 * and human-readable description of the status or error.
	 *
	 * @param int   $code     HTTP code.
	 * @param array $messages Array of message strings.
	 */
	private function send_json_response( $code, $messages ) {

		// Ensure response headers haven't already sent to browser.
		if ( ! headers_sent() ) {
			header( 'Content-Type: application/json; charset=utf-8' );
			status_header( $code );
		}

		$response = array(
			'ok'       => ( $code < 300 ) ? true : false,
			'messages' => $messages,
		);

		echo wp_json_encode( $response );
	}
}
