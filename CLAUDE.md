# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Laravel package (`smart-dato/mondial-relay-shipping-sdk`) wrapping the Mondial Relay **Dual Carrier API V2** — shipment creation and label generation via a single XML-over-HTTPS endpoint (`POST /api/shipment`, production `connect-api.mondialrelay.com`, sandbox `connect-api-sandbox.mondialrelay.com`). API V1 (SOAP relay point search + tracking) is not implemented yet; nothing blocks adding it later as a sibling client.

Docs: `docs/dual-carrier-v271.pdf` is the official V2 spec (request/response XML, mode codes, error codes); `docs/Mondial Relay.pdf` is the client integration sheet with test credentials.

## Commands

```bash
composer test                          # Run all tests (Pest)
vendor/bin/pest tests/Unit/ShipmentTest.php  # Run a single test file
vendor/bin/pest --filter "test name"   # Run a single test by name
composer test-coverage                 # Tests with coverage
composer analyse                       # PHPStan (larastan, level 5, baseline in phpstan-baseline.neon)
composer format                        # Fix code style with Laravel Pint
```

`composer prepare` (testbench `package:discover`) runs automatically on autoload dump; run it manually if package discovery gets stale.

## Requirements & CI

- PHP ^8.4, Laravel (illuminate/contracts) ^11 / ^12 / ^13.
- CI (`.github/workflows/run-tests.yml`) tests PHP 8.3–8.5 against Laravel 12/13 on Ubuntu and Windows, prefer-lowest and prefer-stable.
- PHPUnit runs with random execution order and fails on warnings/risky tests; tests must not produce output.

## Architecture

- **Namespace:** `SmartDato\MondialRelayShipping` → `src/`; tests in `SmartDato\MondialRelayShipping\Tests`.
- **Service provider:** `src/MondialRelayShippingServiceProvider.php` uses `spatie/laravel-package-tools` (`configurePackage()`) to register the config file (`config/mondial-relay-shipping-sdk.php`) and binds the `MondialRelayShipping` client as a singleton built from config. New package assets are registered there, not in classic `boot()`/`register()` methods.
- **Facade:** `SmartDato\MondialRelayShipping\Facades\MondialRelayShipping` resolves `SmartDato\MondialRelayShipping\MondialRelayShipping`, the HTTP client and single public entry point (`createShipment()` / `createShipments()`).
- **Layers:** request DTOs in `src/Data/` (spatie/laravel-data classes; field rules as validation attributes, cross-field invariants as constructor guards throwing `InvalidShipmentException`), string-backed enums in `src/Enums/`, XML building/parsing in `src/Xml/`, response DTOs in `src/Responses/`, exception hierarchy under `src/Exceptions/` rooted at `MondialRelayShippingException`.
- **Error model:** API `StatusList` levels map to behavior — `Critical Error` → `CriticalErrorException`, per-shipment `Error` → `ShipmentFailedException` (single) or `ShipmentBatchResult::failed()` (batch), `Warning` never throws and rides on the result objects.
- **Raw exchange:** `HttpExchange` (request XML, response body, HTTP status) is attached to results and to every exception thrown after the request was built. It is `#[Hidden]` from laravel-data serialization because the request XML contains the password; `sanitizedRequest()` masks it for logging. Multiple accounts are supported by constructing `MondialRelayShipping` directly — the singleton is only a convenience for the configured default account.
- **Testing:** Pest with Orchestra Testbench. `tests/TestCase.php` registers the service provider and sets test credentials in config; `tests/Pest.php` binds it to all tests and provides `validShipment()`/`validAddress()`/`validParcel()`/`xmlFixture()` helpers. `tests/ArchTest.php` forbids `dd`, `dump`, and `ray` in shipped code.
