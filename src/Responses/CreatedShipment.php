<?php

namespace SmartDato\MondialRelayShipping\Responses;

use Spatie\LaravelData\Attributes\Hidden;
use Spatie\LaravelData\Data;

class CreatedShipment extends Data
{
    /**
     * @param  array<int, Label>  $labels
     * @param  array<int, Status>  $statuses
     */
    public function __construct(
        public ?string $shipmentNumber,
        public array $labels = [],
        public array $statuses = [],
        #[Hidden]
        public ?HttpExchange $exchange = null,
    ) {}

    public function succeeded(): bool
    {
        return $this->shipmentNumber !== null
            && $this->shipmentNumber !== ''
            && $this->errors() === [];
    }

    /** @return array<int, Status> */
    public function errors(): array
    {
        return array_values(array_filter(
            $this->allStatuses(),
            fn (Status $status): bool => $status->isError(),
        ));
    }

    /** @return array<int, Status> */
    public function warnings(): array
    {
        return array_values(array_filter(
            $this->allStatuses(),
            fn (Status $status): bool => $status->isWarning(),
        ));
    }

    /** @return array<int, Status> */
    private function allStatuses(): array
    {
        $statuses = $this->statuses;

        foreach ($this->labels as $label) {
            $statuses = array_merge($statuses, $label->statuses);
        }

        return $statuses;
    }
}
