<?php
/**
 * NattiPress Version
 *
 * Contains version information for the current NattiPress release.
 *
 * @package NattiPress
 * @since 1.2.0
 */

/**
 * The NattiPress version string.
 *
 * Holds the current version number for NattiPress core. Used to bust caches
 * and to enable development mode for scripts when running from the /src directory.
 *
 * @global string $np_version
 */
$np_version = '1.0.0';

/**
 * Holds the NattiPress DB revision, increments when changes are made to the NattiPress DB schema.
 *
 * @global int $np_db_version
 */
$np_db_version = 53496;

/**
 * Holds the TinyMCE version.
 *
 * @global string $tinymce_version
 */
$tinymce_version = '49110-20201110';

/**
 * Holds the required PHP version.
 *
 * @global string $required_php_version
 */
$required_php_version = '9.0.2';

/**
 * Holds the required MySQL version.
 *
 * @global string $required_mysql_version
 */
$required_mysql_version = '5.0';
