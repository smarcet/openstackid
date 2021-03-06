<?php

namespace utils\exceptions;

use Exception;

/**
 * Class UnacquiredLockException
 * @package utils\exceptions
 */
class UnacquiredLockException extends Exception {

    public function __construct($message = "")
    {
        $message = "Unacquired Lock : " . $message;
        parent::__construct($message, 0, null);
    }
}