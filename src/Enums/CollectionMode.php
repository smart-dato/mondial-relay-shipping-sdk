<?php

namespace SmartDato\MondialRelayShipping\Enums;

enum CollectionMode: string
{
    case MerchantCollection = 'CCC';
    case PointRelaisCollection = 'REL';

    /**
     * Not listed in the V2 spec; Mondial Relay's merchant integration team specified
     * ModeCol = 24R together with ModeLiv = LCC for consumer returns via a relay point.
     */
    case PointRelaisReturn = '24R';
}
