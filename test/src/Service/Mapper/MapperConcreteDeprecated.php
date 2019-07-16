<?php

namespace RealejoTest\Service\Mapper;

use Realejo\Service\MapperAbstract;
use Zend\Db\Sql\Select;

class MapperConcreteDeprecated extends MapperAbstract
{
    protected $tableName = 'album';
    protected $tableKey = 'id';

    protected $tableJoinLeft = [
        'test' => [
            'table' =>'test_table',
            'condition' => 'test_condition',
            'columns' => ['test_column'],
            'type' => Select::JOIN_LEFT
        ]
    ];
}
