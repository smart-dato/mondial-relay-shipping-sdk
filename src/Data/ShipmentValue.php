<?php

namespace SmartDato\MondialRelayShipping\Data;

use SmartDato\MondialRelayShipping\Exceptions\InvalidShipmentException;
use Spatie\LaravelData\Data;

class ShipmentValue extends Data
{
    public function __construct(
        public float $amount,
        public string $currency = 'EUR',
    ) {
        if ($this->currency !== 'EUR') {
            throw InvalidShipmentException::unsupportedCurrency($this->currency);
        }
    }
}
