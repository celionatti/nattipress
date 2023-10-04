<?php

declare(strict_types=1);

/**
 * These functions are needed to load NattiPress.
 *
 * @package NattiPress
 */

/**
 * Return the HTTP protocol sent by the server.
 *
 * @since 4.4.0
 *
 * @return string The HTTP protocol. Default: HTTP/1.0.
 */
function np_get_server_protocol()
{
	$protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : '';
	if (!in_array($protocol, array('HTTP/1.1', 'HTTP/2', 'HTTP/2.0', 'HTTP/3'), true)) {
		$protocol = 'HTTP/1.0';
	}
	return $protocol;
}

/**
 * Fix `$_SERVER` variables for various setups.
 *
 * @since 1.0.0
 * @access private
 *
 * @global string $PHP_SELF The filename of the currently executing script,
 *                          relative to the document root.
 */
function np_fix_server_vars()
{
	global $PHP_SELF;

	$default_server_values = array(
		'SERVER_SOFTWARE' => '',
		'REQUEST_URI'     => '',
	);

	$_SERVER = array_merge($default_server_values, $_SERVER);

	// Fix for IIS when running with PHP ISAPI.
	if (empty($_SERVER['REQUEST_URI']) || ('cgi-fcgi' !== PHP_SAPI && preg_match('/^Microsoft-IIS\//', $_SERVER['SERVER_SOFTWARE']))) {

		if (isset($_SERVER['HTTP_X_ORIGINAL_URL'])) {
			// IIS Mod-Rewrite.
			$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
		} elseif (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
			// IIS Isapi_Rewrite.
			$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
		} else {
			// Use ORIG_PATH_INFO if there is no PATH_INFO.
			if (!isset($_SERVER['PATH_INFO']) && isset($_SERVER['ORIG_PATH_INFO'])) {
				$_SERVER['PATH_INFO'] = $_SERVER['ORIG_PATH_INFO'];
			}

			// Some IIS + PHP configurations put the script-name in the path-info (no need to append it twice).
			if (isset($_SERVER['PATH_INFO'])) {
				if ($_SERVER['PATH_INFO'] == $_SERVER['SCRIPT_NAME']) {
					$_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'];
				} else {
					$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];
				}
			}

			// Append the query string if it exists and isn't null.
			if (!empty($_SERVER['QUERY_STRING'])) {
				$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
			}
		}
	}

	// Fix for PHP as CGI hosts that set SCRIPT_FILENAME to something ending in php.cgi for all requests.
	if (isset($_SERVER['SCRIPT_FILENAME']) && (strpos($_SERVER['SCRIPT_FILENAME'], 'php.cgi') == strlen($_SERVER['SCRIPT_FILENAME']) - 7)) {
		$_SERVER['SCRIPT_FILENAME'] = $_SERVER['PATH_TRANSLATED'];
	}

	// Fix for Dreamhost and other PHP as CGI hosts.
	if (isset($_SERVER['SCRIPT_NAME']) && (strpos($_SERVER['SCRIPT_NAME'], 'php.cgi') !== false)) {
		unset($_SERVER['PATH_INFO']);
	}

	// Fix empty PHP_SELF.
	$PHP_SELF = $_SERVER['PHP_SELF'];
	if (empty($PHP_SELF)) {
		$_SERVER['PHP_SELF'] = preg_replace('/(\?.*)?$/', '', $_SERVER['REQUEST_URI']);
		$PHP_SELF            = $_SERVER['PHP_SELF'];
	}

	np_populate_basic_auth_from_authorization_header();
}

/**
 * Populates the Basic Auth server details from the Authorization header.
 *
 * Some servers running in CGI or FastCGI mode don't pass the Authorization
 * header on to WordPress.  If it's been rewritten to the `HTTP_AUTHORIZATION` header,
 * fill in the proper $_SERVER variables instead.
 *
 * @since 1.0.0
 */
function np_populate_basic_auth_from_authorization_header()
{
	// If we don't have anything to pull from, return early.
	if (!isset($_SERVER['HTTP_AUTHORIZATION']) && !isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
		return;
	}

	// If either PHP_AUTH key is already set, do nothing.
	if (isset($_SERVER['PHP_AUTH_USER']) || isset($_SERVER['PHP_AUTH_PW'])) {
		return;
	}

	// From our prior conditional, one of these must be set.
	$header = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];

	// Test to make sure the pattern matches expected.
	if (!preg_match('%^Basic [a-z\d/+]*={0,2}$%i', $header)) {
		return;
	}

	// Removing `Basic ` the token would start six characters in.
	$token    = substr($header, 6);
	$userpass = base64_decode($token);

	list($user, $pass) = explode(':', $userpass);

	// Now shove them in the proper keys where we're expecting later on.
	$_SERVER['PHP_AUTH_USER'] = $user;
	$_SERVER['PHP_AUTH_PW']   = $pass;
}

/**
 * Check for the required PHP version, and the MySQL extension or
 * a database drop-in.
 *
 * Dies if requirements are not met.
 *
 * @since 3.0.0
 * @access private
 *
 * @global string $required_php_version The required PHP version string.
 * @global string $np_version           The NattiPress version string.
 */
function np_check_php_versions()
{
	global $required_php_version, $np_version;
	$php_version = PHP_VERSION;

	if (version_compare($required_php_version, $php_version, '>')) {
		$protocol = np_get_server_protocol();
		header(sprintf('%s 500 Internal Server Error', $protocol), true, 500);
		header('Content-Type: text/html; charset=utf-8');
		printf(
			'Your server is running PHP version %1$s but NattiPress %2$s requires at least %3$s.',
			$php_version,
			$np_version,
			$required_php_version
		);
		exit(1);
	}
}

