<?php

namespace RealejoTest\Service\Mptt;

use Realejo\Service\MapperAbstract;

class MapperConcrete extends MapperAbstract
{
    protected $tableName = 'mptt';
    protected $tableKey = 'id';
}
