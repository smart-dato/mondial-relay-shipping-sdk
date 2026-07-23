<?php

namespace SmartDato\MondialRelayShipping\Responses;

use SmartDato\MondialRelayShipping\Enums\OutputType;
use Spatie\LaravelData\Data;

class Label extends Data
{
    /**
     * @param  array<string, string>  $values
     * @param  array<int, Barcode>  $barcodes
     * @param  array<int, Status>  $statuses
     */
    public function __construct(
        public string $output,
        public OutputType $outputType,
        public array $values = [],
        public array $barcodes = [],
        public array $statuses = [],
    ) {}

    public function decoded(): string
    {
        if (! $this->outputType->isBase64()) {
            return $this->output;
        }

        $decoded = base64_decode($this->output, true);

        return $decoded === false ? $this->output : $decoded;
    }
}
