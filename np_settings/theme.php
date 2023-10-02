<?php

declare(strict_types=1);

/**
 * 
 */

 function set_value(string|array $key, mixed $value = ''): bool {
    global $np_data;

    $called_from = debug_backtrace();
    $ikey = array_search(__FUNCTION__, array_column($called_from, 'function'));
    $path = get_plugin_dir(debug_backtrace()[$ikey]['file']) . 'config.json';

    if (file_exists($path)) {
        $json = json_decode(file_get_contents($path));
        $plugin_id = $json->id;

        // Ensure $np_data[$plugin_id] is initialized as an array
        if (!isset($np_data[$plugin_id]) || !is_array($np_data[$plugin_id])) {
            $np_data[$plugin_id] = [];
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $np_data[$plugin_id][$k] = $v;
            }
        } else {
            $np_data[$plugin_id][$key] = $value;
        }

        return true;
    }

    return false;
}

function get_value(string $key = ''): mixed {
    global $np_data;

    $called_from = debug_backtrace();
    $ikey = array_search(__FUNCTION__, array_column($called_from, 'function'));
    $path = get_plugin_dir(debug_backtrace()[$ikey]['file']) . 'config.json';

    if (file_exists($path)) {
        $json = json_decode(file_get_contents($path));
        $plugin_id = $json->id;

        if (empty($key)) {
            return $np_data[$plugin_id] ?? null;
        }

        $value = $np_data[$plugin_id][$key] ?? null;
        return $value;
    }

    return null;
}

function save_user_data_to_config($user_data, $config_path) {
    // Optionally, you can implement a function to save the $user_data array to the config file.
    // This allows you to persist the data between script executions.
    // Make sure to handle file locking and JSON encoding properly.

    // Encode the user data as JSON
    $json_data = json_encode($user_data, JSON_PRETTY_PRINT);

    if ($json_data === false) {
        // Handle JSON encoding error, e.g., log an error message
        error_log('Failed to encode user data to JSON.');
        return;
    }

    // Write the JSON data to the config file
    $result = file_put_contents($config_path, $json_data);

    if ($result === false) {
        // Handle file write error, e.g., log an error message
        error_log('Failed to write user data to the config file.');
    }
}


function get_plugin_folders($plugins_folder = 'themes/')
{
    $res = [];
    $folders = scandir($plugins_folder);

    foreach ($folders as $folder) {
        $pluginDir = $plugins_folder . $folder;
        if ($folder != '.' && $folder != '..' && is_dir($pluginDir)) {
            $res[] = $pluginDir;
        }
    }

    return $res;
}

function load_plugins(array $pluginFolders) {
    global $np_app;
    $loaded = false;

    foreach ($pluginFolders as $pluginDir) {
        $configFile = $pluginDir . '/config.json';

        if (file_exists($configFile)) {
            $json = json_decode(file_get_contents($configFile));

            if (is_object($json) && isset($json->id) && !empty($json->active)) {
                $pluginFile = $pluginDir . '/plugin.php';

                if (file_exists($pluginFile) && valid_route($json)) {
                    $json->index = $json->index ?? 1;
                    $json->version = $json->version ?? "1.0.0";
                    $json->dependencies = $json->dependencies ?? (object)[];
                    $json->index_file = $pluginFile;
                    $json->path = $pluginDir . '/';
                    $json->http_path = NP_ROOT . '/' . $json->path;

                    $np_app['plugins'][] = $json;
                    $loaded = true;
                }
            }
        }
    }

    if (!empty($np_app['plugins'])) {
        $np_app['plugins'] = sort_plugins($np_app['plugins']);

        foreach ($np_app['plugins'] as $json) {
            // Check for plugin dependencies
            if (!empty((array)$json->dependencies)) {
                foreach ((array)$json->dependencies as $plugin_id => $version) {
                    $plugin_data = plugin_exists($plugin_id);

                    if (!$plugin_data || !version_compare($plugin_data->version, $version, '>=')) {
                        dd("Missing or incompatible plugin dependency: {$plugin_id}, Requested by plugin: {$json->id}");
                        die;
                    }
                }
            }

            // Load plugin file
            if (file_exists($json->index_file)) {
                require_once $json->index_file;
            }
        }
    }

    return $loaded;
}


