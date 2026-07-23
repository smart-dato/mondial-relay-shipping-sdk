<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use SmartDato\MondialRelayShipping\Exceptions\CriticalErrorException;
use SmartDato\MondialRelayShipping\Exceptions\InvalidResponseException;
use SmartDato\MondialRelayShipping\Exceptions\RequestFailedException;
use SmartDato\MondialRelayShipping\Exceptions\ShipmentFailedException;
use SmartDato\MondialRelayShipping\MondialRelayShipping;

it('exposes the raw request and response on successful results', function () {
    Http::fake(['*' => Http::response(xmlFixture('shipment-created-pdf-url'))]);

    $result = app(MondialRelayShipping::class)->createShipments([validShipment()]);
    $created = app(MondialRelayShipping::class)->createShipment(validShipment());

    expect($result->exchange->request)->toContain('<CustomerId>BDTEST99</CustomerId>')
        ->and($result->exchange->response)->toBe(xmlFixture('shipment-created-pdf-url'))
        ->and($result->exchange->status)->toBe(200)
        ->and($created->exchange->request)->toContain('<ShipmentCreationRequest');
});

it('exposes the raw exchange when the API responds with an HTTP error', function () {
    Http::fake(['*' => Http::response('Server error', 500)]);

    try {
        app(MondialRelayShipping::class)->createShipment(validShipment());
    } catch (RequestFailedException $exception) {
        expect($exception->exchange->request)->toContain('<CustomerId>BDTEST99</CustomerId>')
            ->and($exception->exchange->response)->toBe('Server error')
            ->and($exception->exchange->status)->toBe(500);

        return;
    }

    $this->fail('RequestFailedException was not thrown.');
});

it('keeps the raw request available when the connection fails', function () {
    Http::fake(fn () => throw new ConnectionException('Connection timed out'));

    try {
        app(MondialRelayShipping::class)->createShipment(validShipment());
    } catch (RequestFailedException $exception) {
        expect($exception->exchange->request)->toContain('<ShipmentCreationRequest')
            ->and($exception->exchange->response)->toBeNull()
            ->and($exception->exchange->status)->toBeNull();

        return;
    }

    $this->fail('RequestFailedException was not thrown.');
});

it('exposes the raw exchange on critical errors', function () {
    Http::fake(['*' => Http::response(xmlFixture('critical-error'))]);

    try {
        app(MondialRelayShipping::class)->createShipment(validShipment());
    } catch (CriticalErrorException $exception) {
        expect($exception->exchange->response)->toBe(xmlFixture('critical-error'))
            ->and($exception->exchange->request)->toContain('<Login>');

        return;
    }

    $this->fail('CriticalErrorException was not thrown.');
});

it('exposes the raw exchange when the single shipment fails', function () {
    Http::fake(['*' => Http::response(xmlFixture('shipment-error'))]);

    try {
        app(MondialRelayShipping::class)->createShipment(validShipment());
    } catch (ShipmentFailedException $exception) {
        expect($exception->exchange->response)->toBe(xmlFixture('shipment-error'))
            ->and($exception->exchange->status)->toBe(200);

        return;
    }

    $this->fail('ShipmentFailedException was not thrown.');
});

it('exposes the raw exchange when the response is not valid XML', function () {
    Http::fake(['*' => Http::response('definitely not xml')]);

    try {
        app(MondialRelayShipping::class)->createShipment(validShipment());
    } catch (InvalidResponseException $exception) {
        expect($exception->exchange->response)->toBe('definitely not xml')
            ->and($exception->exchange->request)->toContain('<ShipmentCreationRequest');

        return;
    }

    $this->fail('InvalidResponseException was not thrown.');
});

it('masks the password in the sanitized request', function () {
    Http::fake(['*' => Http::response(xmlFixture('shipment-created-pdf-url'))]);

    $result = app(MondialRelayShipping::class)->createShipments([validShipment()]);

    expect($result->exchange->request)->toContain('<Password>secret</Password>')
        ->and($result->exchange->sanitizedRequest())->not->toContain('secret')
        ->and($result->exchange->sanitizedRequest())->toContain('<Password>***</Password>');
});

it('hides the exchange from serialized results', function () {
    Http::fake(['*' => Http::response(xmlFixture('shipment-created-pdf-url'))]);

    $result = app(MondialRelayShipping::class)->createShipments([validShipment()]);

    expect($result->toArray())->not->toHaveKey('exchange')
        ->and($result->shipments[0]->toArray())->not->toHaveKey('exchange')
        ->and(json_encode($result))->not->toContain('secret');
});
