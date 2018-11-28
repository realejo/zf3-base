<?php

namespace RealejoTest\Enum;

use Realejo\Enum\Enum;

final class EnumConcrete extends Enum
{
    const STRING1 = 'S';
    const STRING2 = 'X';
    const NUMERIC1 = 666;
    const NUMERIC2 = 999;

    static protected $constDescription = [
        'S' => 'string1',
        'X' => ['string2', 'string with description'],
        666 => 'numeric1',
        999 => ['numeric2', 'numeric with description'],
    ];
}
