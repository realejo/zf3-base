<?php
namespace Realejo\Service\Metadata;

use Realejo\Service\ServiceAbstract;
use Zend\Db\Sql\Expression;
use Zend\Json\Json;

class MetadataService extends ServiceAbstract
{
    /**
     * Tipos de campos
     */
    const TEXT     = "T";

    const INTEGER  = "I";

    const DATE     = "D";

    const BOOLEAN  = "B";

    const DECIMAL  = "F";

    const DATETIME = "M";

    protected $mapperValues;

    protected $mapperSchema;

    protected $mapperValue;

    protected $referenceKey;

    protected $cacheKey;

    protected $lastSaveMetadataLog;

    protected $infoKeyName = 'id_info';

    protected $infoForeignKeyName = 'fk_info';

    public function getWhere($where)
    {
        if (empty($where) || ! is_array($where)) {
            return parent::getWhere($where);
        }

        $schema = $this->getSchemaByKeyNames();
        if (empty($schema)) {
            return parent::getWhere($where);
        }

        // Verifica se tem a chave de metadata
        $metadata  = [];
        if (array_key_exists('metadata', $where)) {
            $metadata = $where['metadata'];
            unset($where['metadata']);
        }

        // Verifica se metadata direto no where
        foreach (array_keys($schema) as $key) {
            if (array_key_exists($key, $where)) {
                $metadata[$key] = $where[$key];
                unset($where[$key]);
            }
        }

        if (empty($metadata)) {
            return parent::getWhere($where);
        }

        $valueTable   = $this->getMapperValue()->getTableName();
        $referenceKey = $this->getReferenceKey();

        $mapperTable = $this->getMapper()->getTableName();
        $mapperKey   = $this->getMapper()->getTableKey(true);

        foreach ($metadata as $id => $value) {
            // Ignora as chaves que não existam
            //@todo deveria retornar Exception?
            if (! array_key_exists($id, $schema)) {
                continue;
            }

            // Determina a consulta de acordo com o type
            switch ($schema[$id]['type']) {
                case self::TEXT:
                    $cast = 'STRING';
                    $field = 'value_text';
                    break;

                case self::DATE:
                    $cast = 'STRING';
                    $field = 'value_date';
                    break;

                case self::DATETIME:
                    $cast = 'STRING';
                    $field = 'value_datetime';
                    break;

                case self::INTEGER:
                    $cast = 'INTEGER';
                    $field = 'value_integer';
                    break;

                case self::DECIMAL:
                    $cast = 'DECIMAL';
                    $field = 'value_decimal';
                    break;

                case self::BOOLEAN:
                    $cast = 'INTEGER';
                    $field = 'value_boolean';
                    break;
            }

            // Corrige o valor
            $value = $this->getCorrectSetValue($schema[$id], $value);

            // Faz a busca pelo campo
            if (is_null($value)) {
                $where[] = new Expression(
                    "EXISTS (SELECT * FROM $valueTable WHERE $valueTable.{$this->infoForeignKeyName}={$schema[$id][$this->infoKeyName]} " .
                            "AND $mapperTable.$mapperKey=$valueTable.$referenceKey " .
                            "AND $field IS NULL) " .
                    "OR NOT EXISTS (SELECT * FROM $valueTable " .
                                    "WHERE $valueTable.{$this->infoForeignKeyName}={$schema[$id][$this->infoKeyName]} " .
                    "AND $mapperTable.$mapperKey=$valueTable.$referenceKey)"
                );
            } else {
                $where[] = new Expression($this->quoteInto(
                    "EXISTS (SELECT * FROM $valueTable " .
                    "WHERE $valueTable.{$this->infoForeignKeyName}={$schema[$id][$this->infoKeyName]} " .
                        "AND $mapperTable.$mapperKey=$valueTable.$referenceKey " .
                        "AND $field = ?)",
                    $value,
                    $this->getMapperSchema()
                                                        ->getTableGateway()
                                                        ->getAdapter()
                    ->getPlatform(),
                    null,
                    $cast
                ));
            }
        }

        return parent::getWhere($where);
    }

