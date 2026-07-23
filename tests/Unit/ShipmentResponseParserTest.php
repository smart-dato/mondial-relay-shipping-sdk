<?php

use SmartDato\MondialRelayShipping\Enums\OutputType;
use SmartDato\MondialRelayShipping\Enums\StatusLevel;
use SmartDato\MondialRelayShipping\Exceptions\InvalidResponseException;
use SmartDato\MondialRelayShipping\Xml\ShipmentResponseParser;

function parseFixture(string $name, OutputType $outputType = OutputType::PdfUrl)
{
    return new ShipmentResponseParser()->parse(xmlFixture($name), $outputType);
}

it('parses a successful shipment with a PDF label url', function () {
    $result = parseFixture('shipment-created-pdf-url');

    $shipment = $result->shipments[0];
    $label = $shipment->labels[0];

    expect($result->shipments)->toHaveCount(1)
        ->and($shipment->shipmentNumber)->toBe('96408887')
        ->and($shipment->succeeded())->toBeTrue()
        ->and($label->output)->toContain('GetStickersExpeditionsAnonyme')
        ->and($label->decoded())->toBe($label->output)
        ->and($label->values['MR.Expedition.NumeroExpedition'])->toBe('96408887')
        ->and($label->barcodes[0]->type)->toBe('Code128')
        ->and($label->barcodes[0]->carrierCode)->toBe('MR')
        ->and($result->statuses)->toBeEmpty();
});

it('decodes base64 ZPL label output', function () {
    $result = parseFixture('shipment-created-zpl', OutputType::ZplCode);

    $label = $result->shipments[0]->labels[0];

    expect($label->output)->toBe('XlhBXkZEVGVzdF5YWg==')
        ->and($label->decoded())->toBe('^XA^FDTest^XZ');
});

it('collects label warnings on the shipment', function () {
    $shipment = parseFixture('shipment-created-with-warning')->shipments[0];

    expect($shipment->succeeded())->toBeTrue()
        ->and($shipment->warnings())->toHaveCount(1)
        ->and($shipment->warnings()[0]->code)->toBe(10014)
        ->and($shipment->errors())->toBeEmpty();
});

it('marks a shipment as failed when it has error statuses', function () {
    $shipment = parseFixture('shipment-error')->shipments[0];

    expect($shipment->succeeded())->toBeFalse()
        ->and($shipment->shipmentNumber)->toBeNull()
        ->and($shipment->errors()[0]->code)->toBe(10033)
        ->and($shipment->errors()[0]->level)->toBe(StatusLevel::Error);
});

it('parses critical errors at response level', function () {
    $result = parseFixture('critical-error');

    expect($result->hasCriticalError())->toBeTrue()
        ->and($result->shipments)->toBeEmpty()
        ->and($result->statuses[0]->code)->toBe(10001)
        ->and($result->statuses[0]->level)->toBe(StatusLevel::CriticalError);
});

it('separates successful and failed shipments in a batch', function () {
    $result = parseFixture('batch-partial-success');

    expect($result->shipments)->toHaveCount(2)
        ->and($result->successful())->toHaveCount(1)
        ->and($result->successful()[0]->shipmentNumber)->toBe('96408887')
        ->and($result->failed())->toHaveCount(1)
        ->and($result->errors()[0]->code)->toBe(10023);
});

it('throws on a response that is not valid XML', function () {
    new ShipmentResponseParser()->parse('definitely not xml', OutputType::PdfUrl);
})->throws(InvalidResponseException::class);
