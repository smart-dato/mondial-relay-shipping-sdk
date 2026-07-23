<?php

namespace SmartDato\MondialRelayShipping\Exceptions;

class InvalidConfigurationException extends MondialRelayShippingException
{
    public static function missingCredential(string $key): self
    {
        return new self("The `{$key}` credential is missing. Set it in config/mondial-relay-shipping-sdk.php or the matching environment variable.");
    }

    public static function invalidCustomerId(string $customerId): self
    {
        return new self("Customer id `{$customerId}` must match ^[0-9A-Z]{2}[0-9A-Z]{6}$.");
    }

    public static function invalidCulture(string $culture): self
    {
        return new self("Culture `{$culture}` must match ^[a-z]{2}-[A-Z]{2}$, e.g. fr-FR.");
    }
}
