<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use SmartDato\MondialRelayShipping\Exceptions\CriticalErrorException;
use SmartDato\MondialRelayShipping\Exceptions\RequestFailedException;
use SmartDato\MondialRelayShipping\Exceptions\ShipmentFailedException;
use SmartDato\MondialRelayShipping\MondialRelayShipping;

it('creates a shipment and returns the label', function () {
    Http::fake(['*' => Http::response(xmlFixture('shipment-created-pdf-url'))]);

    $shipment = app(MondialRelayShipping::class)->createShipment(validShipment());

    expect($shipment->shipmentNumber)->toBe('96408887')
        ->and($shipment->succeeded())->toBeTrue()
        ->and($shipment->labels[0]->output)->toContain('GetStickersExpeditionsAnonyme');
});

it('sends an XML request to the sandbox url', function () {
    Http::fake(['*' => Http::response(xmlFixture('shipment-created-pdf-url'))]);

    app(MondialRelayShipping::class)->createShipment(validShipment());

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://connect-api-sandbox.mondialrelay.com/api/shipment'
            && $request->hasHeader('Accept', 'application/xml')
            && $request->hasHeader('Content-Type', 'text/xml')
            && str_contains($request->body(), '<CustomerId>BDTEST99</CustomerId>');
    });
});

it('uses the production url when sandbox is disabled', function () {
    config()->set('mondial-relay-shipping-sdk.sandbox', false);
    Http::fake(['*' => Http::response(xmlFixture('shipment-created-pdf-url'))]);

    app(MondialRelayShipping::class)->createShipment(validShipment());

    Http::assertSent(
        fn (Request $request): bool => $request->url() === 'https://connect-api.mondialrelay.com/api/shipment',
    );
});

it('throws a critical error exception when authentication fails', function () {
    Http::fake(['*' => Http::response(xmlFixture('critical-error'))]);

    app(MondialRelayShipping::class)->createShipment(validShipment());
})->throws(CriticalErrorException::class);

it('exposes the statuses on a critical error exception', function () {
    Http::fake(['*' => Http::response(xmlFixture('critical-error'))]);

    try {
        app(MondialRelayShipping::class)->createShipment(validShipment());
    } catch (CriticalErrorException $exception) {
        expect($exception->statuses[0]->code)->toBe(10001)
            ->and($exception->getMessage())->toContain('Invalid user and/or password');
    }
});

it('throws when the single shipment could not be created', function () {
    Http::fake(['*' => Http::response(xmlFixture('shipment-error'))]);

    app(MondialRelayShipping::class)->createShipment(validShipment());
})->throws(ShipmentFailedException::class);

it('returns failed shipments in a batch without throwing', function () {
    Http::fake(['*' => Http::response(xmlFixture('batch-partial-success'))]);

    $result = app(MondialRelayShipping::class)->createShipments([validShipment(), validShipment()]);

    expect($result->successful())->toHaveCount(1)
        ->and($result->failed())->toHaveCount(1)
        ->and($result->errors()[0]->code)->toBe(10023);
});

it('exposes warnings without failing the shipment', function () {
    Http::fake(['*' => Http::response(xmlFixture('shipment-created-with-warning'))]);

    $shipment = app(MondialRelayShipping::class)->createShipment(validShipment());

    expect($shipment->succeeded())->toBeTrue()
        ->and($shipment->warnings())->toHaveCount(1)
        ->and($shipment->warnings()[0]->code)->toBe(10014);
});

it('throws when the API responds with an HTTP error', function () {
    Http::fake(['*' => Http::response('Server error', 500)]);

    app(MondialRelayShipping::class)->createShipment(validShipment());
})->throws(RequestFailedException::class);
