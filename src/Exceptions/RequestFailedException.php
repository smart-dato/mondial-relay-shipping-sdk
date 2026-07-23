<?php

namespace SmartDato\MondialRelayShipping\Exceptions;

use SmartDato\MondialRelayShipping\Responses\HttpExchange;
use Throwable;

class RequestFailedException extends MondialRelayShippingException
{
    public ?HttpExchange $exchange = null;

    public static function httpStatus(int $status, ?HttpExchange $exchange = null): self
    {
        $exception = new self("Mondial Relay responded with HTTP {$status}.");
        $exception->exchange = $exchange;

        return $exception;
    }

    public static function connection(Throwable $previous, ?HttpExchange $exchange = null): self
    {
        $exception = new self("Could not reach Mondial Relay: {$previous->getMessage()}", previous: $previous);
        $exception->exchange = $exchange;

        return $exception;
    }
}
