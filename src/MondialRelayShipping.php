<?php

namespace SmartDato\MondialRelayShipping;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use SmartDato\MondialRelayShipping\Data\OutputOptions;
use SmartDato\MondialRelayShipping\Data\Shipment;
use SmartDato\MondialRelayShipping\Enums\OutputFormat;
use SmartDato\MondialRelayShipping\Enums\OutputType;
use SmartDato\MondialRelayShipping\Exceptions\CriticalErrorException;
use SmartDato\MondialRelayShipping\Exceptions\InvalidConfigurationException;
use SmartDato\MondialRelayShipping\Exceptions\RequestFailedException;
use SmartDato\MondialRelayShipping\Exceptions\ShipmentFailedException;
use SmartDato\MondialRelayShipping\Responses\CreatedShipment;
use SmartDato\MondialRelayShipping\Responses\HttpExchange;
use SmartDato\MondialRelayShipping\Responses\ShipmentBatchResult;
use SmartDato\MondialRelayShipping\Xml\ShipmentRequestBuilder;
use SmartDato\MondialRelayShipping\Xml\ShipmentResponseParser;

class MondialRelayShipping
{
    private const string PRODUCTION_URL = 'https://connect-api.mondialrelay.com/api/shipment';

    private const string SANDBOX_URL = 'https://connect-api-sandbox.mondialrelay.com/api/shipment';

    public function __construct(
        private readonly string $login,
        private readonly string $password,
        private readonly string $customerId,
        private readonly string $culture = 'fr-FR',
        private readonly bool $sandbox = true,
        private readonly ?OutputOptions $defaultOutput = null,
        private readonly int $timeout = 30,
    ) {
        if (preg_match('/^[0-9A-Z]{2}[0-9A-Z]{6}$/', $this->customerId) !== 1) {
            throw InvalidConfigurationException::invalidCustomerId($this->customerId);
        }

        if (preg_match('/^[a-z]{2}-[A-Z]{2}$/', $this->culture) !== 1) {
            throw InvalidConfigurationException::invalidCulture($this->culture);
        }
    }

    public function createShipment(Shipment $shipment, ?OutputOptions $output = null): CreatedShipment
    {
        $result = $this->createShipments([$shipment], $output);

        $successful = $result->successful();

        if ($successful === []) {
            throw new ShipmentFailedException($result->errors(), $result->exchange);
        }

        return $successful[0];
    }

    /** @param array<int, Shipment> $shipments */
    public function createShipments(array $shipments, ?OutputOptions $output = null): ShipmentBatchResult
    {
        $output ??= $this->defaultOutput();

        $xml = new ShipmentRequestBuilder(
            login: $this->login,
            password: $this->password,
            customerId: $this->customerId,
            culture: $this->culture,
        )->build($shipments, $output);

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders(['Accept' => 'application/xml'])
                ->withBody($xml, 'text/xml')
                ->post($this->url());
        } catch (ConnectionException $exception) {
            throw RequestFailedException::connection($exception, new HttpExchange($xml));
        }

        $exchange = new HttpExchange($xml, $response->body(), $response->status());

        if ($response->failed()) {
            throw RequestFailedException::httpStatus($response->status(), $exchange);
        }

        $result = new ShipmentResponseParser()->parse($response->body(), $output->type, $exchange);

        if ($result->hasCriticalError()) {
            throw new CriticalErrorException($result->statuses, $exchange);
        }

        return $result;
    }

    public function url(): string
    {
        return $this->sandbox ? self::SANDBOX_URL : self::PRODUCTION_URL;
    }

    private function defaultOutput(): OutputOptions
    {
        return $this->defaultOutput ?? new OutputOptions(OutputType::PdfUrl, OutputFormat::Label10x15);
    }
}
