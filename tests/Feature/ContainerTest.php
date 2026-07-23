<?php

use SmartDato\MondialRelayShipping\Exceptions\InvalidConfigurationException;
use SmartDato\MondialRelayShipping\Facades\MondialRelayShipping as MondialRelayFacade;
use SmartDato\MondialRelayShipping\MondialRelayShipping;

it('resolves the client as a singleton', function () {
    expect(app(MondialRelayShipping::class))->toBe(app(MondialRelayShipping::class));
});

it('resolves the facade to the client', function () {
    expect(MondialRelayFacade::getFacadeRoot())->toBeInstanceOf(MondialRelayShipping::class);
});

it('resolves without credentials so tooling like ide-helper can instantiate the facade', function () {
    config()->set('mondial-relay-shipping-sdk.credentials.login', null);
    config()->set('mondial-relay-shipping-sdk.credentials.password', null);
    config()->set('mondial-relay-shipping-sdk.credentials.customer_id', null);

    expect(app(MondialRelayShipping::class))->toBeInstanceOf(MondialRelayShipping::class);
});

it('throws a clear exception on the first call when credentials are missing', function () {
    config()->set('mondial-relay-shipping-sdk.credentials.login', null);

    app(MondialRelayShipping::class)->createShipments([validShipment()]);
})->throws(InvalidConfigurationException::class, 'The `login` credential is missing.');

it('rejects a malformed customer id on the first call', function () {
    config()->set('mondial-relay-shipping-sdk.credentials.customer_id', 'bad');

    app(MondialRelayShipping::class)->createShipments([validShipment()]);
})->throws(InvalidConfigurationException::class);

it('rejects a malformed culture on the first call', function () {
    config()->set('mondial-relay-shipping-sdk.culture', 'french');

    app(MondialRelayShipping::class)->createShipments([validShipment()]);
})->throws(InvalidConfigurationException::class);
