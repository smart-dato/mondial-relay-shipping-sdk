<?php

namespace SmartDato\MondialRelayShipping\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SmartDato\MondialRelayShipping\MondialRelayShipping
 */
class MondialRelayShipping extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \SmartDato\MondialRelayShipping\MondialRelayShipping::class;
    }
}
