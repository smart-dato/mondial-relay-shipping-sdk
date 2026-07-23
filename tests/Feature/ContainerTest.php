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

it('throws a clear exception when credentials are missing', function () {
    config()->set('mondial-relay-shipping-sdk.credentials.login', null);

    app(MondialRelayShipping::class);
})->throws(InvalidConfigurationException::class);

it('rejects a malformed customer id', function () {
    config()->set('mondial-relay-shipping-sdk.credentials.customer_id', 'bad');

    app(MondialRelayShipping::class);
})->throws(InvalidConfigurationException::class);

it('rejects a malformed culture', function () {
    config()->set('mondial-relay-shipping-sdk.culture', 'french');

    app(MondialRelayShipping::class);
})->throws(InvalidConfigurationException::class);
