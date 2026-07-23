<?php

namespace SmartDato\MondialRelayShipping\Xml;

use SimpleXMLElement;
use SmartDato\MondialRelayShipping\Enums\OutputType;
use SmartDato\MondialRelayShipping\Enums\StatusLevel;
use SmartDato\MondialRelayShipping\Exceptions\InvalidResponseException;
use SmartDato\MondialRelayShipping\Responses\Barcode;
use SmartDato\MondialRelayShipping\Responses\CreatedShipment;
use SmartDato\MondialRelayShipping\Responses\HttpExchange;
use SmartDato\MondialRelayShipping\Responses\Label;
use SmartDato\MondialRelayShipping\Responses\ShipmentBatchResult;
use SmartDato\MondialRelayShipping\Responses\Status;

class ShipmentResponseParser
{
    public function parse(string $xml, OutputType $outputType, ?HttpExchange $exchange = null): ShipmentBatchResult
    {
        $document = $this->load($xml, $exchange);

        $shipments = [];

        foreach ($document->xpath('//ShipmentsList/Shipment') ?: [] as $shipmentElement) {
            $shipments[] = $this->parseShipment($shipmentElement, $outputType, $exchange);
        }

        return new ShipmentBatchResult(
            shipments: $shipments,
            statuses: $this->parseStatuses($document->xpath('/ShipmentCreationResponse/StatusList/Status')),
            exchange: $exchange,
        );
    }

    private function load(string $xml, ?HttpExchange $exchange): SimpleXMLElement
    {
        // Namespaces carry no information here and only complicate traversal.
        $withoutNamespaces = preg_replace('/\sxmlns(:\w+)?="[^"]*"/', '', $xml) ?? $xml;

        $previous = libxml_use_internal_errors(true);
        $document = simplexml_load_string($withoutNamespaces);
        libxml_use_internal_errors($previous);

        if ($document === false) {
            throw InvalidResponseException::unparseableXml($exchange);
        }

        return $document;
    }

    private function parseShipment(SimpleXMLElement $element, OutputType $outputType, ?HttpExchange $exchange): CreatedShipment
    {
        $labels = [];

        foreach ($element->xpath('LabelList/Label') ?: [] as $labelElement) {
            $labels[] = $this->parseLabel($labelElement, $outputType);
        }

        $shipmentNumber = (string) $element['ShipmentNumber'];

        return new CreatedShipment(
            shipmentNumber: $shipmentNumber === '' ? null : $shipmentNumber,
            labels: $labels,
            statuses: $this->parseStatuses($element->xpath('StatusList/Status')),
            exchange: $exchange,
        );
    }

    private function parseLabel(SimpleXMLElement $element, OutputType $outputType): Label
    {
        $values = [];

        foreach ($element->xpath('RawContent/LabelValues') ?: [] as $value) {
            $values[(string) $value['Key']] = (string) $value['Value'];
        }

        $barcodes = [];

        foreach ($element->xpath('RawContent/Barcodes/Barcode') ?: [] as $barcode) {
            $barcodes[] = new Barcode(
                type: (string) $barcode['Type'],
                displayedValue: (string) $barcode['DisplayedValue'],
                value: (string) $barcode['Value'],
                carrierCode: (string) $barcode['CarrierCode'],
            );
        }

        $output = $element->xpath('Output') ?: [];

        return new Label(
            output: $output === [] ? '' : trim((string) $output[0]),
            outputType: $outputType,
            values: $values,
            barcodes: $barcodes,
            statuses: $this->parseStatuses($element->xpath('StatusList/Status')),
        );
    }

    /**
     * @param  array<int, SimpleXMLElement>|false|null  $elements
     * @return array<int, Status>
     */
    private function parseStatuses(array|false|null $elements): array
    {
        $statuses = [];

        foreach ($elements ?: [] as $element) {
            $statuses[] = new Status(
                code: (int) $this->attribute($element, 'code'),
                level: StatusLevel::tryFrom($this->attribute($element, 'level')) ?? StatusLevel::Error,
                message: $this->attribute($element, 'message'),
            );
        }

        return $statuses;
    }

    private function attribute(SimpleXMLElement $element, string $name): string
    {
        $value = (string) $element[$name];

        if ($value !== '') {
            return $value;
        }

        return (string) $element[ucfirst($name)];
    }
}
