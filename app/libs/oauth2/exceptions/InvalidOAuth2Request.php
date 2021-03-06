<?php

namespace oauth2\exceptions;

use Exception;

class InvalidOAuth2Request extends Exception
{

    public function __construct($message = "")
    {
        $message = "Invalid OAuth2 Request : " . $message;
        parent::__construct($message, 0, null);
    }

}