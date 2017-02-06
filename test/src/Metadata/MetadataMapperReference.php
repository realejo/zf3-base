<?php
/**
 * Classe mapper para ser usada nos testes
 */
namespace RealejoTest\Metadata;

use Realejo\Mapper\MapperAbstract;

class MetadataMapperReference extends MapperAbstract
{
    protected $tableName = 'tblreference';
    protected $tableKey  = 'id_reference';
}
