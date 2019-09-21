<?php
namespace Lib\Core;

use Exception;

class SafException extends Exception
{
    public static function throw($errorCode) {
        throw new SafException($errorCode['message'], $errorCode['code']);
    }
}

