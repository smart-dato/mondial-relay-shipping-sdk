<?php

namespace SmartDato\MondialRelayShipping\Exceptions;

use SmartDato\MondialRelayShipping\Enums\DeliveryMode;
use SmartDato\MondialRelayShipping\Enums\OutputFormat;
use SmartDato\MondialRelayShipping\Enums\OutputType;

class InvalidShipmentException extends MondialRelayShippingException
{
    public static function weightTooLow(int $grams): self
    {
        return new self("Parcel weight must be at least 10 grams, got {$grams}.");
    }

    public static function parcelsRequired(): self
    {
        return new self('A shipment requires at least one parcel.');
    }

    public static function locationRequired(DeliveryMode $mode): self
    {
        return new self("Delivery mode {$mode->value} requires a delivery location.");
    }

    public static function multiParcelNotAllowed(DeliveryMode $mode): self
    {
        return new self("Delivery mode {$mode->value} only supports a single parcel.");
    }

    public static function nameOrAddressLineRequired(): self
    {
        return new self('An address requires either a firstname and lastname, or addressAdd1.');
    }

    public static function phoneRequired(DeliveryMode $mode): self
    {
        return new self("Delivery mode {$mode->value} requires a recipient phone number.");
    }

    public static function invalidOrderNo(string $orderNo): self
    {
        return new self("Order number `{$orderNo}` must match ^[0-9A-Z_ -]{0,15}$.");
    }

    public static function invalidCustomerNo(string $customerNo): self
    {
        return new self("Customer number `{$customerNo}` must match ^[0-9A-Z]{0,9}$.");
    }

    public static function unsupportedCurrency(string $currency): self
    {
        return new self("Only EUR is supported as shipment value currency, got `{$currency}`.");
    }

    public static function incompatibleOutput(OutputType $type, ?OutputFormat $format): self
    {
        $formatValue = $format === null ? 'none' : $format->value;

        return new self("Output format `{$formatValue}` is not compatible with output type `{$type->value}`.");
    }
}
