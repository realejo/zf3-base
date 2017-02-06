<?php
namespace RealejoTest\Mapper;

use Realejo\Mapper\MapperAbstract;

class MapperConcrete extends MapperAbstract
{
    protected $tableName = 'album';
    protected $tableKey  = 'id';
}