    /**
     *
     * @param string $schemaTable Tabela onde será gravada a configuração dos metadados
     * @param string $valueTable Tabela onde é gravada os metadados
     * @param string $mapperForeignKey Chave Estrageira para localizar um metadado na tabelas de valores
     * @return MetadataService
     * @throws \Exception
     */
    public function setMetadataMappers($schemaTable, $valueTable, $mapperForeignKey)
    {
        if (! is_string($schemaTable) || empty($schemaTable)) {
            throw new \Exception("schemaTable invalid");
        }
        if (! is_string($valueTable) || empty($valueTable)) {
            throw new \Exception("valueTable invalid");
        }
        if (! is_string($mapperForeignKey) || empty($mapperForeignKey)) {
            throw new \Exception("mapperForeignKey invalid");
        }

        $this->mapperSchema     = $schemaTable;
        $this->mapperValue      = $valueTable;
        $this->referenceKey = $mapperForeignKey;
        $this->cacheKey         = 'metadataschema_'.$schemaTable;

        return $this;
    }

    public function getSchemaByKeyNames($useCache = true)
    {
        $schema = $this->getSchema($useCache);
        $schemaByKeynames = null;
        if (! empty($schema)) {
            $schemaByKeynames = [];
            foreach ($schema as $row) {
                $schemaByKeynames[$row['nick']] = $row;
            }
        }
        return $schemaByKeynames;
    }

    public function getSchema($useCache = true)
    {
        // Verifica se deve usar o cache
        if ($useCache === true && $this->getCache()->hasItem($this->cacheKey)) {
            return $this->getCache()->getItem($this->cacheKey);
        }
        $fetchAll = $this->getMapperSchema()->fetchAll();

        $schema = null;
        if (! empty($fetchAll)) {
            $schema = [];
            foreach ($fetchAll as $row) {
                $schema[$row[$this->infoKeyName]] = $row->toArray();
            }
        }

        $this->getCache()->setItem($this->cacheKey, $schema);

        return $schema;
    }

    /**
     * Retorna os valores de todos os metadados
     *
     * @param int $foreignkey Chave de referencia
     * @param boolean $complete OPCIONAL Retorna os campos não definidos como NULL.
     * @return array|null
     */
    public function getValues($foreignkey, $complete = false)
    {
        $fetchAll = $this->getMapperValue()->fetchAll([$this->referenceKey => $foreignkey]);
        if (empty($fetchAll) && $complete !== true) {
            return $fetchAll;
        }

        $schema = $this->getSchema();
        if (null === $schema) {
            return $fetchAll;
        }

        $getValues = [];
        if (! empty($fetchAll)) {
            foreach ($fetchAll as $row) {
                $getValues[$schema[$row[$this->infoForeignKeyName]]['nick']] = $this->getCurrentValue($schema[$row[$this->infoForeignKeyName]], $row);
            }
        }

        // Adiciona as chaves vazias
        if ($complete === true) {
            foreach ($schema as $s) {
                if (! isset($getValues[$s['nick']])) {
                    $getValues[$s['nick']] = null;
                }
            }
        }

        return $getValues;
    }

    public function removeMetadata($set)
    {
        $metadataKeys = $this->getSchemaByKeyNames(false);

        foreach ($metadataKeys as $schema) {
            // Verifica se existe o metadado no dados enviados
            if (array_key_exists($schema['nick'], $set)) {
                unset($set[$schema['nick']]);
            }
        }

        return $set;
    }

