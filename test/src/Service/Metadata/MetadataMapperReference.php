<?php
/**
 * Classe mapper para ser usada nos testes
 */
namespace RealejoTest\Service\Metadata;

use Realejo\Service\MapperAbstract;

class MetadataMapperReference extends MapperAbstract
{
    protected $tableName = 'tblreference';
    protected $tableKey  = 'id_reference';
}
