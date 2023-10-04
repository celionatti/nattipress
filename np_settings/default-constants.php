<?php


declare(strict_types=1);


/**
 * Defines constants and global variables that can be overridden, generally in np-config.php.
 *
 * @package NattiPress
 */

/**
 * Defines initial NattiPress constants.
 *
 * @see np_debug_mode()
 *
 * @since 3.0.0
 *
 * @global int    $blog_id    The current site ID.
 * @global string $np_version The NattiPress version string.
 */
function np_initial_constants() {
	global $blog_id, $np_version, $npdb;

	/**#@+
	 * Constants for expressing human-readable data sizes in their respective number of bytes.
	 *
	 * @since 4.4.0
	 * @since 6.0.0 `PB_IN_BYTES`, `EB_IN_BYTES`, `ZB_IN_BYTES`, and `YB_IN_BYTES` were added.
	 */
	define( 'KB_IN_BYTES', 1024 );
	define( 'MB_IN_BYTES', 1024 * KB_IN_BYTES );
	define( 'GB_IN_BYTES', 1024 * MB_IN_BYTES );
	define( 'TB_IN_BYTES', 1024 * GB_IN_BYTES );
	define( 'PB_IN_BYTES', 1024 * TB_IN_BYTES );
	define( 'EB_IN_BYTES', 1024 * PB_IN_BYTES );
	define( 'ZB_IN_BYTES', 1024 * EB_IN_BYTES );
	define( 'YB_IN_BYTES', 1024 * ZB_IN_BYTES );
	/**#@-*/

	// Start of run timestamp.
	if ( ! defined( 'NP_START_TIMESTAMP' ) ) {
		define( 'NP_START_TIMESTAMP', microtime( true ) );
	}

	$current_limit     = ini_get( 'memory_limit' );
	$current_limit_int = np_convert_hr_to_bytes( $current_limit );

	// Define memory limits.
	if ( ! defined( 'NP_MEMORY_LIMIT' ) ) {
		if ( false === np_is_ini_value_changeable( 'memory_limit' ) ) {
			define( 'NP_MEMORY_LIMIT', $current_limit );
		} else {
			define( 'NP_MEMORY_LIMIT', '40M' );
		}
	}

	if ( ! defined( 'NP_MAX_MEMORY_LIMIT' ) ) {
		if ( false === np_is_ini_value_changeable( 'memory_limit' ) ) {
			define( 'NP_MAX_MEMORY_LIMIT', $current_limit );
		} elseif ( -1 === $current_limit_int || $current_limit_int > 268435456 /* = 256M */ ) {
			define( 'NP_MAX_MEMORY_LIMIT', $current_limit );
		} else {
			define( 'NP_MAX_MEMORY_LIMIT', '256M' );
		}
	}

	// Set memory limits.
	$wp_limit_int = np_convert_hr_to_bytes( NP_MEMORY_LIMIT );
	if ( -1 !== $current_limit_int && ( -1 === $wp_limit_int || $wp_limit_int > $current_limit_int ) ) {
		ini_set( 'memory_limit', NP_MEMORY_LIMIT );
	}

	if ( ! isset( $blog_id ) ) {
		$blog_id = 1;
	}

	if ( ! defined( 'NP_SITEURL' ) ) {
		define( 'NP_SITEURL', getenv("SITEURL") ); // No trailing slash, full paths only - NP_SITEURL is defined further down.
	}

	if ( ! defined( 'NP_THEMES' ) ) {
		define( 'NP_THEMES', ABSPATH . 'themes' ); // No trailing slash, full paths only - NP_THEMES is defined further down.
	}

	// Add define( 'NP_DEBUG', true ); to np_config.php to enable display of notices during development.
	if ( ! defined( 'NP_DEBUG' ) ) {
		if ( 'development' === np_get_environment_type() ) {
			define( 'NP_DEBUG', true );
		} else {
			define( 'NP_DEBUG', false );
		}
	}

	// Add define( 'NP_DEBUG_DISPLAY', null ); to np-config.php to use the globally configured setting
	// for 'display_errors' and not force errors to be displayed. Use false to force 'display_errors' off.
	if ( ! defined( 'NP_DEBUG_DISPLAY' ) ) {
		define( 'NP_DEBUG_DISPLAY', true );
	}

	// Add define( 'NP_DEBUG_LOG', true ); to enable error logging to logs/debug.log.
	if ( ! defined( 'NP_DEBUG_LOG' ) ) {
		define( 'NP_DEBUG_LOG', false );
	}

	if ( ! defined( 'NP_CACHE' ) ) {
		define( 'NP_CACHE', false );
	}

	// Add define( 'SCRIPT_DEBUG', true ); to np_config.php to enable loading of non-minified,
	// non-concatenated scripts and stylesheets.
	if ( ! defined( 'SCRIPT_DEBUG' ) ) {
		if ( ! empty( $np_version ) ) {
			$develop_src = false !== strpos( $np_version, '-assets' );
		} else {
			$develop_src = false;
		}

		define( 'SCRIPT_DEBUG', $develop_src );
	}

	/**
	 * Private
	 */
	if ( ! defined( 'MEDIA_TRASH' ) ) {
		define( 'MEDIA_TRASH', false );
	}

	if ( ! defined( 'SHORTINIT' ) ) {
		define( 'SHORTINIT', false );
	}

	// Constants for features added to NP that should short-circuit their plugin implementations.
	define( 'NP_FEATURE_BETTER_PASSWORDS', true );

	/**#@+
	 * Constants for expressing human-readable intervals
	 * in their respective number of seconds.
	 *
	 * Please note that these values are approximate and are provided for convenience.
	 * For example, MONTH_IN_SECONDS wrongly assumes every month has 30 days and
	 * YEAR_IN_SECONDS does not take leap years into account.
	 *
	 * If you need more accuracy please consider using the DateTime class (https://www.php.net/manual/en/class.datetime.php).
	 *
	 * @since 3.5.0
	 * @since 4.4.0 Introduced `MONTH_IN_SECONDS`.
	 */
	define( 'MINUTE_IN_SECONDS', 60 );
	define( 'HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS );
	define( 'DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS );
	define( 'WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS );
	define( 'MONTH_IN_SECONDS', 30 * DAY_IN_SECONDS );
	define( 'YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS );
	/**#@-*/
}

/**
 * Defines plugin directory WordPress constants.
 *
 * Defines must-use plugin directory constants, which may be overridden in the sunrise.php drop-in.
 *
 * @since 3.0.0
 */
function np_plugin_directory_constants() {
	/**
	 * Allows for the plugins directory to be moved from the default location.
	 *
	 * @since 2.6.0
	 */
	if ( ! defined( 'NP_THEMES_DIR' ) ) {
		define( 'NP_THEMES_DIR', NP_THEMES . '/plugins' ); // Full path, no trailing slash.
	}

	/**
	 * Allows for the plugins directory to be moved from the default location.
	 *
	 * @since 2.6.0
	 */
	if ( ! defined( 'NP_THEMES_URL' ) ) {
		define( 'NP_THEMES_URL', NP_THEMES_URL . '/plugins' ); // Full URL, no trailing slash.
	}

	/**
	 * Allows for the plugins directory to be moved from the default location.
	 *
	 * @since 2.1.0
	 * @deprecated
	 */
	if ( ! defined( 'THEMEDIR' ) ) {
		define( 'THEMEDIR', '/themes' ); // Relative to ABSPATH. For back compat.
	}
}

/**
 * Defines functionality-related WordPress constants.
 *
 * @since 3.0.0
 */
function np_functionality_constants() {
	/**
	 * @since 2.5.0
	 */
	if ( ! defined( 'AUTOSAVE_INTERVAL' ) ) {
		define( 'AUTOSAVE_INTERVAL', MINUTE_IN_SECONDS );
	}

	/**
	 * @since 2.9.0
	 */
	if ( ! defined( 'EMPTY_TRASH_DAYS' ) ) {
		define( 'EMPTY_TRASH_DAYS', 30 );
	}

	if ( ! defined( 'NP_POST_REVISIONS' ) ) {
		define( 'NP_POST_REVISIONS', true );
	}

	/**
	 * @since 3.3.0
	 */
	if ( ! defined( 'NP_CRON_LOCK_TIMEOUT' ) ) {
		define( 'NP_CRON_LOCK_TIMEOUT', MINUTE_IN_SECONDS );
	}
}

/**
 * Defines templating-related WordPress constants.
 *
 * @since 3.0.0
 */
function np_templating_constants() {
	/**
	 * Filesystem path to the current active template directory.
	 *
	 * @since 1.0.0
	 */
	// define( 'TEMPLATEPATH', get_template_directory() );

	/**
	 * Filesystem path to the current active template stylesheet directory.
	 *
	 * @since 1.0.0
	 */
	// define( 'STYLESHEETPATH', get_stylesheet_directory() );

	/**
	 * Slug of the default theme for this installation.
	 * Used as the default theme when installing new sites.
	 * It will be used as the fallback if the active theme doesn't exist.
	 *
	 * @since 3.0.0
	 *
	 * @see NP_Theme::get_core_default_theme()
	 */
	if ( ! defined( 'NP_DEFAULT_THEME' ) ) {
		define( 'NP_DEFAULT_THEME', 'officialnatti' );
	}

}
