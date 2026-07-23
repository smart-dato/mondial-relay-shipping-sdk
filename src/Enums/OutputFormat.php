<?php

namespace SmartDato\MondialRelayShipping\Enums;

enum OutputFormat: string
{
    case A4 = 'A4';
    case A5 = 'A5';
    case Label10x15 = '10x15';
    case ZplGeneric10x15 = 'Generic_ZPL_10x15_200dpi';
    case IplGeneric10x15 = 'Generic_IPL_10x15_204dpi';
}
