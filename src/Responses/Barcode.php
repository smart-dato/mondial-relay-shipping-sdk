<?php

namespace SmartDato\MondialRelayShipping\Responses;

use Spatie\LaravelData\Data;

class Barcode extends Data
{
    public function __construct(
        public string $type,
        public string $displayedValue,
        public string $value,
        public string $carrierCode,
    ) {}
}
