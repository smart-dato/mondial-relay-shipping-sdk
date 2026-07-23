# Mondial Relay Dual Carrier API V2 SDK for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/smart-dato/mondial-relay-shipping-sdk.svg?style=flat-square)](https://packagist.org/packages/smart-dato/mondial-relay-shipping-sdk)
[![GitHub Tests Action Status](https://github.com/smart-dato/mondial-relay-shipping-sdk/actions/workflows/run-tests.yml/badge.svg)](https://github.com/smart-dato/mondial-relay-shipping-sdk/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://github.com/smart-dato/mondial-relay-shipping-sdk/actions/workflows/fix-php-code-style-issues.yml/badge.svg)](https://github.com/smart-dato/mondial-relay-shipping-sdk/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/smart-dato/mondial-relay-shipping-sdk.svg?style=flat-square)](https://packagist.org/packages/smart-dato/mondial-relay-shipping-sdk)

Create Mondial Relay / InPost shipments and print labels from Laravel using the [Dual Carrier API V2](https://www.mondialrelay.fr/media/124596/web-service-dual-carrier-v-271.pdf). Labels can be retrieved as a PDF URL, raw ZPL/IPL printer code, or QR code.

## Installation

```bash
composer require smart-dato/mondial-relay-shipping-sdk
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag="mondial-relay-shipping-sdk-config"
```

Set your credentials in `.env`:

```dotenv
MONDIAL_RELAY_SHIPPING_SANDBOX=true
MONDIAL_RELAY_SHIPPING_LOGIN=M1ITTEST@business-api.mondialrelay.com
MONDIAL_RELAY_SHIPPING_PASSWORD=your-password
MONDIAL_RELAY_SHIPPING_CUSTOMER_ID=M1ITTEST
MONDIAL_RELAY_SHIPPING_CULTURE=fr-FR
MONDIAL_RELAY_SHIPPING_OUTPUT_TYPE=PdfUrl
MONDIAL_RELAY_SHIPPING_OUTPUT_FORMAT=10x15
```

While `MONDIAL_RELAY_SHIPPING_SANDBOX` is `true`, requests go to `connect-api-sandbox.mondialrelay.com`.

## Usage

```php
use SmartDato\MondialRelayShipping\Data\Address;
use SmartDato\MondialRelayShipping\Data\Parcel;
use SmartDato\MondialRelayShipping\Data\Shipment;
use SmartDato\MondialRelayShipping\Enums\CollectionMode;
use SmartDato\MondialRelayShipping\Enums\DeliveryMode;
use SmartDato\MondialRelayShipping\Facades\MondialRelayShipping;

$shipment = new Shipment(
    deliveryMode: DeliveryMode::PointRelais,
    deliveryLocation: 'FR-66974',
    collectionMode: CollectionMode::MerchantCollection,
    sender: new Address(
        streetname: 'Avenue Antoine Pinay',
        houseNo: '4',
        countryCode: 'FR',
        postCode: '59510',
        city: 'Hem',
        addressAdd1: 'My Shop',
    ),
    recipient: new Address(
        streetname: 'Test Street',
        houseNo: '10',
        countryCode: 'FR',
        postCode: '75001',
        city: 'Paris',
        firstname: 'John',
        lastname: 'Doe',
        phoneNo: '+33320202020',
    ),
    parcels: [new Parcel(weightGrams: 1000, content: 'Books')],
    orderNo: 'ORDER-1234',
);

$created = MondialRelayShipping::createShipment($shipment);

$created->shipmentNumber;        // "96408887"
$created->labels[0]->output;     // PDF URL (or base64 printer code)
$created->labels[0]->decoded();  // raw ZPL/IPL when using printer output
$created->warnings();            // non-blocking API warnings
```

### Delivery and collection modes

| Enum case | Code | Meaning |
|---|---|---|
| `DeliveryMode::PointRelais` | 24R | Point Relais® delivery (requires `deliveryLocation`) |
| `DeliveryMode::PointRelaisXL` | 24L | Point Relais® XL delivery (multi-parcel capable) |
| `DeliveryMode::HomeDelivery` | HOM | Home delivery (requires recipient phone) |
| `DeliveryMode::HomeDeliveryNextDay` | XOH | D+1 home delivery (requires EDI setup) |
| `DeliveryMode::MerchantDelivery` | LCC | Delivery back to the merchant (returns) |
| `CollectionMode::MerchantCollection` | CCC | Collected at the merchant (outbound) |
| `CollectionMode::PointRelaisCollection` | REL | Dropped off at a Point Relais® (returns) |

### Label output

```php
use SmartDato\MondialRelayShipping\Data\OutputOptions;
use SmartDato\MondialRelayShipping\Enums\OutputFormat;
use SmartDato\MondialRelayShipping\Enums\OutputType;

$created = MondialRelayShipping::createShipment(
    $shipment,
    new OutputOptions(OutputType::ZplCode, OutputFormat::ZplGeneric10x15),
);

$zpl = $created->labels[0]->decoded();
```

### Batches

`createShipments()` accepts multiple shipments and never throws for individual failures:

```php
$result = MondialRelayShipping::createShipments([$first, $second]);

$result->successful(); // CreatedShipment[]
$result->failed();     // CreatedShipment[] with their error statuses
$result->errors();     // Status[] (code, level, message)
```

### Multiple accounts

The facade uses the credentials from the config file. To ship on behalf of other accounts, instantiate clients directly — every setting is a constructor argument:

```php
use SmartDato\MondialRelayShipping\MondialRelayShipping;

$otherAccount = new MondialRelayShipping(
    login: 'OTHER@business-api.mondialrelay.com',
    password: 'other-password',
    customerId: 'OTHER123',
    culture: 'de-DE',
    sandbox: false,
);

$otherAccount->createShipment($shipment);
```

### Raw request & response

Every live call carries the raw HTTP exchange — on results and on all exceptions thrown after the request was built:

```php
$result = MondialRelayShipping::createShipments([$shipment]);

$result->exchange->request;            // XML sent (contains credentials!)
$result->exchange->sanitizedRequest(); // XML with the password masked — safe to log
$result->exchange->response;           // raw XML received
$result->exchange->status;             // HTTP status code

try {
    MondialRelayShipping::createShipment($shipment);
} catch (RequestFailedException|CriticalErrorException|ShipmentFailedException|InvalidResponseException $exception) {
    logger()->error('Mondial Relay call failed', [
        'request' => $exception->exchange?->sanitizedRequest(),
        'response' => $exception->exchange?->response,
    ]);
}
```

On a connection failure `response` and `status` are `null` but the request XML is still available. The exchange is excluded from `toArray()`/`toJson()` so serialized results never leak credentials.

### Error handling

All exceptions extend `SmartDato\MondialRelayShipping\Exceptions\MondialRelayShippingException`:

- `InvalidShipmentException` — the shipment breaks a documented constraint before any HTTP call (missing relay location, parcel under 10 g, multi-parcel on a single-parcel mode, …)
- `InvalidConfigurationException` — missing/malformed credentials, customer id, or culture; thrown on the first API call, not when the client is resolved, so tooling that instantiates all container bindings (`ide-helper:generate`, `artisan about`) works without credentials
- `RequestFailedException` — connection failure or non-2xx HTTP response
- `InvalidResponseException` — the API returned unparseable XML
- `CriticalErrorException` — the API rejected the whole request (e.g. authentication); inspect `$exception->statuses`
- `ShipmentFailedException` — `createShipment()` could not create the shipment; inspect `$exception->statuses`

API warnings never throw — read them via `$created->warnings()` or `$result->warnings()`.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [SmartDato](https://github.com/smart-dato)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
