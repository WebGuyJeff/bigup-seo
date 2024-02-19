<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Plugin Name: Bigup Web: Bigup SEO
 * Plugin URI: https://jeffersonreal.uk
 * Description: Bigup your SEO game.
 * Version: 0.1.1
 * Author: Jefferson Real
 * Author URI: https://jeffersonreal.uk
 * License: GPL3+
 *
 * @package bigup-seo
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2024, Jefferson Real
 * @license GPL3+
 * @link https://jeffersonreal.uk
 */

$enable_debug = false;

// Set global constants.
define( 'BIGUPSEO_DEBUG', $enable_debug );
define( 'BIGUPSEO_PATH', trailingslashit( __DIR__ ) );
define( 'BIGUPSEO_URL', trailingslashit( get_site_url( null, strstr( __DIR__, '/wp-content/' ) ) ) );

// Register namespaced autoloader.
$namespace = 'BigupWeb\\Bigup_Seo\\';
$root      = BIGUPSEO_PATH . 'classes/';
require_once $root . 'autoload.php';

// Setup the plugin.
$Init = new Init();
$Init->setup();
