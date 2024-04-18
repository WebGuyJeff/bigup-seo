<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Robots Tester
 *
 * @package bigup-seo
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2024, Jefferson Real
 * @license GPL3+
 * @link https://jeffersonreal.uk
 */
class Parse_Robots {

	/**
	 * The active robots.txt for the site.
	 */
	private static $robots = '';

	/**
	 * The robots.txt rule groups.
	 */
	private static $groups = array();


	/**
	 * Test a URL against robots.txt.
	 *
	 * @param string $url       The URL to test.
	 * @param string $useragent A user agent to test against (optional).
	 */
	public static function test_url( $url, $test_agent = false ) {

		$result = array(
			'crawlable' => null,
			'report'    => '',
		);

		// Validate passed URL.
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			$result['crawlable'] = false;
			$result['report']    = __( 'Invalid URL passed', 'bigup-seo' );
			return $result;
		}

		self::$robots = Robots::get_contents();

		// If there isn't a robots.txt then then crawling is a free-for-all.
		if ( empty( self::$robots ) ) {
			return array(
				'crawlable' => true,
				'rules'     => 'no robots.txt',
			);
		}

		if ( empty( self::$groups ) ) {

			/**
			 * Build the robots rule groups.
			 */

			self::$groups = array();

			$lines      = explode( "\r\n", self::$robots );
			$user_agent = '';

			foreach ( $lines as $line ) {
				if ( ! trim( $line ) || preg_match( '/^#.*$/', $line ) ) {
					// Skip empty lines and comments.
					continue;

				} elseif ( preg_match( '/^\s*User-agent: (.*)/i', $line, $agent_matches ) ) {
					// Line declares a user agent.
					$user_agent = $agent_matches[1];

				} elseif ( preg_match( '/^\s*(Allow|Disallow):(.*)/i', $line, $rule_matches ) && isset( $user_agent ) ) {
					// Line declares a rule.
					self::$groups[ $user_agent ][] = array(
						$rule_matches[1],
						trim( $rule_matches[2] ),
					);
				}
			}
		}

		/**
		 * Test the URL and return the results.
		 */

		$test_groups = array();

		// Check if agent groups were found.
		if ( empty( self::$groups ) ) {
			$result['crawlable'] = true;
			$result['report']    = __( 'No user agents specified', 'bigup-seo' );
			return $result;
		}

		// Check for a passed user agent.
		if ( $test_agent ) {
			if ( isset( self::$groups[ $test_agent ] ) ) {
				$test_groups[ $test_agent ] = self::$groups[ $test_agent ];
			} else {
				// Robots.txt doesn't specify the user agent.
				$result['crawlable'] = true;
				$result['report']    = __( 'User agent not found', 'bigup-seo' );
				return $result;
			}
		} else {
			$test_groups = self::$groups;
		}

		// Process the passed URL.
		$url_parts = wp_parse_url( $url );
		$url_path  = '';
		if ( isset( $url_parts['path'] ) ) {
			$url_path = $url_parts['path'];
		}

		// Test the URL path against the rules.
		foreach ( $test_groups as $agent => $rules ) {
			$matched_rule = array();
			$crawlable    = null;

			if ( ! empty( $rules ) ) {
				foreach ( $rules as $index => $rule ) {
					$directive = $rule[0];
					$path      = $rule[1];
					$pattern   = self::url_to_pattern( $path );

					if ( preg_match( $pattern, $url_path ) ) {
						if ( 'Disallow' === $directive ) {
							$crawlable = ( 'Disallow' === $directive ) ? false : true;
						}
						$matched_rule = array( $directive, $path );

						// End loop for this agent on the first match, as bots also accept first match.
						break;
					}
				}
			}

			if ( isset( $matched_rule[0] ) ) {
				$result['crawlable'] = $crawlable;
				$result['report']   .= $agent . "\n";
				$result['report']   .= $matched_rule[0] . ': ' . $matched_rule[1] . "\n";
			} else {
				$result['crawlable'] = true;
				$result['report']    = __( 'No matching rules', 'bigup-seo' );
			}
		}


		/**
		 * TO FIX: Currently $crawlable shows the result on only the last match. This is great
		 * when a user agent is passed to match against, but meaningless when all agents are
		 * checked.
		 */

		// DEBUG.
		error_log( '$result: ' . wp_json_encode( $result ) );


		return $result;

	}


	/**
	 * Convert a URL into a safe regex pattern.
	 *
	 * @param  string $url     The URL to convert.
	 * @return string $pattern A regex-safe pattern.
	 */
	public static function url_to_pattern( $url ) {
		$escaped_specials = preg_quote( $url );
		$escaped_slashes  = str_replace( '/', '\/', $escaped_specials );
		$pattern          = '/^' . $escaped_slashes . '/i';
		return $pattern;
	}
}
