<?php

declare(strict_types=1);

use NattiPress\NattiCore\NattiPress;

function np()
{
	$np = new NattiPress();
	dd($np);

	// $np->run();
}

/**
 * Guesses the URL for the site.
 *
 * Will remove wp-admin links to retrieve only return URLs not in the wp-admin
 * directory.
 *
 * @since 2.6.0
 *
 * @return string The guessed URL.
 */
function np_guess_url() {
	if ( defined( 'NP_SITEURL' ) && '' !== NP_SITEURL ) {
		$url = NP_SITEURL;
	} else {
		$abspath_fix         = str_replace( '\\', '/', ABSPATH );
		$script_filename_dir = dirname( $_SERVER['SCRIPT_FILENAME'] );

		// The request is for the admin.
		if ( strpos( $_SERVER['REQUEST_URI'], 'np-admin' ) !== false || strpos( $_SERVER['REQUEST_URI'], 'np-login.php' ) !== false ) {
			$path = preg_replace( '#/(np-admin/?.*|np-login\.php.*)#i', '', $_SERVER['REQUEST_URI'] );

			// The request is for a file in ABSPATH.
		} elseif ( $script_filename_dir . '/' === $abspath_fix ) {
			// Strip off any file/query params in the path.
			$path = preg_replace( '#/[^/]*$#i', '', $_SERVER['PHP_SELF'] );

		} else {
			if ( false !== strpos( $_SERVER['SCRIPT_FILENAME'], $abspath_fix ) ) {
				// Request is hitting a file inside ABSPATH.
				$directory = str_replace( ABSPATH, '', $script_filename_dir );
				// Strip off the subdirectory, and any file/query params.
				$path = preg_replace( '#/' . preg_quote( $directory, '#' ) . '/[^/]*$#i', '', $_SERVER['REQUEST_URI'] );
			} elseif ( false !== strpos( $abspath_fix, $script_filename_dir ) ) {
				// Request is hitting a file above ABSPATH.
				$subdirectory = substr( $abspath_fix, strpos( $abspath_fix, $script_filename_dir ) + strlen( $script_filename_dir ) );
				// Strip off any file/query params from the path, appending the subdirectory to the installation.
				$path = preg_replace( '#/[^/]*$#i', '', $_SERVER['REQUEST_URI'] ) . $subdirectory;
			} else {
				$path = $_SERVER['REQUEST_URI'];
			}
		}

		$schema = is_ssl() ? 'https://' : 'http://'; // set_url_scheme() is not defined yet.
		$url    = $schema . $_SERVER['HTTP_HOST'] . $path;
	}

	return rtrim( $url, '/' );
}

/**
 * Check if the option exists in your options store (e.g., an array or database)
 * For demonstration, let's assume we have an array called $options_store
 *
 * @param string $name
 * @param  $default
 * @return void
 */
function get_option(string $name, $default = null) {
    global $npopt;

    if (isset($npopt[$name])) {
        return $npopt[$name];
    } else {
        // Return the default value if provided; otherwise, return null
        return $default;
    }
}

/**
 * Check if the global $npdb object exists (it's available in multisite installations)
 *
 * @return boolean
 */
function is_multisite() {
    global $npdb;
    
    if (is_object($npdb) && property_exists($npdb, 'siteid')) {
        return true; // It's a multisite installation
    } else {
        return false; // It's a single site installation
    }
}


