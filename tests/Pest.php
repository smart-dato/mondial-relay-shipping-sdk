<?php

use SmartDato\MondialRelayShipping\Data\Address;
use SmartDato\MondialRelayShipping\Data\Parcel;
use SmartDato\MondialRelayShipping\Data\Shipment;
use SmartDato\MondialRelayShipping\Enums\CollectionMode;
use SmartDato\MondialRelayShipping\Enums\DeliveryMode;
use SmartDato\MondialRelayShipping\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

function xmlFixture(string $name): string
{
    return file_get_contents(__DIR__."/Fixtures/{$name}.xml");
}

function validAddress(array $attributes = []): Address
{
    return new Address(...array_merge([
        'streetname' => 'Avenue Antoine Pinay',
        'countryCode' => 'FR',
        'postCode' => '59510',
        'city' => 'Hem',
        'firstname' => 'John',
        'lastname' => 'Doe',
        'houseNo' => '4',
    ], $attributes));
}

function validParcel(array $attributes = []): Parcel
{
    return new Parcel(...array_merge([
        'weightGrams' => 1000,
        'content' => 'Books',
    ], $attributes));
}

function validShipment(array $attributes = []): Shipment
{
    return new Shipment(...array_merge([
        'deliveryMode' => DeliveryMode::PointRelais,
        'collectionMode' => CollectionMode::MerchantCollection,
        'sender' => validAddress(),
        'recipient' => validAddress(['phoneNo' => '+33320202020']),
        'parcels' => [validParcel()],
        'deliveryLocation' => 'FR-66974',
        'orderNo' => 'KDZ-9999',
    ], $attributes));
}
