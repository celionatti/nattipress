<?php

declare(strict_types=1);

namespace NattiPress\NattiCore\Database;

use Exception;

/**
 * Database Exception.
 */

class DatabaseException extends Exception
{
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return "DatabaseException [{$this->code}]: {$this->message}\n";
    }
}