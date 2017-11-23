<?php

namespace RealejoTest\Stdlib;

use Realejo\Stdlib\ArrayObject;

class ArrayObjectTypedKeys extends ArrayObject
{
    protected $booleanKeys = ['booleanKey'];
    protected $intKeys = ['intKey'];
    protected $jsonKeys = ['jsonKey'];
    protected $dateKeys = ['datetimeKey'];
}
