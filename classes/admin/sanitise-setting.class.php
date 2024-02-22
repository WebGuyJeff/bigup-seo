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
}
