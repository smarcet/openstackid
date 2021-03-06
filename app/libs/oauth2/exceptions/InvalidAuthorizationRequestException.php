<?php

namespace oauth2\exceptions;

use Exception;

class InvalidAuthorizationRequestException extends Exception
{

    public function __construct($message = "")
    {
        $message = "Invalid Authorization Request : " . $message;
        parent::__construct($message, 0, null);
    }

}