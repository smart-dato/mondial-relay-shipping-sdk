<?php

namespace SmartDato\MondialRelayShipping\Exceptions;

use SmartDato\MondialRelayShipping\Responses\HttpExchange;

class InvalidResponseException extends MondialRelayShippingException
{
    public ?HttpExchange $exchange = null;

    public static function unparseableXml(?HttpExchange $exchange = null): self
    {
        $exception = new self('Mondial Relay returned a response that is not valid XML.');
        $exception->exchange = $exchange;

        return $exception;
    }
}
