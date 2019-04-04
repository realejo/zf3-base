<?php


namespace RealejoTest\Stdlib;


use Realejo\Stdlib\Entity;

/**
 * @property int integerValue
 * @property string string
 */
class EntityConcrete extends Entity
{
    protected $schema = [
        'integer_value' => [
            'type' => self::TYPE_INTEGER,
            'default' => 123,
            'alias' => 'integerValue'
        ],
        'string_value' => [
            'type' => self::TYPE_STRING,
            'default' => null,
            'alias' => 'integerNull'
        ],
    ];
}