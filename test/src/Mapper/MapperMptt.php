<?php
namespace RealejoTest\Mapper;

use Realejo\Mapper\MapperAbstract;

class MapperMptt extends MapperAbstract
{
    protected $tableName = 'mptt';
    protected $tableKey  = 'id';
}
