<?php

namespace SmartDato\MondialRelayShipping\Xml;

use DOMDocument;
use DOMElement;
use SmartDato\MondialRelayShipping\Data\Address;
use SmartDato\MondialRelayShipping\Data\OutputOptions;
use SmartDato\MondialRelayShipping\Data\Parcel;
use SmartDato\MondialRelayShipping\Data\Shipment;
use SmartDato\MondialRelayShipping\Data\ShipmentValue;

class ShipmentRequestBuilder
{
    public const string VERSION_API = '1.0';

    private const string XMLNS = 'http://www.example.org/Request';

    public function __construct(
        private readonly string $login,
        private readonly string $password,
        private readonly string $customerId,
        private readonly string $culture,
    ) {}

    /** @param array<int, Shipment> $shipments */
    public function build(array $shipments, OutputOptions $output): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');

        $root = $document->createElementNS(self::XMLNS, 'ShipmentCreationRequest');
        $document->appendChild($root);

        $this->appendContext($document, $root);
        $this->appendOutputOptions($document, $root, $output);

        $list = $this->element($document, $root, 'ShipmentsList');

        foreach ($shipments as $shipment) {
            $this->appendShipment($document, $list, $shipment);
        }

        return $document->saveXML();
    }

    private function appendContext(DOMDocument $document, DOMElement $root): void
    {
        $context = $this->element($document, $root, 'Context');

        $this->element($document, $context, 'Login', $this->login);
        $this->element($document, $context, 'Password', $this->password);
        $this->element($document, $context, 'CustomerId', $this->customerId);
        $this->element($document, $context, 'Culture', $this->culture);
        $this->element($document, $context, 'VersionAPI', self::VERSION_API);
    }

    private function appendOutputOptions(DOMDocument $document, DOMElement $root, OutputOptions $output): void
    {
        $options = $this->element($document, $root, 'OutputOptions');

        if ($output->format !== null) {
            $this->element($document, $options, 'OutputFormat', $output->format->value);
        }

        $this->element($document, $options, 'OutputType', $output->type->value);
    }

    private function appendShipment(DOMDocument $document, DOMElement $list, Shipment $shipment): void
    {
        $element = $this->element($document, $list, 'Shipment');

        if ($shipment->options !== []) {
            $options = $this->element($document, $element, 'Options');

            foreach ($shipment->options as $option) {
                $optionElement = $this->element($document, $options, 'Option');
                $optionElement->setAttribute('Key', $option->key);
                $optionElement->setAttribute('Value', $option->value);
            }
        }

        $this->element($document, $element, 'OrderNo', $shipment->orderNo ?? '');
        $this->element($document, $element, 'CustomerNo', $shipment->customerNo ?? '');
        $this->element($document, $element, 'ParcelCount', (string) $shipment->parcelCount());

        if ($shipment->shipmentValue instanceof ShipmentValue) {
            // The official documentation example uses dotted element names here.
            $this->element($document, $element, 'shipmentValue.amount', number_format($shipment->shipmentValue->amount, 2, '.', ''));
            $this->element($document, $element, 'shipmentValue.currency', $shipment->shipmentValue->currency);
        }

        $deliveryMode = $this->element($document, $element, 'DeliveryMode');
        $deliveryMode->setAttribute('Mode', $shipment->deliveryMode->value);
        $deliveryMode->setAttribute('Location', $shipment->deliveryLocation ?? '');

        $collectionMode = $this->element($document, $element, 'CollectionMode');
        $collectionMode->setAttribute('Mode', $shipment->collectionMode->value);
        $collectionMode->setAttribute('Location', $shipment->collectionLocation ?? '');

        $parcels = $this->element($document, $element, 'Parcels');

        foreach ($shipment->parcels as $parcel) {
            $this->appendParcel($document, $parcels, $parcel);
        }

        if ($shipment->deliveryInstruction !== null) {
            $this->element($document, $element, 'DeliveryInstruction', $shipment->deliveryInstruction);
        }

        $sender = $this->element($document, $element, 'Sender');
        $this->appendAddress($document, $sender, $shipment->sender);

        $recipient = $this->element($document, $element, 'Recipient');
        $this->appendAddress($document, $recipient, $shipment->recipient);
    }

    private function appendParcel(DOMDocument $document, DOMElement $parcels, Parcel $parcel): void
    {
        $element = $this->element($document, $parcels, 'Parcel');

        if ($parcel->content !== null) {
            $this->element($document, $element, 'Content', $parcel->content);
        }

        $dimensions = [
            'Length' => $parcel->lengthCm,
            'Width' => $parcel->widthCm,
            'Depth' => $parcel->depthCm,
        ];

        foreach ($dimensions as $name => $centimeters) {
            if ($centimeters === null) {
                continue;
            }

            $dimension = $this->element($document, $element, $name);
            $dimension->setAttribute('Value', (string) $centimeters);
            $dimension->setAttribute('Unit', 'cm');
        }

        $weight = $this->element($document, $element, 'Weight');
        $weight->setAttribute('Value', (string) $parcel->weightGrams);
        $weight->setAttribute('Unit', 'gr');
    }

    private function appendAddress(DOMDocument $document, DOMElement $parent, Address $address): void
    {
        $element = $this->element($document, $parent, 'Address');

        $fields = [
            'Title' => $address->title,
            'Firstname' => $address->firstname,
            'Lastname' => $address->lastname,
            'Streetname' => $address->streetname,
            'HouseNo' => $address->houseNo,
            'CountryCode' => $address->countryCode,
            'PostCode' => $address->postCode,
            'City' => $address->city,
            'AddressAdd1' => $address->addressAdd1,
            'AddressAdd2' => $address->addressAdd2,
            'AddressAdd3' => $address->addressAdd3,
            'PhoneNo' => $address->phoneNo,
            'MobileNo' => $address->mobileNo,
            'Email' => $address->email,
        ];

        foreach ($fields as $name => $value) {
            $this->element($document, $element, $name, $value ?? '');
        }
    }

    private function element(DOMDocument $document, DOMElement $parent, string $name, ?string $value = null): DOMElement
    {
        $element = $document->createElementNS(self::XMLNS, $name);

        if ($value !== null && $value !== '') {
            $element->textContent = $value;
        }

        $parent->appendChild($element);

        return $element;
    }
}
