# Changelog

All notable changes to `mondial-relay-shipping-sdk` will be documented in this file.

## 0.0.2 - 2026-07-23

- Configuration validation (credentials, customer id, culture) now runs on the first API call instead of at container resolution, so tooling that instantiates all facades (`ide-helper:generate`, `artisan about`, CI without secrets) no longer fails when credentials are absent

## 0.0.1 - 2026-07-23

Initial release.

- Shipment creation and label generation via the Mondial Relay Dual Carrier API V2 (`POST /api/shipment`)
- `MondialRelayShipping` client with `createShipment()` / `createShipments()`, facade, and config-driven singleton
- Request DTOs (spatie/laravel-data) with validation attributes and cross-field guards
- Typed error model: `CriticalErrorException`, `ShipmentFailedException`, batch results with per-shipment failures, non-throwing warnings
- Sanitized raw HTTP exchange attached to results and exceptions for debugging
- Supports PHP 8.4+ and Laravel 12/13
