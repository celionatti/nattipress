<?php

declare(strict_types=1);

dd("NP Admin");

/**
 * Retrieves and creates the np_config.php file.
 *
 * The permissions for the base directory must allow for writing files in order
 * for the np_config.php to be created using this page.
 *
 * @package NattiPress
 * @subpackage Administration
 */

/**
 * We are installing.
 */
define( 'NP_INSTALLING', true );

/**
 * We are blissfully unaware of anything.
 */
define( 'NP_SETUP_CONFIG', true );

/**
 * Disable error reporting
 *
 * Set this to error_reporting( -1 ) for debugging
 */
error_reporting( 0 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

dd(ABSPATH . 'settings.php');

require ABSPATH . 'settings.php';