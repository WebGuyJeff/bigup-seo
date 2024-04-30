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

		// Submit data to be inserted to the database.
		$form_values = $request->get_body_params();
		$data_ok     = Meta_Table::upsert( $form_values );
		$string_pass = __( 'Database updated successfully.', 'bigup-seo' );
		$string_fail = __( 'Database update failure. Please check site logs.', 'bigup-seo' );
		$messages    = array( $data_ok ? $string_pass : $string_fail );

		$this->send_json_response(
			$data_ok ? 200 : 500,
			$messages,
		);
		exit; // Request handlers should exit() when done.
	}


	/**
	 * Send JSON response to client.
	 *
	 * Set the response header and send a response to the client.
	 *
	 * @param int   $code     HTTP code.
	 * @param array $messages Array of message strings.
	 */
	private function send_json_response( $code, $messages ) {

		/*
		 * Clean output buffer to ensure we only include what we want in the response. Note that
		 * without this, WP HTML errors may be included breaking the response formatting.
		 */
		ob_clean();

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
