<?php

declare(strict_types=1);

/**
 * The base configuration for NattiPress
 *
 * The np_config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "np_config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://nattipress.org/documentation/article/editing-np-config-php/
 *
 * @package NattiPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for NattiPress */
define( 'DB_NAME', 'database_name_here' );

/** Database username */
define( 'DB_USER', 'username_here' );

/** Database password */
define( 'DB_PASSWORD', 'password_here' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.nattipress.org/secret-key/1.1/salt/ NattiPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'put your unique phrase here' );
define( 'SECURE_AUTH_KEY',  'put your unique phrase here' );
define( 'LOGGED_IN_KEY',    'put your unique phrase here' );
define( 'NONCE_KEY',        'put your unique phrase here' );
define( 'AUTH_SALT',        'put your unique phrase here' );
define( 'SECURE_AUTH_SALT', 'put your unique phrase here' );
define( 'LOGGED_IN_SALT',   'put your unique phrase here' );
define( 'NONCE_SALT',       'put your unique phrase here' );

/**#@-*/

/**
 * NattiPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'np_';

/**
 * For developers: NattiPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use NP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://nattipress.org/documentation/article/debugging-in-nattipress/
 */
define( 'NP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */

define('NP_ENVIRONMENT_TYPE', 'development');

define( 'NP_DEBUG_DISPLAY', null );

define( 'NP_DEBUG_LOG', true );

define( 'SCRIPT_DEBUG', true );

define( 'NP_THEMES', "" );

define( 'NP_THEMES_URL', "" );

define( 'NP_ROOT', "" );


/* That's all, stop editing! Happy publishing. */

/** Absolute path to the NattiPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname(__DIR__) . '/' );
}

/** Sets up NattiPress vars and included files. */
require_once ABSPATH . 'np_settings/np-settings.php';
