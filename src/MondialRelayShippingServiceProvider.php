<?php

namespace SmartDato\MondialRelayShipping;

use SmartDato\MondialRelayShipping\Data\OutputOptions;
use SmartDato\MondialRelayShipping\Enums\OutputFormat;
use SmartDato\MondialRelayShipping\Enums\OutputType;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MondialRelayShippingServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('mondial-relay-shipping-sdk')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(MondialRelayShipping::class, function (): MondialRelayShipping {
            $credentials = config('mondial-relay-shipping-sdk.credentials');

            return new MondialRelayShipping(
                login: (string) ($credentials['login'] ?? ''),
                password: (string) ($credentials['password'] ?? ''),
                customerId: (string) ($credentials['customer_id'] ?? ''),
                culture: config('mondial-relay-shipping-sdk.culture'),
                sandbox: (bool) config('mondial-relay-shipping-sdk.sandbox'),
                defaultOutput: new OutputOptions(
                    type: OutputType::from(config('mondial-relay-shipping-sdk.output.type')),
                    format: OutputFormat::tryFrom((string) config('mondial-relay-shipping-sdk.output.format')),
                ),
                timeout: (int) config('mondial-relay-shipping-sdk.timeout'),
            );
        });
    }
}
