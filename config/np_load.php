<?php

declare(strict_types=1);

/**
 * Bootstrap file for setting the ABSPATH constant
 * and loading the np_config.php file. The np_config.php
 * file will then load the np-settings.php file, which
 * will then set up the WordPress environment.
 *
 * If the np_config.php file is not found then an error
 * will be displayed asking the visitor to set up the
 * np_config.php file.
 *
 * Will also search for np_config.php in NattiPress' parent
 * directory to allow the NattiPress directory to remain
 * untouched.
 *
 * @package WordPress
 */

/** Define ABSPATH as this file's directory */
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/');
}

/*
 * The error_reporting() function can be disabled in php.ini. On systems where that is the case,
 * it's best to add a dummy function to the np_config.php file, but as this call to the function
 * is run prior to np_config.php loading, it is wrapped in a function_exists() check.
 */
if (function_exists('error_reporting')) {
    /*
	 * Initialize error reporting to a known set of levels.
	 *
	 * This will be adapted in np_debug_mode() located in np-includes/load.php based on NP_DEBUG.
	 * @see https://www.php.net/manual/en/errorfunc.constants.php List of known error levels.
	 */
    error_reporting(E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR);
}

/**
 * 
 */
if (file_exists(ABSPATH . 'config/np_config.php')) {

    /** The config file resides in ABSPATH */
    require_once ABSPATH . 'config/np_config.php';
} elseif (@file_exists(dirname(ABSPATH) . '/np_config.php') && !@file_exists(dirname(ABSPATH) . '/np_settings.php')) {

    /** The config file resides one level above ABSPATH but is not part of another installation */
    require_once dirname(ABSPATH) . '/np_config.php';
} else {

    // A config file doesn't exist.

    define('NPSET', 'np_settings');
    require_once ABSPATH . NPSET . '/load.php';

    // Standardize $_SERVER variables across setups.
    np_fix_server_vars();

    require_once ABSPATH . NPSET . '/functions.php';


    define('NP_PLUGIN_DIR', ABSPATH . 'themes');
    require_once ABSPATH . NPSET . '/version.php';

    np_check_php_versions();
}