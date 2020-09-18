<?php
namespace Lepresk\MomoApi\Exception;

use Exception;

class UserCreationErrorException extends Exception {

    public function __construct($message, $code = 0, Exception $previous = null)
    {
        $message = 'Api user creation fail. Response stack : ' . $message;
        parent::__construct($message, $code, $previous);
    }
}