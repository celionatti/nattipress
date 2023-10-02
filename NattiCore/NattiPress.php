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
        $this->indexing();
    }

    private function indexing()
    {
        do_action('before_controller');
        do_action('controller');
        do_action('after_controller');

        ob_start();
        do_action('before_view');

        $before_content = ob_get_contents();
        do_action('view');
        $after_content = ob_get_contents();

        if (strlen($after_content) == strlen($before_content)) {
            if (page() != 'not-found') {
                redirect('not-found');
            }
        }

        do_action('after_view');
    }
}
