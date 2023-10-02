<?php

declare(strict_types=1);

/**
 * Used to set up and fix common variables and include
 * the NattiPress procedural and class library.
 *
 * Allows for some configuration in wp-config.php (see default-constants.php)
 *
 * @package NattiPress
 */

/**
 * Stores the location of the NattiPress directory of functions, classes, and core content.
 *
 * @since 1.0.0
 */
define( 'NPSET', 'np_settings' );

/**
 * Version information for the current NattiPress release.
 *
 * These can't be directly globalized in version.php. When updating,
 * include version.php from another installation and don't override
 * these values if already set.
 *
 * @global string $np_version             The NattiPress version string.
 * @global int    $np_db_version          NattiPress database version.
 * @global string $tinymce_version        TinyMCE version.
 * @global string $required_php_version   The required PHP version string.
 * @global string $required_mysql_version The required MySQL version string.
 * @global string $np_local_package       Locale code of the package.
 */
global $np_version, $np_db_version, $tinymce_version, $required_php_version, $required_mysql_version, $np_local_package, $np_actions, $np_filters, $np_app;
require ABSPATH . NPSET . '/version.php';
require ABSPATH . NPSET . '/load.php';
require_once ABSPATH . NPSET . '/np-functions.php';

// Check for the required PHP version and for the MySQL extension or a database drop-in.
np_check_php_versions();

// Include files required for initialization.
require ABSPATH . NPSET . '/default-constants.php';
require ABSPATH . NATTICORE . '/global-variables.php';

/**
 * If not already configured, `$blog_id` will default to 1 in a single site
 * configuration. In multisite, it will be overridden by default in ms-settings.php.
 *
 * @global int $blog_id
 * @since 1.0.0
 */
global $blog_id;

// Set initial default constants including NP_MEMORY_LIMIT, NP_MAX_MEMORY_LIMIT, NP_DEBUG, SCRIPT_DEBUG, NP_THEME_DIR and NP_CACHE.
np_initial_constants();

// Register the shutdown handler for fatal errors as soon as possible.
// np_register_fatal_error_handler();

// WordPress calculates offsets from UTC.
// phpcs:ignore WordPress.DateTime.RestrictedFunctions.timezone_change_date_default_timezone_set
date_default_timezone_set( 'UTC' );

// Standardize $_SERVER variables across setups.
np_fix_server_vars();

// Check if the site is in maintenance mode.
// np_maintenance();

// Start loading timer.
timer_start();

require_once ABSPATH . NPSET . '/theme.php';