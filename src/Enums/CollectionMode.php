<?php

namespace SmartDato\MondialRelayShipping\Enums;

enum CollectionMode: string
{
    case MerchantCollection = 'CCC';
    case PointRelaisCollection = 'REL';
}
