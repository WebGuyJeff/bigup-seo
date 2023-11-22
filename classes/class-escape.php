<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Escape filters and args for use with wp_kses().
 *
 * @package bigup-seo
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2023, Jefferson Real
 * @license GPL3+
 * @link https://jeffersonreal.uk
 */
class Escape {


	/**
	 * Paths to json data.
	 */
	private const PATHS = array(
		'html_global' => BIGUPSEO_PATH . 'data/htmlGlobalAttributes.json',
		'html_head'   => BIGUPSEO_PATH . 'data/htmlHeadAttributes.json',
	);


	/**
	 * Get global HTML attributes from JSON.
	 */
	private static function get_global_html_attributes() {
		$json              = Util::get_contents( self::PATHS['html_global'] );
		$global_attributes = json_decode( $json, true );
		return $global_attributes;
	}


	/**
	 * Get HTML attributes from JSON and return them as kses-args.
	 *
	 * @param string $path Path to JSON file.
	 */
	private static function get_html_attributes_as_kses_args( $path ) {
		$json              = Util::get_contents( $path );
		$html_tags         = json_decode( $json, true );
		$global_attributes = self::get_global_html_attributes();
		$args              = array();
		foreach ( $html_tags as $html_tag => $html_attributes ) {
			$allowed = array_merge( $html_attributes, $global_attributes );
			$tag     = strtolower( $html_tag );
			foreach ( $allowed as $html_attribute ) {
				$attribute                  = strtolower( $html_attribute );
				$args[ $tag ][ $attribute ] = true;
			}
		}
		return $args;
	}


	/**
	 * Escape and print HTML head markup.
	 *
	 * @param string $markup HTML markup to be escaped.
	 */
	public static function head( $markup ) {
		$args = self::get_html_attributes_as_kses_args( self::PATHS['html_head'] );
		echo wp_kses( $markup, $args );
	}
}
