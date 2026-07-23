<?php

namespace SmartDato\MondialRelayShipping\Enums;

enum OutputType: string
{
    case PdfUrl = 'PdfUrl';
    case ZplCode = 'ZplCode';
    case IplCode = 'IplCode';
    case QrCode = 'QRCode';

    public function supports(?OutputFormat $format): bool
    {
        return match ($this) {
            self::PdfUrl => in_array($format, [OutputFormat::Label10x15, OutputFormat::A4, OutputFormat::A5], true),
            self::ZplCode => $format === OutputFormat::ZplGeneric10x15,
            self::IplCode => $format === OutputFormat::IplGeneric10x15,
            self::QrCode => $format === null,
        };
    }

    public function isBase64(): bool
    {
        return match ($this) {
            self::ZplCode, self::IplCode => true,
            default => false,
        };
    }
}
