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

		$body          = $request->get_body_params();
		$data          = array();
		$page_type     = array();
		$page_type_key = '';
		$reset         = false;

		// Process form data.
		foreach ( $body as $key => $value ) {
			if ( 'seo_reset_flag' === $key ) {
				$reset = $value;
			} elseif ( 'page_type' === $key ) {
				$page_type = $value;
			} elseif ( 'page_type_key' === $key ) {
				$page_type_key = $value;
			} else {
				$data[ $key ] = $value;
			}
		}

		// DEBUG.
		error_log( '$reset: ' . $reset );
		error_log( '$data: ' . json_encode( $data ) );
		error_log( '$where: ' . json_encode( $where ) );

		global $wpdb;
		$table_name = $wpdb->prefix . 'bigup_seo_meta';

		/**
		 * Update the DB table.
		 *
		 * @see https://developer.wordpress.org/reference/classes/wpdb/update/
		 */
		/*
		$result = $wpdb->update(
			$table_name,
			$data,       // Column => value pairs of data to insert.
			$where,      // Column => value pairs to identify the row to update.
			// Use $format and $where_format (ommitted) if values are anything other than string.
		);
		*/

		// FINISH THIS!!!
		$sql = "SELECT *
			FROM $table_name
			WHERE (page_type = $page_type AND page_type_key = $page_type_key;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$result = dbDelta( $sql );

		// DEBUG.
		error_log( 'DB table result: ' . json_encode( $result ) );





		$this->send_json_response(
			( $result ) ? 200 : 500,
			( $result ) ? 'Update succesful' : 'Update failed', // Non-public.
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
	 * @param array $info: [ int(http-code), str(human readable message) ].
	 */
	private function send_json_response( $status, $file_exists = null ) {

		// Ensure response headers haven't already sent to browser.
		if ( ! headers_sent() ) {
			header( 'Content-Type: application/json; charset=utf-8' );
			status_header( $status );
		}

		$response = array(
			'ok'     => ( $status < 300 ) ? true : false,
			'exists' => $file_exists,
		);

		echo wp_json_encode( $response );
	}
}
