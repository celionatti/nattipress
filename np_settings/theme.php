<?php

declare(strict_types=1);

/**
 * 
 */

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
