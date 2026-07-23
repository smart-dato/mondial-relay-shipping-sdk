<?php

namespace SmartDato\MondialRelayShipping\Data;

use SmartDato\MondialRelayShipping\Enums\OutputFormat;
use SmartDato\MondialRelayShipping\Enums\OutputType;
use SmartDato\MondialRelayShipping\Exceptions\InvalidShipmentException;
use Spatie\LaravelData\Data;

class OutputOptions extends Data
{
    public function __construct(
        public OutputType $type,
        public ?OutputFormat $format = null,
    ) {
        if (! $this->type->supports($this->format)) {
            throw InvalidShipmentException::incompatibleOutput($this->type, $this->format);
        }
    }
}
