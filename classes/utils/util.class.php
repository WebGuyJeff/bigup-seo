<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Utility functions for use throughout the plugin.
 *
 * @package bigup-seo
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2024, Jefferson Real
 * @license GPL3+
 * @link https://jeffersonreal.uk
 */

class Util {


	/**
	 * Retrieve file contents the 'WordPress way'.
	 *
	 * @param string $path File system path.
	 */
	public static function get_contents( $path ) {
		include_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
		if ( ! class_exists( 'WP_Filesystem_Direct' ) ) {
			return false;
		}
		$wp_filesystem = new \WP_Filesystem_Direct( null );
		$string        = $wp_filesystem->get_contents( $path );
		return $string;
	}


	/**
	 * Check all PHP current theme files for a containing string.
	 *
	 * @param string $needle The string to search for.
	 */
	public static function theme_files_contain( $needle ) {
		$theme_dir     = get_template_directory() . '/';
		$theme_files   = array();
		$matched_files = array();
		foreach ( glob( $theme_dir . '*.php' ) as $file ) {
			$theme_files[] = $file;
		}
		foreach ( $theme_files as $file ) {
			$file_contents = file_get_contents( $file );
			if ( strpos( $file_contents, $needle ) ) {
				$relative_path = str_replace( $theme_dir, '', $file );
				array_push( $matched_files, $relative_path );
			}
		}
		if ( count( $matched_files ) > 0 ) {
			return $matched_files;
		} else {
			return false;
		}
	}


	/**
	 * Include a template with passed variables.
	 *
	 * In the template file you can explicity declare the passed vars by desructuring like this:
	 * `[ 'my_var' => $my_var ] = $passed_variables;`
	 *
	 * @param string $template_path The path of the file to be included.
	 * @param string $variables The variables to pass.
	 */
	public static function include_with_vars( $template_path, $passed_variables = array() ) {
		// Wrap '$passed_variables' so it can be explicitly desructured in the template.
		$variables = array( $passed_variables );
		$output    = NULL;
		if ( file_exists( $template_path ) ) {
			// Extract variables to local namespace.
			extract( $variables );
			// Start output buffering.
			ob_start();
			// Include the template file.
			include $template_path;
			// End buffering and return its contents.
			$output = ob_get_clean();
		}
		return $output;
	}
}
