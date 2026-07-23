<?php

use SmartDato\MondialRelayShipping\Exceptions\InvalidShipmentException;

it('requires a firstname and lastname or addressAdd1', function () {
    validAddress(['firstname' => null, 'lastname' => null]);
})->throws(InvalidShipmentException::class);

it('rejects a lastname without firstname when addressAdd1 is missing', function () {
    validAddress(['firstname' => null]);
})->throws(InvalidShipmentException::class);

it('accepts an address with only addressAdd1 as name', function () {
    $address = validAddress([
        'firstname' => null,
        'lastname' => null,
        'addressAdd1' => 'Mondial Relay',
    ]);

    expect($address->hasName())->toBeFalse();
});

it('detects a phone number on either phone field', function () {
    expect(validAddress()->hasPhone())->toBeFalse()
        ->and(validAddress(['phoneNo' => '+33320202020'])->hasPhone())->toBeTrue()
        ->and(validAddress(['mobileNo' => '+33600000000'])->hasPhone())->toBeTrue();
});