    public function saveMetadata($set, $foreignKey)
    {
        $metadataKeys = $this->getSchemaByKeyNames(false);
        $currentValues = $this->getValues($foreignKey);
        $saveMetadataLog = [];

        // Verifica se tem alguma chave definida
        if (empty($metadataKeys)) {
            $this->lastSaveMetadataLog = $saveMetadataLog;
            return $set;
        }

        foreach ($metadataKeys as $schema) {
            // Verifica se existe o metadado no dados enviados
            if (! array_key_exists($schema['nick'], $set)) {
                continue;
            }

            // Define o valor do metadado
            // Deve passar para nulo
            // Nao pode usar empty pois 0 (zero) e FALSE são empty e são válidos
            $setMetadataValue = $this->getCorrectSetValue($schema, $set[$schema['nick']]);
            if ($setMetadataValue === '') {
                $setMetadataValue = null;
            }
            $setMetadataKey = $this->getCorrectSetKey($schema);

            // Verifica se existe o metadado salvo e se é diferente
            // $currentValues pode ser vazio quando o PDV for novo
            if (! empty($currentValues) && array_key_exists($schema['nick'], $currentValues)) {
                $whereKey = [
                    $this->infoForeignKeyName => $schema[$this->infoKeyName],
                    $this->referenceKey => $foreignKey
                ];
                if ($setMetadataValue !== $currentValues[$schema['nick']]) {
                    if (is_null($setMetadataValue)) {
                        $this->getMapperValue()
                             ->delete($whereKey);
                        $saveMetadataLog[$schema['nick']] = [$currentValues[$schema['nick']], null];
                    } else {
                        $this->getMapperValue()
                             ->update(
                                 [$setMetadataKey => $setMetadataValue],
                                 $whereKey
                             );
                        $saveMetadataLog[$schema['nick']] = [$currentValues[$schema['nick']], $setMetadataValue];
                    }
                }
            } elseif (! is_null($setMetadataValue)) {
                $this->getMapperValue()
                     ->insert([
                         $this->infoForeignKeyName           => $schema[$this->infoKeyName],
                         $this->referenceKey => $foreignKey,
                         $setMetadataKey     => $setMetadataValue
                     ]);
                $saveMetadataLog[$schema['nick']] = [null, $setMetadataValue];
            }
            unset($set[$schema['nick']]);
        }

        // Verifica se algum dos metadados foi alterado e recarrega o campo resumo no PDV
        if (! empty($saveMetadataLog)) {
            $set['metadata'] = json_encode($this->getValues($foreignKey, true));
        }

        $this->lastSaveMetadataLog = $saveMetadataLog;

        return $set;
    }

    /**
     * Atualiza o JSON do campo metadata
     *
     * Sempre acesas direto o table gateway para não gerar log
     *
     * @param int $key
     * @param string $dbMetaField
     * @throws \Exception
     */
    public function fixMetadata($key, $dbMetaField = 'metadata')
    {
        // Verifica o código do PDV
        if (empty($key) || ! is_numeric($key)) {
            throw new \Exception('Código inválido em MetadaService::fixMetadata()');
        }

        // Recupera as informações do PDV
        $values = $this->getValues($key, true);

        // Cria o JSON
        $jsonValues = Json::encode($values);

        // Atualiza o PDV sem passar pelo log
        $this->getMapper()
            ->getTableGateway()
            ->update([$dbMetaField => $jsonValues], "{$this->getMapper()->getTableKey()}=$key");
    }

    public function getLastSaveMetadataLog()
    {
        return $this->lastSaveMetadataLog;
    }

    protected function getCorrectSetValue($schema, $value)
    {
        // Null is always null
        if (is_null($value)) {
            return null;
        }

        if ($schema['type'] == self::BOOLEAN) {
            if ($value === null || $value === '') {
                return null;
            }
            return ($value) ? 1 : 0;
        }

        if ($schema['type'] == self::INTEGER) {
            if ($value === null || $value === '') {
                return null;
            }
            return (int) $value;
        }

        if ($schema['type'] == self::DECIMAL) {
            if ($value === null || $value === '') {
                return null;
            }
            if (preg_replace('/[^0-9,\.]*/', '', $value) == '') {
                return 0;
            }
            // @todo como considerar as virgulas e pontos?!
            return $value;
        }

        if ($schema['type'] == self::TEXT) {
            if ($value === null || $value === '') {
                return null;
            }
            return $value;
        }

        if ($schema['type'] == self::DATE) {
            // Remove qualquer hora se houver
            $value = explode(' ', $value);
            $value = array_shift($value);
            if (\Realejo\Utils\DateHelper::isFormat('d/m/Y', $value)) {
                return \DateTime::createFromFormat('d/m/Y', $value)->format('Y-m-d');
            }
        }

        if ($schema['type'] == self::DATETIME && \Realejo\Utils\DateHelper::isFormat('d/m/Y H:i:s', $value)) {
            return \DateTime::createFromFormat('d/m/Y H:i:s', $value)->format('Y-m-d H:i:s');
        }

        if ($schema['type'] == self::DATETIME && \Realejo\Utils\DateHelper::isFormat('d/m/Y', $value)) {
            return \DateTime::createFromFormat('d/m/Y', $value)->format('Y-m-d 00:00:00');
        }

        return $value;
    }

