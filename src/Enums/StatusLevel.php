<?php

namespace SmartDato\MondialRelayShipping\Enums;

enum StatusLevel: string
{
    case CriticalError = 'Critical Error';
    case Error = 'Error';
    case Warning = 'Warning';
}
