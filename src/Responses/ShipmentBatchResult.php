<?php

namespace SmartDato\MondialRelayShipping\Responses;

use Spatie\LaravelData\Attributes\Hidden;
use Spatie\LaravelData\Data;

class ShipmentBatchResult extends Data
{
    /**
     * @param  array<int, CreatedShipment>  $shipments
     * @param  array<int, Status>  $statuses
     */
    public function __construct(
        public array $shipments = [],
        public array $statuses = [],
        #[Hidden]
        public ?HttpExchange $exchange = null,
    ) {}

    public function hasCriticalError(): bool
    {
        return array_any($this->statuses, fn (Status $status): bool => $status->isCritical());
    }

    /** @return array<int, CreatedShipment> */
    public function successful(): array
    {
        return array_values(array_filter(
            $this->shipments,
            fn (CreatedShipment $shipment): bool => $shipment->succeeded(),
        ));
    }

    /** @return array<int, CreatedShipment> */
    public function failed(): array
    {
        return array_values(array_filter(
            $this->shipments,
            fn (CreatedShipment $shipment): bool => ! $shipment->succeeded(),
        ));
    }

    /** @return array<int, Status> */
    public function errors(): array
    {
        $errors = array_values(array_filter(
            $this->statuses,
            fn (Status $status): bool => $status->isError(),
        ));

        foreach ($this->shipments as $shipment) {
            $errors = array_merge($errors, $shipment->errors());
        }

        return $errors;
    }

    /** @return array<int, Status> */
    public function warnings(): array
    {
        $warnings = array_values(array_filter(
            $this->statuses,
            fn (Status $status): bool => $status->isWarning(),
        ));

        foreach ($this->shipments as $shipment) {
            $warnings = array_merge($warnings, $shipment->warnings());
        }

        return $warnings;
    }
}
