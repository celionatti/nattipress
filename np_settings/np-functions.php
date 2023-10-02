<?php

declare(strict_types=1);

use NattiPress\NattiCore\NattiPress;

function np()
{
    $np = new NattiPress();

    $np->run();
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
function np_guess_url()
{
    if (defined('NP_SITEURL') && '' !== NP_SITEURL) {
        $url = NP_SITEURL;
    } else {
        $abspath_fix         = str_replace('\\', '/', ABSPATH);
        $script_filename_dir = dirname($_SERVER['SCRIPT_FILENAME']);

        // The request is for the admin.
        if (strpos($_SERVER['REQUEST_URI'], 'np-admin') !== false || strpos($_SERVER['REQUEST_URI'], 'np-login.php') !== false) {
            $path = preg_replace('#/(np-admin/?.*|np-login\.php.*)#i', '', $_SERVER['REQUEST_URI']);

            // The request is for a file in ABSPATH.
        } elseif ($script_filename_dir . '/' === $abspath_fix) {
            // Strip off any file/query params in the path.
            $path = preg_replace('#/[^/]*$#i', '', $_SERVER['PHP_SELF']);
        } else {
            if (false !== strpos($_SERVER['SCRIPT_FILENAME'], $abspath_fix)) {
                // Request is hitting a file inside ABSPATH.
                $directory = str_replace(ABSPATH, '', $script_filename_dir);
                // Strip off the subdirectory, and any file/query params.
                $path = preg_replace('#/' . preg_quote($directory, '#') . '/[^/]*$#i', '', $_SERVER['REQUEST_URI']);
            } elseif (false !== strpos($abspath_fix, $script_filename_dir)) {
                // Request is hitting a file above ABSPATH.
                $subdirectory = substr($abspath_fix, strpos($abspath_fix, $script_filename_dir) + strlen($script_filename_dir));
                // Strip off any file/query params from the path, appending the subdirectory to the installation.
                $path = preg_replace('#/[^/]*$#i', '', $_SERVER['REQUEST_URI']) . $subdirectory;
            } else {
                $path = $_SERVER['REQUEST_URI'];
            }
        }

        $schema = is_ssl() ? 'https://' : 'http://'; // set_url_scheme() is not defined yet.
        $url    = $schema . $_SERVER['HTTP_HOST'] . $path;
    }

    return rtrim($url, '/');
}

/**
 * Check if the option exists in your options store (e.g., an array or database)
 * For demonstration, let's assume we have an array called $options_store
 *
 * @param string $name
 * @param  $default
 * @return void
 */
function get_option(string $name, $default = null)
{
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
function is_multisite()
{
    global $npdb;

    if (is_object($npdb) && property_exists($npdb, 'siteid')) {
        return true; // It's a multisite installation
    } else {
        return false; // It's a single site installation
    }
}

function np_env($data)
{
    if (isset($_ENV[$data])) {
        return $_ENV[$data];
    }

    return false;
}


function esc_url($url)
{
    // Use filter_var to sanitize the URL
    $sanitized_url = filter_var($url, FILTER_SANITIZE_URL);

    // Check if the result is a valid URL
    if (filter_var($sanitized_url, FILTER_VALIDATE_URL) !== false) {
        return $sanitized_url;
    } else {
        // If the URL is not valid, return an empty string or handle it as needed
        return '';
    }
}

function esc_html($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function esc_js($javascript)
{
    return json_encode($javascript, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

function esc_sql($sql)
{
    global $npdb;

    if (isset($npdb)) {
        return $npdb->prepare($sql);
    } else {
        // Handle the case where $npdb is not available, or customize as needed
        return $sql;
    }
}

function esc($data, $context = 'html', $encoding = 'UTF-8')
{
    if (is_array($data) || is_object($data)) {
        // Handle arrays or objects by recursively calling the function
        foreach ($data as &$value) {
            $value = esc($value, $context, $encoding);
        }
        return $data;
    }

    switch ($context) {
        case 'html':
            return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, $encoding);
        case 'attr':
            return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, $encoding);
        case 'url':
            return esc_url($data);
        case 'js':
            return esc_js($data);
        case 'sql':
            global $wpdb;
            return esc_sql($data);
        default:
            return $data;
    }
}

function base_path(string $path)
{
    return ABSPATH . $path;
}

// function np_die($value)
// {
//     echo "<pre>";
//     echo "<div style='background-color:#000; color:#458657; margin: 5px; padding:5px;border:3px solid;'>";
//     echo "<h2 style='border:3px solid; border-color:teal; padding:5px; text-align:center;font-weight:bold;font-weight: bold;
//     text-transform: uppercase;'>";
//     echo "NattiPress Error Type: Dump and die";
//     echo "</h2>";
//     var_dump($value);
//     echo "</div>";
//     echo "</pre>";

//     die;
// }

function np_die($value, $message = '', $title = 'NattiPress Error', $status_code = 500)
{
    http_response_code($status_code);

    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>{$title}</title>
        <style>
            body {font-family: Arial, sans-serif;}
            .error-container {background-color: #F8F8F8; border: 1px solid #E0E0E0; margin: 20px; padding: 20px; text-align:center;}
            .error-title {font-size: 24px; color: #FF0000; font-weight: bold; margin-bottom: 10px;}
            .error-message {font-size: 18px; color: #333; margin-bottom: 20px;}
            .error-details {font-size: 16px; color: #777;}
        </style>
    </head>
    <body>
        <div class='error-container'>
            <div class='error-title'>{$title}</div>
            <div class='error-message'>{$message}</div>
            <div class='error-details'><pre>" . print_r($value, true) . "</pre></div>
        </div>
    </body>
    </html>";

    die;
}


function view($path, $attributes = [], $layout = 'default'): void
{
    // $view = new \App\Core\View();
    // $view->setLayout($layout);
    // $view->render($path, $attributes);
    np_die($path);
}

function split_url($url)
{
    return explode("/", trim($url, '/'));
}

function page()
{
    return URL(0);
}

function URL($key = '')
{
    global $np_app;

    // If $key is numeric or not empty, attempt to fetch a specific URL by key
    if (is_numeric($key) || !empty($key)) {
        if (isset($np_app['URL'][$key])) {
            return $np_app['URL'][$key];
        }
    } else {
        // If no $key is provided or it's empty, return the entire URL array
        return $np_app['URL'];
    }

    // Return an empty string if the URL key is not found
    return '';
}

function redirect($url, $status_code = 302, $headers = [], $query_params = [], $exit = true)
{
    // Ensure a valid HTTP status code is used
    if (!in_array($status_code, [301, 302, 303, 307, 308])) {
        $status_code = 302; // Default to a temporary (302) redirect
    }

    // Build the query string from the provided query parameters
    $query_string = !empty($query_params) ? '?' . http_build_query($query_params) : '';

    // Prepare and set custom headers
    $headers['Location'] = $url . $query_string;
    $headers['Status'] = $status_code . ' ' . http_response_code($status_code);

    // Send headers
    foreach ($headers as $key => $value) {
        header($key . ': ' . $value, true);
    }

    // Optionally exit to prevent further script execution
    if ($exit) {
        exit();
    }
}
