<?php
namespace VEWA\ApiBundle\Exception;

class ApiException extends \Exception
{
    const API_GENERIC_ERROR = 1;
    const API_NO_PARAMS_ERROR = 2;
    const API_UNKNOWN_ERROR = 3;

    public function __construct($message, $code = self::API_GENERIC_ERROR, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
