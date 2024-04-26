<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Bigup SEO - Robots file controller.
 *
 * Handle requests for robots.txt creation and deletion.
 *
 * @package bigup-forms
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2024, Jefferson Real
 * @license GPL3+
 * @link https://jeffersonreal.uk
 */
class Robots_File_Controller {

	/**
	 * Receive robots file API requests.
	 */
	public function receive_requests( \WP_REST_Request $request ) {

		// Check header is JSON.
		if ( ! str_contains( $request->get_header( 'Content-Type' ), 'application/json' ) ) {
			$this->send_json_response( array( 405, 'Unexpected payload content-type' ) );
			exit; // Request handlers should exit() when done.
		}

		$data   = $request->get_json_params();
		$action = $data['action'];
		$result = '';
		if ( 'create' === $action ) {
			$result = Robots::write_file();

		} elseif ( 'delete' === $action ) {
			$result = Robots::delete_file();

		}

		$file_exists = Robots::file_exists();

		$this->send_json_response( ( $result ) ? 200 : 500, $file_exists );
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
	private function send_json_response( $code, $file_exists = null ) {

		// Ensure response headers haven't already sent to browser.
		if ( ! headers_sent() ) {
			header( 'Content-Type: application/json; charset=utf-8' );
			status_header( $code );
		}

		$response = array(
			'ok'     => ( $code < 300 ) ? true : false,
			'exists' => $file_exists,
		);

		echo wp_json_encode( $response );
	}
}
