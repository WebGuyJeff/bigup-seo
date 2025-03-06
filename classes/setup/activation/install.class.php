<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Install.
 *
 * Handle all functionality which needs to be installed on plugin activation.
 *
 * @package bigup-seo
 * @author Jefferson Real <jeff@webguyjeff.com>
 * @copyright Copyright (c) 2024, Jefferson Real
 * @license GPL3+
 * @link https://webguyjeff.com
 */

class Install {

	public const PLUGIN_PATH = BIGUPSEO_PATH . 'plugin-entry.php';


	/**
	 * Setup a new instance.
	 */
	public function __construct() {
		register_activation_hook( self::PLUGIN_PATH, array( $this, 'all' ) );
	}


	/**
	 * Install all plugin requirements callback.
	 *
	 * This should be called by `register_activation_hook()` as a single handler for all plugin
	 * activation tasks.
	 */
	public function all() {
		Meta_Table::create_table();
	}
}
