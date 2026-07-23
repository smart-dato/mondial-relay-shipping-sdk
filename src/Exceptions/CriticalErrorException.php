<?php

namespace SmartDato\MondialRelayShipping\Exceptions;

use SmartDato\MondialRelayShipping\Responses\HttpExchange;
use SmartDato\MondialRelayShipping\Responses\Status;

class CriticalErrorException extends MondialRelayShippingException
{
    /** @param array<int, Status> $statuses */
    public function __construct(
        public readonly array $statuses,
        public readonly ?HttpExchange $exchange = null,
    ) {
        parent::__construct('Mondial Relay reported a critical error: '.self::describe($statuses));
    }

    /** @param array<int, Status> $statuses */
    private static function describe(array $statuses): string
    {
        $messages = array_map(
            fn (Status $status): string => "[{$status->code}] {$status->message}",
            $statuses,
        );

        return implode(' | ', $messages);
    }
}
