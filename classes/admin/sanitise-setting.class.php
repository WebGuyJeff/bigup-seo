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
	 * Sanitise robots.txt contents.
	 */
	public static function robotstxt( $robots ) {
		$robots_string = (string) $robots;
		$clean_multiline = sanitize_textarea_field( $robots_string );
		return $clean_multiline;
	}
}
