<?php


class FLEA_Exception extends Exception
{
    function __construct($message = '', $code = 0)
    {
        parent::__construct($message, $code);
    }
}

