<?php

namespace RealejoTest\Stdlib;

use Realejo\Stdlib\ArrayObject;
use RealejoTest\Enum\EnumConcrete;
use RealejoTest\Enum\EnumFlaggedConcrete;

/**
 * @property bool booleanKey
 * @property int intKey
 * @property \stdClass jsonKey
 * @property \DateTime datetimeKey
 * @property EnumConcrete enum
 * @property EnumFlaggedConcrete enumFlagged
 */
class ArrayObjectTypedKeys extends ArrayObject
{
    protected $booleanKeys = ['booleanKey'];

    protected $intKeys = ['intKey'];

    protected $jsonKeys = ['jsonKey'];

    protected $dateKeys = ['datetimeKey'];

    protected $enumKeys = [
        'enum' => EnumConcrete::class,
        'enumFlagged' => EnumFlaggedConcrete::class
    ];
}
