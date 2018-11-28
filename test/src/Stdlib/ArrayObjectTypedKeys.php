<?php

namespace RealejoTest\Stdlib;

use Realejo\Stdlib\ArrayObject;
use RealejoTest\Enum\EnumConcrete;
use RealejoTest\Enum\EnumFlaggedConcrete;

/**
 * @property bool booleanKey
 * @property int intKey
 * @property \stdClass jsonObjectKey
 * @property array jsonArrayKey
 * @property \DateTime datetimeKey
 * @property EnumConcrete enum
 * @property EnumFlaggedConcrete enumFlagged
 */
class ArrayObjectTypedKeys extends ArrayObject
{
    protected $booleanKeys = ['booleanKey'];

    protected $intKeys = ['intKey'];

    protected $jsonArrayKeys = ['jsonArrayKey'];

    protected $jsonObjectKeys = ['jsonObjectKey'];

    protected $dateKeys = ['datetimeKey'];

    protected $enumKeys = [
        'enum' => EnumConcrete::class,
        'enumFlagged' => EnumFlaggedConcrete::class
    ];
}
