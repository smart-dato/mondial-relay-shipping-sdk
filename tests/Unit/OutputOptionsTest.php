<?php

use SmartDato\MondialRelayShipping\Data\OutputOptions;
use SmartDato\MondialRelayShipping\Enums\OutputFormat;
use SmartDato\MondialRelayShipping\Enums\OutputType;
use SmartDato\MondialRelayShipping\Exceptions\InvalidShipmentException;

it('accepts compatible output type and format combinations', function (OutputType $type, ?OutputFormat $format) {
    $options = new OutputOptions($type, $format);

    expect($options->type)->toBe($type);
})->with([
    'PdfUrl 10x15' => [OutputType::PdfUrl, OutputFormat::Label10x15],
    'PdfUrl A4' => [OutputType::PdfUrl, OutputFormat::A4],
    'PdfUrl A5' => [OutputType::PdfUrl, OutputFormat::A5],
    'ZplCode generic printer' => [OutputType::ZplCode, OutputFormat::ZplGeneric10x15],
    'IplCode generic printer' => [OutputType::IplCode, OutputFormat::IplGeneric10x15],
    'QRCode without format' => [OutputType::QrCode, null],
]);

it('rejects incompatible output type and format combinations', function (OutputType $type, ?OutputFormat $format) {
    expect(fn () => new OutputOptions($type, $format))->toThrow(InvalidShipmentException::class);
})->with([
    'PdfUrl with printer format' => [OutputType::PdfUrl, OutputFormat::ZplGeneric10x15],
    'PdfUrl without format' => [OutputType::PdfUrl, null],
    'ZplCode with paper format' => [OutputType::ZplCode, OutputFormat::A4],
    'QRCode with format' => [OutputType::QrCode, OutputFormat::A4],
]);
