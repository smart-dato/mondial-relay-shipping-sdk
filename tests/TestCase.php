<?php

namespace SmartDato\MondialRelayShipping\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use SmartDato\MondialRelayShipping\MondialRelayShippingServiceProvider;
use Spatie\LaravelData\LaravelDataServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelDataServiceProvider::class,
            MondialRelayShippingServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('mondial-relay-shipping-sdk.sandbox', true);
        config()->set('mondial-relay-shipping-sdk.credentials.login', 'BDTEST@business-api.mondialrelay.com');
        config()->set('mondial-relay-shipping-sdk.credentials.password', 'secret');
        config()->set('mondial-relay-shipping-sdk.credentials.customer_id', 'BDTEST99');
        config()->set('mondial-relay-shipping-sdk.culture', 'fr-FR');
    }
}
