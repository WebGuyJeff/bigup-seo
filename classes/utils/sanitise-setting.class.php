<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Sanitise settings.
 *
 * @package bigup-seo
 */
class Sanitise_Setting {


	/**
	 * Sanitise a checkbox.
	 */
	public static function checkbox( $checkbox ) {
		$bool_checkbox = (bool) $checkbox;
		return $bool_checkbox;
	}


	/**
	 * Sanitises a hex colour value.
	 */
	public static function hex_colour( $hex ) {
		$hex = isset( $hex ) && 0 < strlen( $hex ) ? trim( $hex ) : '';
		$hex = str_replace( '#', '', $hex );

		// If the string is 3 characters long then use it in pairs.
		if ( 3 === strlen( $hex ) ) {
			$hex = substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) . substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) . substr( $hex, 2, 1 ) . substr( $hex, 2, 1 );
		}

		$substr = array();
		for ( $i = 0; $i <= 5; $i++ ) {
			$default    = ( 0 == $i ) ? '' : ( $substr[$i-1] );
			$substr[$i] = substr( $hex, $i, 1 );
			$substr[$i] = ( false === $substr[$i] || ! ctype_xdigit( $substr[$i] ) ) ? $default : $substr[$i];
		}
		$hex = implode( '', $substr );

		if ( 6 === strlen( $hex ) ) {
			return '#' . $hex;
		} else {
			return '';
		}
	}


	/**
	 * Sanitise robots.txt contents.
	 */
	public static function robotstxt( $robots ) {
		$robots_string = (string) $robots;
		$clean_multiline = sanitize_textarea_field( $robots_string );
		return $clean_multiline;
	}
}
