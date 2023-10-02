<?php

declare(strict_types=1);

namespace NattiPress\NattiCore;

use NattiPress\NattiCore\Http\Request;
use NattiPress\NattiCore\Http\Response;

/**
 * NattiPress Class.
 */


class NattiPress
{
    public Request $request;
    public Response $response;
    public Router $router;

    public function __construct()
    {
        // Load plugins folders.
        $plugins = get_plugin_folders(ABSPATH . 'themes/');

        if (!load_plugins($plugins))
            np_die("No plugins were found! Please put at least one plugin in the plugins folder");
        // Use Server Request_uri
        //Nattipress Admin Dashboard.
    }

    public function run()
    {
        // echo $this->router->resolve();
    }
}
