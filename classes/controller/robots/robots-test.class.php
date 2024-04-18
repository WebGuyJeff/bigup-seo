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
class Robots_Test {

	/**
	 * The active robots.txt for the site.
	 */
	private static $robots;


	/**
	 * Test a URL against robots.txt.
	 *
	 * @see https://www.the-art-of-web.com/php/parse-robots/
	 */
	public static function url( $url, $useragent = false ) {

		self::$robots = Robots::get_contents();

		// Build array of valid user agents.
		$agents = array(
			preg_quote( '*' ),
		);
		if ( $useragent ) {
			$agents[] = preg_quote( $useragent );
		}
			$agents = implode( '|', $agents );

			// If there isn't a robots.txt then then crawling is a free-for-all.
		if ( empty( self::$robots ) ) {
			return true;
		}

			$rules = array();

			// First line of robots.txt file.
			$line = strtok( $robotstxt, "\r\n" );

		while ( false !== $line ) {

			$rule_applies = false;

			// Skip blank lines.
			if ( ! $line = trim( $line ) ) {
				continue;
			}

			// Following rules only apply if User-agent matches $useragent or '*'.
			if ( preg_match( '/^\s*User-agent: (.*)/i', $line, $match ) ) {
				$rule_applies = preg_match( "/($agents)/i", $match[1] );
			}

			if ( $rule_applies && preg_match( '/^\s*Disallow:(.*)/i', $line, $regs ) ) {
				// An empty rule implies full access - no further tests required.
				if ( ! $regs[1] ) {
					return true;
				}
				// Add rules that apply to array for testing.
				$rules[] = preg_quote( trim( $regs[1] ), '/' );
			}

			// Next line of robots.txt file.
			$line = strtok( "\r\n" );

		}

		foreach ( $rules as $rule ) {
			// Check if page is disallowed to us.
			if ( preg_match( "/^$rule/", $parsed['path'] ) ) {
				return false;
			}
		}

			// page is not disallowed
			return true;
	}
}
