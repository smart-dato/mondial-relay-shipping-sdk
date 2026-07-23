<?php

namespace SmartDato\MondialRelayShipping;

use SmartDato\MondialRelayShipping\Data\OutputOptions;
use SmartDato\MondialRelayShipping\Enums\OutputFormat;
use SmartDato\MondialRelayShipping\Enums\OutputType;
use SmartDato\MondialRelayShipping\Exceptions\InvalidConfigurationException;
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

            foreach (['login', 'password', 'customer_id'] as $key) {
                if (empty($credentials[$key])) {
                    throw InvalidConfigurationException::missingCredential($key);
                }
            }

            return new MondialRelayShipping(
                login: $credentials['login'],
                password: $credentials['password'],
                customerId: $credentials['customer_id'],
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
