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
	private $robots_txt = '';

	/**
	 * The robots.txt rule groups.
	 */
	public $groups = array();


	/**
	 * Get a new instance.
	 */
	public function __construct( $robots_txt = false ) {
		$this->robots_txt = ( $robots_txt ) ? $robots_txt : Robots::get_existing_contents();
		$this->groups     = $this->extract_rule_groups( $this->robots_txt );
	}


	/**
	 * Test if a bot is allowed to crawl a URL.
	 *
	 * NOTE: This method only works for traditional robots.txt rules. Unstandardised pattern
	 * matching accepted by some crawlers is not accounted for.
	 *
	 * @param  string $url The URL to test.
	 * @param  string $bot A user agent to test against (optional).
	 * @return bool|null Boolean on succesful test and null on error.
	 */
	public function is_url_allowed_for_bot( $url, $bot ) {

		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return null;
		}

		if ( ! $bot || ! is_string( $bot ) ) {
			return null;
		}

		if ( empty( $this->robots_txt ) || empty( $this->groups ) ) {
			// If robots.txt is missing or empty crawling is allowed.
			return true;
		}

		$rules    = $this->get_rules_matching_bot( $bot );
		$url_path = $this->get_url_path( $url );
		$result   = null;

		// Test the URL path against the rules.
		foreach ( $rules as $index => $rule ) {
			$pattern = $this->url_to_pattern( $rule['path'] );
			if ( preg_match( $pattern, $url_path ) ) {
				$result = ( 'Allow' === $rule['directive'] ) ? true : false;
				// End loop as the first matching rule wins.
				break;
			} else {
				// No matching rules so crawling is allowed.
				$result = true;
			}
		}
		return $result;
	}


	/**
	 * Get rules matching a passed URL.
	 *
	 * @param  string $url The URL to test.
	 * @return string A multiline string of user-agents and matching rules.
	 */
	public function get_rules_report_for_url( $url ) {

		if ( ! filter_var( $url, FILTER_VALIDATE_URL )
			|| empty( $this->robots_txt )
			|| empty( $this->groups ) ) {
			return false;
		}

		$url_path = $this->get_url_path( $url );
		$report   = '';

		// Test the URL path against the rules.
		foreach ( $this->groups as $agent => $rules ) {
			$matched_rules = array();
			if ( ! empty( $rules ) ) {
				foreach ( $rules as $index => $rule ) {
					$pattern = $this->url_to_pattern( $rule['path'] );
					if ( preg_match( $pattern, $url_path ) ) {
						$matched_rules[] = $rule['directive'] . ': ' . $rule['path'];
					}
				}
			}
			if ( empty( $matched_rules ) ) {
				continue;
			}
			$report .= 'User-agent: ' . $agent . "\n";
			foreach ( $matched_rules as $rule ) {
				$report .= $rule . "\n";
			}
		}
		return $report;
	}


	/**
	 * Get a text report of all rules.
	 *
	 * @return string A multiline string of all user-agents and rules.
	 */
	public function get_all_rules_report() {

		if ( empty( $this->robots_txt ) || empty( $this->groups ) ) {
			return false;
		}

		$report = '';

		// Build the string of user agents and rules.
		foreach ( $this->groups as $agent => $rules ) {
			$all_rules = array();
			if ( ! empty( $rules ) ) {
				foreach ( $rules as $index => $rule ) {
					$all_rules[] = $rule['directive'] . ': ' . $rule['path'];
				}
			}
			$report .= 'User-agent: ' . $agent . "\n";
			if ( empty( $all_rules ) ) {
				$report .= "-\n";
			} else {
				foreach ( $all_rules as $rule ) {
					$report .= $rule . "\n";
				}
			}
		}
		return $report;
	}


	/**
	 * Convert a URL into a safe regex pattern.
	 *
	 * @param  string $url     The URL to convert.
	 * @return string $pattern A regex-safe pattern.
	 */
	public function url_to_pattern( $url ) {
		$escaped_specials = preg_quote( $url, '\\' );
		$escaped_slashes  = str_replace( '/', '\/', $escaped_specials );
		$pattern          = '/^' . $escaped_slashes . '/i';
		return $pattern;
	}


	/**
	 * Extract rule groups from robots.txt.
	 *
	 * The robots.txt contents is parsed line by line and rules are grouped by their user agent. If
	 * multiple groups for the same user agent are found they are grouped together in the order they
	 * are found. This means the first relevant rule specified in the file wins. This complies with
	 * how bots are supposed to read the robots.txt.
	 *
	 * @param string $robots_txt Robots.txt contents.
	 */
	private function extract_rule_groups( $robots_txt ) {
		$groups = array();

		$lines      = explode( "\r\n", $robots_txt );
		$user_agent = '';

		foreach ( $lines as $line ) {
			if ( ! trim( $line ) || preg_match( '/^#.*$/', $line ) ) {
				// Skip empty lines and comments.
				continue;

			} elseif ( preg_match( '/^\s*User-agent: (.*)/i', $line, $agent_match ) ) {
				// Line declares a user agent.
				$user_agent = $agent_match[1];

			} elseif ( preg_match( '/^\s*(Allow|Disallow):(.*)/i', $line, $rule_match ) && isset( $user_agent ) ) {
				// Line declares a rule.
				$groups[ $user_agent ][] = array(
					'directive' => $rule_match[1],
					'path'      => trim( $rule_match[2] ),
				);
			}
		}
		return $groups;
	}


	/**
	 * Get the path part of a URL.
	 *
	 * @param string $url The URL to process.
	 */
	private function get_url_path( $url ) {
		// Get passed URL path.
		$url_parts = wp_parse_url( $url ); // Returns no path for root (/).
		$url_path  = isset( $url_parts['path'] ) ? $url_parts['path'] : '/';
		return $url_path;
	}


	/**
	 * Get the rules matching a bot.
	 *
	 * @param string $bot The bot to match to user agent groups.
	 */
	private function get_rules_matching_bot( $bot ) {
		$rules = array();
		if ( isset( $this->groups['*'] ) ) {
			// Loop the groups to maintain user agent order.
			foreach ( $this->groups as $key => $user_agent_rules ) {
				if ( '*' === $key || $bot === $key ) {
					foreach ( $user_agent_rules as $key => $rule ) {
						$rules[] = $rule;
					}
				}
			}
		} elseif ( isset( $this->groups[ $bot ] ) ) {
			$rules = $this->groups[ $bot ];
		}
		return $rules;
	}
}
