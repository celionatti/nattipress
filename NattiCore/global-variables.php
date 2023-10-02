<?php

declare(strict_types=1);

use NattiPress\NattiCore\Http\Request;

/**
 * Global Variables
 */
$req = new Request();
$np_app['URL'] = split_url($req->getPath());
// $np_app['URL'] = split_url($_GET['url'] ?? 'home');
$np_actions = [];
$np_filters = [];
$np_data = [];

if ( ! defined( 'NP_ROOT' ) ) {
    define( 'NP_ROOT', np_env("SITE_URL") );
}