function plugin_exists(string $plugin_id): bool | object
{
    global $np_app;

    foreach ($np_app['plugins'] as $plugin) {
        if ($plugin['id'] === $plugin_id) {
            return (object)$plugin;
        }
    }

    return false;
}

function sort_plugins(array $plugins): array
{
    usort($plugins, function ($a, $b) {
        return $a->index - $b->index;
    });

    return $plugins;
}

function valid_route(object $json): bool
{
    $currentPage = page();

    if (!empty($json->routes->off) && is_array($json->routes->off)) {
        if (in_array($currentPage, $json->routes->off)) {
            return false;
        }
    }

    if (!empty($json->routes->on) && is_array($json->routes->on)) {
        if (in_array('all', $json->routes->on) || in_array($currentPage, $json->routes->on)) {
            return true;
        }
    }

    return false;
}



function add_action($hook, $func, $priority = 10)
{
    global $np_actions;

    if (!isset($np_actions[$hook])) {
        $np_actions[$hook] = [];
    }

    while (isset($np_actions[$hook][$priority])) {
        $priority++;
    }

    $np_actions[$hook][$priority] = $func;
    return true;
}

function do_action($hook, $data = [])
{
    global $np_actions;

    if (isset($np_actions[$hook])) {
        ksort($np_actions[$hook]);
        foreach ($np_actions[$hook] as $priority => $func) {
            call_user_func_array($func, [$data]);
        }
    }
}

function remove_action($hook, $func, $priority = 10)
{
    global $np_actions;

    if (isset($np_actions[$hook][$priority]) && $np_actions[$hook][$priority] === $func) {
        unset($np_actions[$hook][$priority]);
        return true;
    }
    return false;
}

function add_filter(string $hook, $func, int $priority = 10): bool
{
    global $np_filters;

    while (!empty($np_filters[$hook][$priority])) {
        $priority++;
    }

    $np_filters[$hook][$priority] = $func;

    return true;
}

function remove_filter(string $hook, $func, int $priority = 10): bool
{
    global $np_filters;

    if (!empty($np_filters[$hook][$priority]) && $np_filters[$hook][$priority] === $func) {
        unset($np_filters[$hook][$priority]);
        return true;
    }

    return false;
}

function do_filter(string $hook, $data = '')
{
    global $np_filters;

    if (!empty($np_filters[$hook])) {
        ksort($np_filters[$hook]);
        foreach ($np_filters[$hook] as $key => $func) {
            if (is_callable($func)) {
                $data = call_user_func($func, $data);
            }
        }
    }

    return $data;
}


function get_plugin_dir(string $filepath): string {
    // Get the directory path of the provided file
    $directory = dirname($filepath);

    // Define the expected directory structure for plugins
    $pluginsDirectory = 'themes' . DIRECTORY_SEPARATOR;

    // Check if the path contains the plugins directory
    if (strpos($directory, $pluginsDirectory) !== false) {
        // Split the path using the plugins directory as a delimiter
        $parts = explode($pluginsDirectory, $directory, 2);

        // Ensure the expected structure is found
        if (count($parts) === 2) {
            // Extract the plugin folder name
            $pluginFolder = strtok($parts[1], DIRECTORY_SEPARATOR);

            // Reconstruct the plugin directory path
            $pluginDirPath = ABSPATH . $pluginsDirectory . $pluginFolder . DIRECTORY_SEPARATOR;

            return $pluginDirPath;
        }
    }

    // If the expected structure is not found, return an empty string or handle the error as needed
    return '';
}

function plugin_path(string $path = '')
{
	$called_from = debug_backtrace();
	$key = array_search(__FUNCTION__, array_column($called_from, 'function'));
	return get_plugin_dir(debug_backtrace()[$key]['file']) . $path;
}

function plugin_http_path(string $path = '')
{
	$called_from = debug_backtrace();
	$key = array_search(__FUNCTION__, array_column($called_from, 'function'));
	
	return NP_ROOT . DIRECTORY_SEPARATOR . get_plugin_dir(debug_backtrace()[$key]['file']) . $path;
}
