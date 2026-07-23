<?php

namespace SmartDato\MondialRelayShipping\Data;

use SmartDato\MondialRelayShipping\Enums\CollectionMode;
use SmartDato\MondialRelayShipping\Enums\DeliveryMode;
use SmartDato\MondialRelayShipping\Exceptions\InvalidShipmentException;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;

class Shipment extends Data
{
    /**
     * @param  array<int, Parcel>  $parcels
     * @param  array<int, ShipmentOption>  $options
     */
    public function __construct(
        public DeliveryMode $deliveryMode,
        public CollectionMode $collectionMode,
        public Address $sender,
        public Address $recipient,
        public array $parcels,
        public ?string $deliveryLocation = null,
        public ?string $collectionLocation = null,
        public ?string $orderNo = null,
        public ?string $customerNo = null,
        public ?ShipmentValue $shipmentValue = null,
        #[Max(30)]
        public ?string $deliveryInstruction = null,
        public array $options = [],
    ) {
        if ($this->parcels === []) {
            throw InvalidShipmentException::parcelsRequired();
        }

        if (count($this->parcels) > 1 && ! $this->deliveryMode->allowsMultiParcel()) {
            throw InvalidShipmentException::multiParcelNotAllowed($this->deliveryMode);
        }

        if ($this->deliveryMode->requiresLocation() && ($this->deliveryLocation === null || $this->deliveryLocation === '')) {
            throw InvalidShipmentException::locationRequired($this->deliveryMode);
        }

        if ($this->deliveryMode->requiresPhone() && ! $this->recipient->hasPhone()) {
            throw InvalidShipmentException::phoneRequired($this->deliveryMode);
        }

        if ($this->orderNo !== null && preg_match('/^[0-9A-Z_ -]{0,15}$/', $this->orderNo) !== 1) {
            throw InvalidShipmentException::invalidOrderNo($this->orderNo);
        }

        if ($this->customerNo !== null && preg_match('/^[0-9A-Z]{0,9}$/', $this->customerNo) !== 1) {
            throw InvalidShipmentException::invalidCustomerNo($this->customerNo);
        }
    }

    public function parcelCount(): int
    {
        return count($this->parcels);
    }
}
