<?php
namespace BigupWeb\Bigup_Seo;

/**
 * Plugin Name: Bigup Web: Bigup SEO
 * Plugin URI: https://jeffersonreal.uk
 * Description: Bigup your SEO game.
 * Version: 0.1
 * Author: Jefferson Real
 * Author URI: https://jeffersonreal.uk
 * License: GPL2
 *
 * @package bigup-seo
 * @version 0.1
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright (c) 2023, Jefferson Real
 * @license GPL3+
 * @link https://jeffersonreal.uk
 */

// Set global constants.
define( 'BIGUPWEB_NAMESPACE', 'Lonewolf' );
define( 'BIGUPSEO_DEBUG', defined( 'WP_DEBUG' ) && WP_DEBUG === true );
define( 'BIGUPSEO_PATH', trailingslashit( __DIR__ ) );
define( 'BIGUPSEO_URL', trailingslashit( get_site_url( null, strstr( __DIR__, '/wp-content/' ) ) ) );

// Setup PHP namespace.
require_once BIGUPSEO_PATH . 'classes/autoload.php';

// Setup the plugin.
$Init = new Init();
$Init->setup();
