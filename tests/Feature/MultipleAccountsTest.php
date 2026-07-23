<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use SmartDato\MondialRelayShipping\MondialRelayShipping;

it('supports multiple accounts on the fly through the constructor', function () {
    Http::fake(['*' => Http::response(xmlFixture('shipment-created-pdf-url'))]);

    $sandboxAccount = new MondialRelayShipping(
        login: 'first@business-api.mondialrelay.com',
        password: 'first-secret',
        customerId: 'FIRST111',
        culture: 'fr-FR',
    );

    $productionAccount = new MondialRelayShipping(
        login: 'second@business-api.mondialrelay.com',
        password: 'second-secret',
        customerId: 'SECOND22',
        culture: 'de-DE',
        sandbox: false,
    );

    $sandboxAccount->createShipment(validShipment());
    $productionAccount->createShipment(validShipment());

    Http::assertSentCount(2);

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://connect-api-sandbox.mondialrelay.com/api/shipment'
            && str_contains($request->body(), '<Login>first@business-api.mondialrelay.com</Login>')
            && str_contains($request->body(), '<CustomerId>FIRST111</CustomerId>')
            && str_contains($request->body(), '<Culture>fr-FR</Culture>');
    });

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://connect-api.mondialrelay.com/api/shipment'
            && str_contains($request->body(), '<Login>second@business-api.mondialrelay.com</Login>')
            && str_contains($request->body(), '<CustomerId>SECOND22</CustomerId>')
            && str_contains($request->body(), '<Culture>de-DE</Culture>');
    });
});

it('keeps constructor accounts independent from the configured singleton', function () {
    Http::fake(['*' => Http::response(xmlFixture('shipment-created-pdf-url'))]);

    $adHocAccount = new MondialRelayShipping(
        login: 'adhoc@business-api.mondialrelay.com',
        password: 'adhoc-secret',
        customerId: 'ADHOC123',
    );

    $adHocAccount->createShipment(validShipment());
    app(MondialRelayShipping::class)->createShipment(validShipment());

    Http::assertSent(fn (Request $request): bool => str_contains($request->body(), '<CustomerId>ADHOC123</CustomerId>'));
    Http::assertSent(fn (Request $request): bool => str_contains($request->body(), '<CustomerId>BDTEST99</CustomerId>'));
});
