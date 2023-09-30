<?php

declare(strict_types=1);

use Dotenv\Dotenv;

/**
 * 
 */

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

/**
 * Tells NattiPress to load the NattiPress theme and output it.
 *
 * @var bool
 */
define( 'NP_USE_THEMES', true );

/** Loads the NattiPress Environment and Template */
require dirname(__DIR__) . '/config/np_header.php';