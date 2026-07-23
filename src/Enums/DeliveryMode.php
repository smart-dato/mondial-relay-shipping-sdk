<?php

namespace SmartDato\MondialRelayShipping\Enums;

enum DeliveryMode: string
{
    case MerchantDelivery = 'LCC';
    case HomeDelivery = 'HOM';
    case PointRelais = '24R';
    case PointRelaisXL = '24L';
    case HomeDeliveryNextDay = 'XOH';

    public function requiresLocation(): bool
    {
        return match ($this) {
            self::PointRelais, self::PointRelaisXL, self::HomeDeliveryNextDay => true,
            default => false,
        };
    }

    public function allowsMultiParcel(): bool
    {
        return $this === self::PointRelaisXL;
    }

    public function requiresPhone(): bool
    {
        return match ($this) {
            self::HomeDelivery, self::HomeDeliveryNextDay => true,
            default => false,
        };
    }
}
