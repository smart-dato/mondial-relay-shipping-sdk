<?php

namespace SmartDato\MondialRelayShipping\Data;

use Spatie\LaravelData\Data;

class ShipmentOption extends Data
{
    public function __construct(
        public string $key,
        public string $value,
    ) {}

    public static function language(string $language): self
    {
        return new self('LNG', $language);
    }

    public static function insurance(string $level): self
    {
        return new self('ASS', $level);
    }
}
