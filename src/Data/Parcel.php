<?php

namespace SmartDato\MondialRelayShipping\Data;

use SmartDato\MondialRelayShipping\Exceptions\InvalidShipmentException;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;

class Parcel extends Data
{
    public function __construct(
        public int $weightGrams,
        #[Max(40)]
        public ?string $content = null,
        public ?int $lengthCm = null,
        public ?int $widthCm = null,
        public ?int $depthCm = null,
    ) {
        if ($this->weightGrams < 10) {
            throw InvalidShipmentException::weightTooLow($this->weightGrams);
        }
    }
}
