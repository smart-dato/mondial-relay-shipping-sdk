<?php

use SmartDato\MondialRelayShipping\Data\ShipmentValue;
use SmartDato\MondialRelayShipping\Enums\DeliveryMode;
use SmartDato\MondialRelayShipping\Exceptions\InvalidShipmentException;

it('requires at least one parcel', function () {
    validShipment(['parcels' => []]);
})->throws(InvalidShipmentException::class);

it('rejects multiple parcels for single-parcel delivery modes', function () {
    validShipment(['parcels' => [validParcel(), validParcel()]]);
})->throws(InvalidShipmentException::class);

it('allows multiple parcels for Point Relais XL delivery', function () {
    $shipment = validShipment([
        'deliveryMode' => DeliveryMode::PointRelaisXL,
        'parcels' => [validParcel(), validParcel()],
    ]);

    expect($shipment->parcelCount())->toBe(2);
});

it('requires a delivery location for relay delivery modes', function () {
    validShipment(['deliveryLocation' => null]);
})->throws(InvalidShipmentException::class);

it('requires a recipient phone number for home delivery', function () {
    validShipment([
        'deliveryMode' => DeliveryMode::HomeDelivery,
        'deliveryLocation' => null,
        'recipient' => validAddress(),
    ]);
})->throws(InvalidShipmentException::class);

it('rejects order numbers with lowercase characters', function () {
    validShipment(['orderNo' => 'ord-1']);
})->throws(InvalidShipmentException::class);

it('rejects customer numbers with invalid characters', function () {
    validShipment(['customerNo' => 'CUS 1234']);
})->throws(InvalidShipmentException::class);

it('rejects non-EUR shipment values', function () {
    new ShipmentValue(amount: 20.50, currency: 'USD');
})->throws(InvalidShipmentException::class);

it('accepts a complete valid shipment', function () {
    $shipment = validShipment([
        'customerNo' => 'CUS1234',
        'shipmentValue' => new ShipmentValue(amount: 20.50),
        'deliveryInstruction' => 'Livrer au fond a droite',
    ]);

    expect($shipment->parcelCount())->toBe(1)
        ->and($shipment->orderNo)->toBe('KDZ-9999');
});
