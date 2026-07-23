<?php

use SmartDato\MondialRelayShipping\Data\OutputOptions;
use SmartDato\MondialRelayShipping\Data\ShipmentOption;
use SmartDato\MondialRelayShipping\Data\ShipmentValue;
use SmartDato\MondialRelayShipping\Enums\DeliveryMode;
use SmartDato\MondialRelayShipping\Enums\OutputFormat;
use SmartDato\MondialRelayShipping\Enums\OutputType;
use SmartDato\MondialRelayShipping\Xml\ShipmentRequestBuilder;

function builder(): ShipmentRequestBuilder
{
    return new ShipmentRequestBuilder(
        login: 'BDTEST@business-api.mondialrelay.com',
        password: 'secret',
        customerId: 'BDTEST99',
        culture: 'fr-FR',
    );
}

function buildXml(array $shipments, ?OutputOptions $output = null): string
{
    return builder()->build(
        $shipments,
        $output ?? new OutputOptions(OutputType::PdfUrl, OutputFormat::Label10x15),
    );
}

function requestXpath(string $xml): DOMXPath
{
    $document = new DOMDocument;
    $document->loadXML($xml);

    $xpath = new DOMXPath($document);
    $xpath->registerNamespace('r', 'http://www.example.org/Request');

    return $xpath;
}

it('declares UTF-8 without a byte order mark', function () {
    $xml = buildXml([validShipment()]);

    expect($xml)->toStartWith('<?xml version="1.0" encoding="UTF-8"?>')
        ->and(str_starts_with($xml, "\xEF\xBB\xBF"))->toBeFalse();
});

it('places the request in the documented namespace', function () {
    $xpath = requestXpath(buildXml([validShipment()]));

    expect($xpath->query('/r:ShipmentCreationRequest')->length)->toBe(1);
});

it('writes the context credentials and API version', function () {
    $xpath = requestXpath(buildXml([validShipment()]));

    expect($xpath->query('//r:Context/r:Login')->item(0)->textContent)->toBe('BDTEST@business-api.mondialrelay.com')
        ->and($xpath->query('//r:Context/r:CustomerId')->item(0)->textContent)->toBe('BDTEST99')
        ->and($xpath->query('//r:Context/r:Culture')->item(0)->textContent)->toBe('fr-FR')
        ->and($xpath->query('//r:Context/r:VersionAPI')->item(0)->textContent)->toBe('1.0');
});

it('renders delivery and collection modes as attributes', function () {
    $xpath = requestXpath(buildXml([validShipment()]));

    $delivery = $xpath->query('//r:Shipment/r:DeliveryMode')->item(0);
    $collection = $xpath->query('//r:Shipment/r:CollectionMode')->item(0);

    expect($delivery->getAttribute('Mode'))->toBe('24R')
        ->and($delivery->getAttribute('Location'))->toBe('FR-66974')
        ->and($collection->getAttribute('Mode'))->toBe('CCC')
        ->and($collection->getAttribute('Location'))->toBe('');
});

it('renders the parcel weight in grams as attributes', function () {
    $xpath = requestXpath(buildXml([validShipment()]));

    $weight = $xpath->query('//r:Parcel/r:Weight')->item(0);

    expect($weight->getAttribute('Value'))->toBe('1000')
        ->and($weight->getAttribute('Unit'))->toBe('gr');
});

it('renders parcel dimensions only when provided', function () {
    $withDimensions = validShipment([
        'parcels' => [validParcel(['lengthCm' => 60, 'widthCm' => 10, 'depthCm' => 8])],
    ]);

    $xpath = requestXpath(buildXml([$withDimensions]));

    expect($xpath->query('//r:Parcel/r:Length')->item(0)->getAttribute('Value'))->toBe('60')
        ->and($xpath->query('//r:Parcel/r:Width')->item(0)->getAttribute('Unit'))->toBe('cm')
        ->and(requestXpath(buildXml([validShipment()]))->query('//r:Parcel/r:Length')->length)->toBe(0);
});

it('derives the parcel count from the parcels list', function () {
    $shipment = validShipment([
        'deliveryMode' => DeliveryMode::PointRelaisXL,
        'parcels' => [validParcel(), validParcel()],
    ]);

    $xpath = requestXpath(buildXml([$shipment]));

    expect($xpath->query('//r:Shipment/r:ParcelCount')->item(0)->textContent)->toBe('2');
});

it('omits the options element when no options are set', function () {
    $xpath = requestXpath(buildXml([validShipment()]));

    expect($xpath->query('//r:Shipment/r:Options')->length)->toBe(0);
});

it('renders options as key value attributes', function () {
    $shipment = validShipment([
        'options' => [ShipmentOption::insurance('3'), ShipmentOption::language('NL')],
    ]);

    $xpath = requestXpath(buildXml([$shipment]));
    $options = $xpath->query('//r:Shipment/r:Options/r:Option');

    expect($options->length)->toBe(2)
        ->and($options->item(0)->getAttribute('Key'))->toBe('ASS')
        ->and($options->item(0)->getAttribute('Value'))->toBe('3')
        ->and($options->item(1)->getAttribute('Key'))->toBe('LNG');
});

it('renders the shipment value with dotted element names', function () {
    $shipment = validShipment(['shipmentValue' => new ShipmentValue(amount: 20.5)]);

    $xpath = requestXpath(buildXml([$shipment]));

    expect($xpath->query('//r:Shipment/r:shipmentValue.amount')->item(0)->textContent)->toBe('20.50')
        ->and($xpath->query('//r:Shipment/r:shipmentValue.currency')->item(0)->textContent)->toBe('EUR');
});

it('omits the output format for QR code output', function () {
    $xpath = requestXpath(buildXml([validShipment()], new OutputOptions(OutputType::QrCode)));

    expect($xpath->query('//r:OutputOptions/r:OutputFormat')->length)->toBe(0)
        ->and($xpath->query('//r:OutputOptions/r:OutputType')->item(0)->textContent)->toBe('QRCode');
});

it('escapes special characters in text content', function () {
    $shipment = validShipment(['deliveryInstruction' => 'Sonnette "R&D" <2>']);

    $xpath = requestXpath(buildXml([$shipment]));

    expect($xpath->query('//r:Shipment/r:DeliveryInstruction')->item(0)->textContent)->toBe('Sonnette "R&D" <2>');
});

it('renders one shipment element per shipment', function () {
    $xpath = requestXpath(buildXml([validShipment(), validShipment()]));

    expect($xpath->query('//r:ShipmentsList/r:Shipment')->length)->toBe(2);
});
