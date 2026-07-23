<?php

use SmartDato\MondialRelayShipping\Exceptions\InvalidShipmentException;

it('rejects parcels lighter than 10 grams', function () {
    validParcel(['weightGrams' => 9]);
})->throws(InvalidShipmentException::class);

it('accepts the minimum weight of 10 grams', function () {
    expect(validParcel(['weightGrams' => 10])->weightGrams)->toBe(10);
});