    protected function getCorrectSetKey($schema)
    {
        if ($schema['type'] == self::INTEGER) {
            return 'value_integer';
        }

        if ($schema['type'] == self::BOOLEAN) {
            return 'value_boolean';
        }

        if ($schema['type'] == self::DATE) {
            return 'value_date';
        }

        if ($schema['type'] == self::DATETIME) {
            return 'value_datetime';
        }

        if ($schema['type'] == self::DECIMAL) {
            return 'value_decimal';
        }

        return 'value_text';
    }

    protected function getCurrentValue($schema, $value)
    {
        if ($schema['type'] == self::TEXT) {
            return $value['value_text'];
        }

        if ($schema['type'] == self::INTEGER) {
            return $value['value_integer'];
        }

        if ($schema['type'] == self::BOOLEAN) {
            return $value['value_boolean'];
        }

        if ($schema['type'] == self::DATE) {
            return $value['value_date'];
        }

        if ($schema['type'] == self::DATETIME) {
            return $value['value_datetime'];
        }

        if ($schema['type'] == self::DECIMAL) {
            return $value['value_decimal'];
        }

        return 'ERROR';
    }

    /**
     * Define se deve usar o cache
     *
     * @param boolean $useCache
     *
     * @return \Realejo\Metadata\MetadataService
     */
    public function setUseCache($useCache)
    {
        $this->useCache = $useCache;
        $this->getMapperSchema()->setUseCache($useCache);
        $this->getMapperValue()->setUseCache($useCache);
        $this->getMapper()->setUseCache($useCache);

        // Mantem a cadeia
        return $this;
    }

    /**
     * Apaga o cache
     *
     * Não precisa apagar o cache dos metadata pois é o mesmo do serviço
     */
    public function cleanCache()
    {
        $this->getCache()->flush();
        $this->getMapper()->getCache()->flush();
    }

    /**
     * @return boolean
     */
    public function getAutoCleanCache()
    {
        return $this->getMapper()->getAutoCleanCache();
    }

    /**
     * @param boolean $autoCleanCache
     *
     * @return self
     */
    public function setAutoCleanCache($autoCleanCache)
    {
        $this->getMapper()->setAutoCleanCache($autoCleanCache);
        $this->getMapperSchema()->setAutoCleanCache($autoCleanCache);
        $this->getMapperValue()->setAutoCleanCache($autoCleanCache);

        return $this;
    }

    public function getMapperSchema()
    {
        if (is_string($this->mapperSchema)) {
            $this->mapperSchema = new MetadataMapper($this->mapperSchema, $this->infoForeignKeyName);
            $this->mapperSchema->setCache($this->getCache());

            if ($this->hasServiceLocator()) {
                $this->mapperSchema->setServiceLocator($this->getServiceLocator());
            }
        }

        return $this->mapperSchema;
    }

    public function getMapperValue()
    {
        if (is_string($this->mapperValue)) {
            $this->mapperValue = new MetadataMapper($this->mapperValue, [$this->infoForeignKeyName, $this->referenceKey]);
            $this->mapperValue->setCache($this->getCache());

            if ($this->hasServiceLocator()) {
                $this->mapperValue->setServiceLocator($this->getServiceLocator());
            }
        }

        return $this->mapperValue;
    }

    /**
     * @return string
     */
    public function getReferenceKey()
    {
        return $this->referenceKey;
    }

    // modified quoteInto() function for ZF2
    private function quoteInto($text, $value, $platform, $count = null, $cast = 'INTEGER')
    {
        if ($count === null) {
            if ($cast === 'INTEGER') {
                return str_replace('?', intval($value), $text);
            } elseif ($cast === 'DECIMAL') {
                return str_replace('?', floatval($value), $text);
            } elseif ($cast === 'STRING') {
                return str_replace('?', $platform->quoteValue($value), $text);
            } else {
                throw new \Exception('CAST inválido em '.get_class($this).'::quoteInto');
            }
        } else {
            while ($count > 0) {
                if (strpos($text, '?') !== false) {
                    $text = substr_replace($text, $platform->quoteValue($value), strpos($text, '?'), 1);
                }
                --$count;
            }
            return $text;
        }
    }
}