/**
 * Retrieves the current environment type.
 *
 * The type can be set via the `NP_ENVIRONMENT_TYPE` global system variable,
 * or a constant of the same name.
 *
 * Possible values are 'local', 'development', 'staging', and 'production'.
 * If not set, the type defaults to 'production'.
 *
 * @since 5.5.0
 * @since 5.5.1 Added the 'local' type.
 * @since 5.5.1 Removed the ability to alter the list of types.
 *
 * @return string The current environment type.
 */
function np_get_environment_type()
{
	static $current_env = '';

	if (!defined('NP_RUN_CORE_TESTS') && $current_env) {
		return $current_env;
	}

	$np_environments = array(
		'local',
		'development',
		'staging',
		'production',
	);

	// Add a note about the deprecated NP_ENVIRONMENT_TYPES constant.
	if (defined('NP_ENVIRONMENT_TYPES')) {
		$message = sprintf('The %s constant is no longer supported.', 'NP_ENVIRONMENT_TYPES');

		np_die(
			'define()',
			$message,
			'5.5.1'
		);
	}

	// Check if the environment variable has been set, if `getenv` is available on the system.
	if (function_exists('getenv')) {
		$has_env = getenv('NP_ENVIRONMENT_TYPE');
		if (false !== $has_env) {
			$current_env = $has_env;
		}
	}

	// Fetch the environment from a constant, this overrides the global system variable.
	if (defined('NP_ENVIRONMENT_TYPE') && NP_ENVIRONMENT_TYPE) {
		$current_env = NP_ENVIRONMENT_TYPE;
	}

	// Make sure the environment is an allowed one, and not accidentally set to an invalid value.
	if (!in_array($current_env, $np_environments, true)) {
		$current_env = 'production';
	}

	return $current_env;
}

/**
 * Don't load all of WordPress when handling a favicon.ico request.
 *
 * Instead, send the headers for a zero-length favicon and bail.
 *
 * @since 3.0.0
 * @deprecated 5.4.0 Deprecated in favor of do_favicon().
 */
function np_favicon_request()
{
	if ('/favicon.ico' === $_SERVER['REQUEST_URI']) {
		header('Content-Type: image/vnd.microsoft.icon');
		exit;
	}
}

/**
 * Determines if SSL is used.
 *
 * @since 2.6.0
 * @since 4.6.0 Moved from functions.php to load.php.
 *
 * @return bool True if SSL, otherwise false.
 */
function is_ssl()
{
	if (isset($_SERVER['HTTPS'])) {
		if ('on' === strtolower($_SERVER['HTTPS'])) {
			return true;
		}

		if ('1' == $_SERVER['HTTPS']) {
			return true;
		}
	} elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
		return true;
	}
	return false;
}

/**
 * Get the time elapsed so far during this PHP script.
 *
 * Uses REQUEST_TIME_FLOAT that appeared in PHP 5.4.0.
 *
 * @since 1.0.0
 *
 * @return float Seconds since the PHP script started.
 */
function timer_float()
{
	return microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
}

/**
 * Start the NattiPress micro-timer.
 *
 * @since 1.0.0
 * @access private
 *
 * @global float $timestart Unix timestamp set at the beginning of the page load.
 * @see timer_stop()
 *
 * @return bool Always returns true.
 */
function timer_start()
{
	global $timestart;
	$timestart = microtime(true);
	return true;
}

/**
 * Converts a shorthand byte value to an integer byte value.
 *
 * @since 2.3.0
 * @since 4.6.0 Moved from media.php to load.php.
 *
 * @link https://www.php.net/manual/en/function.ini-get.php
 * @link https://www.php.net/manual/en/faq.using.php#faq.using.shorthandbytes
 *
 * @param string $value A (PHP ini) byte value, either shorthand or ordinary.
 * @return int An integer byte value.
 */
function np_convert_hr_to_bytes( $value ) {
	$value = strtolower( trim( $value ) );
	$bytes = (int) $value;

	if ( false !== strpos( $value, 'g' ) ) {
		$bytes *= GB_IN_BYTES;
	} elseif ( false !== strpos( $value, 'm' ) ) {
		$bytes *= MB_IN_BYTES;
	} elseif ( false !== strpos( $value, 'k' ) ) {
		$bytes *= KB_IN_BYTES;
	}

	// Deal with large (float) values which run into the maximum integer size.
	return min( $bytes, PHP_INT_MAX );
}

/**
 * Determines whether a PHP ini value is changeable at runtime.
 *
 * @since 4.6.0
 *
 * @link https://www.php.net/manual/en/function.ini-get-all.php
 *
 * @param string $setting The name of the ini setting to check.
 * @return bool True if the value is changeable at runtime. False otherwise.
 */
function np_is_ini_value_changeable( $setting ) {
	static $ini_all;

	if ( ! isset( $ini_all ) ) {
		$ini_all = false;
		// Sometimes `ini_get_all()` is disabled via the `disable_functions` option for "security purposes".
		if ( function_exists( 'ini_get_all' ) ) {
			$ini_all = ini_get_all();
		}
	}

	// Bit operator to workaround https://bugs.php.net/bug.php?id=44936 which changes access level to 63 in PHP 5.2.6 - 5.2.17.
	if ( isset( $ini_all[ $setting ]['access'] ) && ( INI_ALL === ( $ini_all[ $setting ]['access'] & 7 ) || INI_USER === ( $ini_all[ $setting ]['access'] & 7 ) ) ) {
		return true;
	}

	// If we were unable to retrieve the details, fail gracefully to assume it's changeable.
	if ( ! is_array( $ini_all ) ) {
		return true;
	}

	return false;
}
