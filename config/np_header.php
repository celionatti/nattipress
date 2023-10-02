<?php

declare(strict_types=1);

/**
 * Loads the NattiPress environment and template.
 *
 * @package NattiPress
 */

if (!isset($np_did_header)) {

    $np_did_header = true;

    // Load the WordPress library.
    require_once __DIR__ . '/np_load.php';

    // Set up the NattiPress query.
    np();
}
