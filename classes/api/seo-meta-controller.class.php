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
	 * Receive robots file API requests.
	 */
	public function receive_requests( \WP_REST_Request $request ) {

		// Check header is multipart/form-data.
		if ( ! str_contains( $request->get_header( 'Content-Type' ), 'multipart/form-data' ) ) {
			$this->send_json_response( array( 405, 'Unexpected payload content-type' ) );
			exit; // Request handlers should exit() when done.
		}

		// Process form data into SQL-ready strings.
		$body                 = $request->get_body_params();
		$reset                = false;
		$page_type            = '';
		$page_type_key        = '';
		$sql_update_cols_vals = '';
		$sql_insert_columns   = '';
		$sql_insert_values    = '';
		foreach ( $body as $key => $value ) {
			// Refactor as 'seo_reset_flag' will break if not always first in array.
			if ( 'seo_reset_flag' === $key && $value ) {
				$reset = true;
			} elseif ( 'page_type' === $key || 'page_type_key' === $key ) {
				$sql_insert_columns .= $key . ', ';
				$sql_insert_values  .= "'" . $value . "', ";
			} else {
				if ( $reset ) {
					$value = '';
				}
				$sql_update_cols_vals .= $key . ' = ' . "'" . $value . "', ";
				$sql_insert_columns   .= $key . ', ';
				$sql_insert_values    .= "'" . $value . "', ";
			}
		}
		$sql_update_cols_vals = preg_replace( '/, $/', '', $sql_update_cols_vals );
		$sql_insert_columns   = preg_replace( '/, $/', '', $sql_insert_columns );
		$sql_insert_values    = preg_replace( '/, $/', '', $sql_insert_values );

		global $wpdb;
		$table_name = $wpdb->prefix . 'bigup_seo_meta';

		// Update the DB table.
		$insert_or_update_table_row = "
			INSERT INTO wp_bigup_seo_meta ( $sql_insert_columns )
			VALUES ( $sql_insert_values )
			ON DUPLICATE KEY UPDATE $sql_update_cols_vals;
		";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$messages = dbDelta( $insert_or_update_table_row );

		// DEBUG.
		error_log( 'DB insert or update: ' . json_encode( $result ) );

		$this->send_json_response(
			200, // Always 200 if server was reached, as dbDelta() doesn't return success/failure.
			$messages, // The MySQL messages for each query passed.
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
