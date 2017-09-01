<?php

namespace Realejo\Service;

use Psr\Container\ContainerInterface;
use Realejo\Stdlib\ArrayObject;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;
use Zend\Db\TableGateway\TableGateway;
use Zend\Hydrator\ArraySerializable;

abstract class MapperAbstract
{
    const KEY_STRING = 'STRING';
    const KEY_INTEGER = 'INTEGER';

    /**
     * @var ArrayObject
     */
    protected $hydratorEntity = null;
    /**
     * @var ArraySerializable
     */
    protected $hydrator = null;
    /**
     * Nome da tabela a ser usada
     * @var string
     */
    protected $tableName;
    /**
     * @var TableGateway
     */
    protected $tableGateway;
    /**
     * Define o nome da chave
     * @var string|array
     */
    protected $tableKey;
    /**
     * Join lefts que devem ser usados no mapper
     *
     * @var array
     */
    protected $tableJoinLeft = false;
    /**
     * Join lefts que devem ser usados no mapper
     *
     * @var boolean
     */
    protected $useJoin = false;
    /**
     * Define se deve usar todas as chaves para os operações de update e delete
     *
     * @var boolean
     */
    protected $useAllKeys = true;
    /**
     * Define a ordem padrão a ser usada na consultas
     *
     * @var string|array
     */
    protected $order;
    /**
     * Define o adapter a ser usado
     *
     * @var Adapter
     */
    protected $adapter;
    /**
     * Define se deve remover os registros ou apenas marcar como removido
     *
     * @var boolean
     */
    protected $useDeleted = false;
    /**
     * Define se deve mostrar os registros marcados como removido
     *
     * @var boolean
     */
    protected $showDeleted = false;
    /**
     * @var boolean
     */
    protected $useCache = false;
    /**
     * @var ContainerInterface
     */
    protected $serviceLocator;
    /**
     * @var boolean
     */
    protected $autoCleanCache = true;
    protected $cache;
    protected $lastInsertSet;
    protected $lastInsertKey;
    protected $lastUpdateSet;
    protected $lastUpdateDiff;
    protected $lastUpdateKey;
    protected $lastDeleteKey;

    /**
     *
     * @param string $tableName Nome da tabela a ser usada
     * @param string|array $tableKey Nome ou array de chaves a serem usadas
     *
     */
    public function __construct($tableName = null, $tableKey = null)
    {
        // Verifica o nome da tabela
        if (!empty($tableName) && is_string($tableName)) {
            $this->tableName = $tableName;
        }

        // Verifica o nome da chave
        if (!empty($tableKey) && (is_string($tableKey) || is_array($tableKey))) {
            $this->tableKey = $tableKey;
        }
    }

    /**
     * Excluí um registro
     *
     * @param int|array $key Código da registro a ser excluído
     *
     * @return bool Informa se teve o registro foi removido
     */
    public function delete($key)
    {
        if (empty($key)) {
            throw new \InvalidArgumentException("Chave <b>'$key'</b> inválida");
        }

        if (!is_array($key) && is_array($this->getTableKey()) && count($this->getTableKey()) > 1) {
            throw new \InvalidArgumentException('Não é possível apagar um registro usando chaves múltiplas parciais');
        }

        // Grava os dados alterados para referencia
        $this->lastDeleteKey = $key;

        // Verifica se deve marcar como removido ou remover o registro
        if ($this->useDeleted === true) {
            $return = $this->getTableGateway()->update(['deleted' => 1], $this->getKeyWhere($key));
        } else {
            $return = $this->getTableGateway()->delete($this->getKeyWhere($key));
        }

        // Limpa o cache se necessario
        if ($this->getUseCache() && $this->getAutoCleanCache()) {
            $this->getCache()->flush();
        }

        // Retorna se o registro foi excluído
        return $return;
    }

    /**
     * Retorna a chave definida para a tabela
     *
     * @param bool $returnSingle Quando for uma chave multipla, use TRUE para retorna a primeira chave
     * @return array|string
     */
    public function getTableKey($returnSingle = false)
    {
        $key = $this->tableKey;

        // Verifica se é para retorna apenas a primeira da chave multipla
        if (is_array($key) && $returnSingle === true) {
            if (is_array($key)) {
                foreach ($key as $type => $keyName) {
                    $key = $keyName;
                    break;
                }
            }
        }

        return $key;
    }

    /**
     * @param string|array
     *
     * @return MapperAbstract
     */
    public function setTableKey($key)
    {
        if (empty($key) && !is_string($key) && !is_array($key)) {
            throw new \InvalidArgumentException('Chave inválida em ' . get_class($this));
        }

        $this->tableKey = $key;

        return $this;
    }

    /**
     * @return TableGateway
     */
    public function getTableGateway()
    {
        if (null === $this->tableName) {
            throw new \InvalidArgumentException('Tabela não definida em ' . get_class($this));
        }

        // Verifica se a tabela já foi previamente carregada
        if (null === $this->tableGateway) {
            $this->tableGateway = new TableGateway($this->tableName, $this->getAdapter());
        }

        // Retorna a tabela
        return $this->tableGateway;
    }

    /**
     * @return Adapter
     */
    public function getAdapter()
    {
        if (null === $this->adapter) {
            if ($this->hasServiceLocator() && $this->getServiceLocator()->has(Adapter::class)) {
                $this->adapter = $this->getServiceLocator()->get(Adapter::class);
                return $this->adapter;
            }

            $this->adapter = GlobalAdapterFeature::getStaticAdapter();
        }

        return $this->adapter;
    }

    /**
     * @param Adapter $adapter
     *
     * @return MapperAbstract
     */
    public function setAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    public function hasServiceLocator()
    {
        return null !== $this->serviceLocator;
    }

    /**
     * @return ContainerInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param ContainerInterface $serviceLocator
     * @return MapperAbstract
     */
    public function setServiceLocator(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     * Retorna a chave no formato que ela deve ser usada
     *
     * @param Expression|string|array $key
     *
     * @return Expression|string
     */
    protected function getKeyWhere($key)
    {
        if ($key instanceof Expression) {
            return $key;
        }

        if (is_string($this->getTableKey()) && is_numeric($key)) {
            return "{$this->getTableKey()} = $key";
        }

        if (is_string($this->getTableKey()) && is_string($key)) {
            return "{$this->getTableKey()} = '$key'";
        }

        if (!is_array($this->getTableKey())) {
            throw new \LogicException('Chave mal definida em ' . get_class($this));
        }

        $where = [];
        $usedKeys = [];

        // Verifica as chaves definidas
        foreach ($this->getTableKey() as $type => $definedKey) {
            // Verifica se é uma chave única com cast
            if (count($this->getTableKey()) === 1 && !is_array($key)) {
                // Grava a chave como integer
                if (is_numeric($type) || $type === self::KEY_INTEGER) {
                    $where[] = "$definedKey = $key";

                    // Grava a chave como string
                } elseif ($type === self::KEY_STRING) {
                    $where[] = "$definedKey = '$key'";
                }

                $usedKeys[] = $definedKey;
            } // Verifica se a chave definida foi informada
            elseif (is_array($key) && !is_array($definedKey) && isset($key[$definedKey])) {
                // Grava a chave como integer
                if (is_numeric($type) || $type === self::KEY_INTEGER) {
                    $where[] = "$definedKey = {$key[$definedKey]}";

                    // Grava a chave como string
                } elseif ($type === self::KEY_STRING) {
                    $where[] = "$definedKey = '{$key[$definedKey]}'";
                }

                // Marca a chave com usada
                $usedKeys[] = $definedKey;
            } elseif (is_array($key) && is_array($definedKey)) {
                foreach ($definedKey as $value) {
                    // Grava a chave como integer
                    if (is_numeric($value) || $type === self::KEY_INTEGER) {
                        $where[] = "$value = {$key[$value]}";

                        // Grava a chave como string
                    } elseif ($type === self::KEY_STRING) {
                        $where[] = "$value = '{$key[$value]}'";
                    }
                }

                // Marca a chave com usada
                $usedKeys[] = $definedKey;
            }

        }

        // Verifica se alguma chave foi definida
        if (empty($where)) {
            throw new \LogicException('Nenhuma chave definida em ' . get_class($this));
        }

        // Verifica se todas as chaves foram usadas
        if ($this->getUseAllKeys() === true
            && is_array($this->getTableKey())
            && count($usedKeys) !== count($this->getTableKey())
        ) {
            throw new \LogicException('Não é permitido usar chaves parciais em ' . get_class($this));
        }

        return '(' . implode(') AND (', $where) . ')';
    }

    /**
     * @return boolean
     */
    public function getUseAllKeys()
    {
        return $this->useAllKeys;
    }

    /**
     * @param boolean $useAllKeys
     *
     * @return $this
     */
    public function setUseAllKeys($useAllKeys)
    {
        $this->useAllKeys = $useAllKeys;

        return $this;
    }

    /**
     * Retorna se deve usar o cache
     *
     * @return boolean
     */
    public function getUseCache()
    {
        return $this->useCache;
    }

    /**
     * Define se deve usar o cache
     *
     * @param boolean $useCache
     * @return MapperAbstract
     */
    public function setUseCache($useCache)
    {
        $this->useCache = $useCache;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getAutoCleanCache()
    {
        return $this->autoCleanCache;
    }

    /**
     * @param boolean $autoCleanCache
     *
     * @return MapperAbstract
     */
    public function setAutoCleanCache($autoCleanCache)
    {
        $this->autoCleanCache = $autoCleanCache;

        return $this;
    }

    /**
     * @return \Zend\Cache\Storage\Adapter\Filesystem
     */
    public function getCache()
    {
        if (!isset($this->cache)) {
            $this->cache = \Realejo\Utils\Cache::getFrontend(str_replace('\\', DIRECTORY_SEPARATOR, get_class($this)));
        }

        return $this->cache;
    }

    /**
     * @param $cache
     * @return MapperAbstract
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * @param $set
     * @return bool|int
     * @throws \Exception
     */
    public function save($set)
    {
        if (!isset($set[$this->getTableKey()])) {
            return $this->insert($set);
        }

        // Caso não seja, envia um Exception
        if (!is_numeric($set[$this->getTableKey()])) {
            throw new \Exception("Chave invalida: '{$set[$this->getTableKey()]}'");
        }

        if ($this->fetchRow($set[$this->getTableKey()])) {
            return $this->update($set, $set[$this->getTableKey()]);
        }

        throw new \Exception("{$this->getTableKey()} key does not exist in " . get_class($this));
    }

    /**
     * Grava um novo registro
     *
     * @param $set
     * @return int boolean
     *
     */
    public function insert($set)
    {
        // Verifica se há algo a ser adicionado
        if (empty($set)) {
            return false;
        }

        // Grava o ultimo set incluído para referencia
        $this->lastInsertSet = $set;
        // Cria um objeto para conseguir usar o hydrator
        if (is_array($set)) {
            $set = new ArrayObject($set);
        }

        $hydrator = $this->getHydrator();
        $set = $hydrator->extract($set);

        // Remove os campos vazios
        foreach ($set as $field => $value) {
            if (is_string($value)) {
                $set[$field] = trim($value);
                if ($set[$field] === '') {
                    $set[$field] = null;
                }
            }
        }

        // Grava o set no BD
        $this->getTableGateway()->insert($set);

        // Recupera a chave gerada do registro
        if (is_array($this->getTableKey())) {
            $rightKeys = $this->getTableKey();
            $key = [];
            foreach ($rightKeys as $type => $k) {
                if (is_array($k)) {
                    foreach ($k as $value) {
                        // Grava a chave como integer
                        if (is_numeric($value) || $type === self::KEY_INTEGER) {
                            $key[$value] = $set[$value];

                            // Grava a chave como string
                        } elseif ($type === self::KEY_STRING) {
                            $key[$value] = $set[$value];
                        }
                    }
                } else {
                    if (isset($set[$k])) {
                        $key[$k] = $set[$k];
                    } else {
                        $key = false;
                        break;
                    }
                }
            }
        } elseif (isset($set[$this->getTableKey()])) {
            $key = $set[$this->getTableKey()];
        }

        if (empty($key)) {
            $key = $this->getTableGateway()->getAdapter()->getDriver()->getLastGeneratedValue();
        }

        // Grava a chave criada para referencia
        $this->lastInsertKey = $key;

        // Limpa o cache se necessário
        if ($this->getUseCache() && $this->getAutoCleanCache()) {
            $this->getCache()->flush();
        }

        // Retorna o código do registro recem criado
        return $key;
    }

    /**
     * @return ArraySerializable
     */
    public function getHydrator()
    {
        return new ArraySerializable();
    }

    /**
     * @param ArraySerializable $hydrator
     *
     * @return MapperAbstract
     *
     * @throws \Exception
     *
     */
    public function setHydrator(ArraySerializable $hydrator)
    {
        if (empty($hydrator)) {
            throw new \Exception('Invalid hydrator');
        }
        $this->hydrator = $hydrator;

        return $this;
    }

    /**
     * Recupera um registro
     *
     * @param mixed $where condições para localizar o registro
     * @param null $order
     *
     * @return null|ArrayObject
     */
    public function fetchRow($where, $order = null)
    {
        // Define se é a chave da tabela
        if (is_numeric($where) || is_string($where)) {
            // Verifica se há chave definida
            if (empty($this->tableKey)) {
                throw new \InvalidArgumentException('Chave não definida em ' . get_class($this));
            }

            // Verifica se é uma chave múltipla ou com cast
            if (is_array($this->tableKey)) {
                // Verifica se é uma chave simples com cast
                if (count($this->tableKey) != 1) {
                    throw new \InvalidArgumentException('Não é possível acessar chaves múltiplas informando apenas uma');
                }
                $where = [$this->getTableKey(true) => $where];
            } else {
                $where = [$this->tableKey => $where];
            }
        }

        // Recupera o registro
        $fetchRow = $this->fetchAll($where, $order, 1);

        // Retorna o registro se algum foi encontrado
        return (!empty($fetchRow)) ? $fetchRow[0] : null;
    }

    /**
     * @param null $where
     * @param null $order
     * @param null $count
     * @param null $offset
     * @return ArrayObject[]
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        // Cria a assinatura da consulta
        if ($where instanceof Select) {
            $cacheKey = 'fetchAll' . md5($where->getSqlString());
        } else {
            $cacheKey = 'fetchAll'
                . md5(
                    var_export($this->showDeleted, true)
                    . var_export($this->useJoin, true)
                    . var_export($where, true)
                    . var_export($order, true)
                    . var_export($count, true)
                    . var_export($offset, true)
                );
        }

        // Verifica se tem no cache
        // o Zend_Paginator precisa do Zend_Paginator_Adapter_DbSelect para acessar o cache
        if ($this->getUseCache() && $this->getCache()->hasItem($cacheKey)) {
            return $this->getCache()->getItem($cacheKey);
        }

        // Define a consulta
        if ($where instanceof Select) {
            $select = $where;
        } else {
            $select = $this->getSelect($where, $order, $count, $offset);
        }

        // Recupera os registros do banco de dados
        $fetchAll = $this->getTableGateway()->selectWith($select);

        // Verifica se foi localizado algum registro
        if (!is_null($fetchAll) && count($fetchAll) > 0) {
            // Passa o $fetch para array para poder incluir campos extras
            $fetchAll = $fetchAll->toArray();
        } else {
            $fetchAll = null;
        }

        if (empty($fetchAll)) {
            // Grava a consulta no cache
            if ($this->getUseCache()) {
                $this->getCache()->setItem($cacheKey, $fetchAll);
            }
            return $fetchAll;
        }

        $hydrator = $this->getHydrator();
        if (empty($hydrator)) {
            // Grava a consulta no cache
            if ($this->getUseCache()) {
                $this->getCache()->setItem($cacheKey, $fetchAll);
            }
            return $fetchAll;
        }
        $hydratorEntity = $this->getHydratorEntity();

        foreach ($fetchAll as $id => $row) {
            $fetchAll[$id] = $hydrator->hydrate($row, new $hydratorEntity);
        }

        // Grava a consulta no cache
        if ($this->getUseCache()) {
            $this->getCache()->setItem($cacheKey, $fetchAll);
        }

        return $fetchAll;
    }

    /**
     * Retorna o select para a consulta
     *
     * @param string|array $where OPTIONAL An SQL WHERE clause
     * @param string|array $order OPTIONAL An SQL ORDER clause.
     * @param int $count OPTIONAL An SQL LIMIT count.
     * @param int $offset OPTIONAL An SQL LIMIT offset.
     *
     * @return \Zend\Db\Sql\Select
     */
    public function getSelect($where = null, $order = null, $count = null, $offset = null)
    {
        // Retorna o select para a tabela
        $select = $this->getTableSelect();

        // Verifica se existe ordem padrão
        if ($order === false) {
            $select->reset('order');
        } elseif (empty($order) && isset($this->order)) {
            if (is_string($this->order) && strpos($this->order, '=') !== false) {
                $this->order = new Expression($this->order);
            }
            $order = $this->order;
        }

        // Define a ordem
        if (!empty($order)) {
            $select->order($order);
        }

        // Verifica se há paginação, não confundir com o Zend\Paginator
        if (!is_null($count)) {
            $select->limit($count);
        }
        if (!is_null($offset)) {
            $select->offset($offset);
        }

        // Checks $where is not null
        if (empty($where)) {
            if ($this->getUseDeleted() && !$this->getShowDeleted()) {
                $where = new Expression($this->getTableGateway()->getTable() . 'deleted = 0');
            }
        }

        // Verifica se é um array para fazer o processamento abaixo
        if (!is_array($where)) {
            $where = (empty($where)) ? [] : [$where];
        }

        // Checks $where is deleted
        if ($this->getUseDeleted() && !$this->getShowDeleted() && !isset($where['deleted'])) {
            $where['deleted'] = 0;
        }

        // Verifica as clausulas especiais se houver
        $where = $this->getWhere($where);

        // processa as clausulas
        foreach ($where as $id => $w) {
            // \Zend\Db\Sql\Expression
            if ($w instanceof Expression) {
                $select->where($w);

                // Checks is deleted
            } elseif ($id === 'deleted' && $w === false) {
                $select->where("{$this->getTableGateway()->getTable()}.deleted=0");
                unset($where['deleted']);
            } elseif ($id === 'deleted' && $w === true) {
                $select->where("{$this->getTableGateway()->getTable()}.deleted=1");
                unset($where['deleted']);
            } elseif ((is_numeric($id) && $w === 'ativo') || ($id === 'ativo' && $w === true)) {
                $select->where("{$this->getTableGateway()->getTable()}.ativo=1");
                unset($where['ativo']);
            } elseif ($id === 'ativo' && $w === false) {
                $select->where("{$this->getTableGateway()->getTable()}.ativo=0");
                unset($where['ativo']);

                // Valor numerico
            } elseif (!is_numeric($id) && is_numeric($w)) {
                if (strpos($id, '.') === false) {
                    $id = "{$this->tableName}.$id";
                }
                $select->where(new \Zend\Db\Sql\Predicate\Operator($id, '=', $w));

                // Texto e Data
            } elseif (!is_numeric($id)) {
                if (strpos($id, '.') === false) {
                    $id = "{$this->tableName}.$id";
                }
                
                if (is_null($w)){
                    $select->where(new \Zend\Db\Sql\Predicate\IsNull($id));
                } else {
                    $select->where(new \Zend\Db\Sql\Predicate\Operator($id, '=', $w));
                }
            } else {
                throw new \LogicException("Condição inválida '$w' em " . get_class($this));
            }
        }

        return $select;
    }

    /**
     * Retorna o select a ser usado no fetchAll e fetchRow
     *
     * @return \Zend\Db\Sql\Select
     */
    public function getTableSelect()
    {
        $select = $this->getTableGateway()->getSql()->select();

        if ($this->getUseJoin() && !empty($this->tableJoinLeft)) {
            foreach ($this->tableJoinLeft as $tableJoinLeft) {
                #TODO validar se tem os tres campos no array

                if (empty($tableJoinLeft['table']) && !is_string($tableJoinLeft['table'])) {
                    throw new \InvalidArgumentException('Tabela não definida em ' . get_class($this));
                }

                if (empty($tableJoinLeft['condition']) && !is_string($tableJoinLeft['condition'])) {
                    throw new \InvalidArgumentException('Condição não definida' . get_class($this));
                }

                if (isset($tableJoinLeft['columns']) && !empty($tableJoinLeft['columns'])
                    && !is_array($tableJoinLeft['columns'])
                ) {
                    throw new \InvalidArgumentException('Colunas devem ser um array em ' . get_class($this));
                }

                if (isset($tableJoinLeft['schema']) && !empty($tableJoinLeft['schema'])
                    && !is_string($tableJoinLeft['schema'])
                ) {
                    throw new \InvalidArgumentException('Schema devem ser uma string em ' . get_class($this));
                } elseif (!isset($tableJoinLeft['schema'])) {
                    $tableJoinLeft['schema'] = null;
                }

                $select->join(
                    $tableJoinLeft['table'],
                    $tableJoinLeft['condition'],
                    $tableJoinLeft['columns'],
                    $tableJoinLeft['schema']
                );
            }
        }

        return $select;
    }

    /**
     * @return boolean
     */
    public function getUseJoin()
    {
        return $this->useJoin;
    }

    /**
     * @param boolean $useJoin
     *
     * @return MapperAbstract
     */
    public function setUseJoin($useJoin)
    {
        $this->useJoin = $useJoin;
        return $this;
    }

    /**
     * Retorna se irá usar o campo deleted ou remover o registro quando usar delete()
     *
     * @return boolean
     */
    public function getUseDeleted()
    {
        return $this->useDeleted;
    }

    /**
     * Define se irá usar o campo deleted ou remover o registro quando usar delete()
     *
     * @param boolean $useDeleted
     *
     * @return MapperAbstract
     */
    public function setUseDeleted($useDeleted)
    {
        $this->useDeleted = $useDeleted;

        return $this;
    }

    /**
     * Retorna se deve retornar os registros marcados como removidos
     *
     * @return boolean
     */
    public function getShowDeleted()
    {
        return $this->showDeleted;
    }

    /**
     * Define se deve retornar os registros marcados como removidos
     *
     * @param boolean $showDeleted
     *
     * @return MapperAbstract
     */
    public function setShowDeleted($showDeleted)
    {
        $this->showDeleted = $showDeleted;

        return $this;
    }

    /**
     * Processa as clausulas especiais do where
     *
     * @param array|string $where
     *
     * @return array
     */
    public function getWhere($where)
    {
        return $where;
    }

    /**
     * @param bool $asObject
     * @return ArrayObject
     */
    public function getHydratorEntity($asObject = true)
    {
        if ($asObject === false) {
            return $this->hydratorEntity;
        }

        if (isset($this->hydratorEntity)) {
            $hydrator = $this->hydratorEntity;
            return new $hydrator();
        }

        return new ArrayObject();
    }

    /**
     * @param ArrayObject $hydratorEntity
     *
     * @return MapperAbstract
     */
    public function setHydratorEntity(ArrayObject $hydratorEntity)
    {
        $this->hydratorEntity = $hydratorEntity;

        return $this;
    }

    /**
     * Altera um registro
     *
     * @param array $set Dados a serem atualizados
     * @param int|array $key Chave do registro a ser alterado
     *
     * @return boolean
     */
    public function update($set, $key)
    {
        // Verifica se o código é válido
        if (empty($key)) {
            throw new \InvalidArgumentException("Chave <b>'$key'</b> inválida");
        }

        // Verifica se há algo para alterar
        if (empty($set)) {
            return false;
        }

        // Cria um objeto para conseguir usar o hydrator
        if (is_array($set)) {
            $set = new ArrayObject($set);
        }

        // Recupera os dados existentes
        $row = $this->fetchRow($key);

        // Verifica se existe o registro
        if (empty($row)) {
            return false;
        }

        $hydrator = $this->getHydrator();
        $row = $hydrator->extract($row);
        $set = $hydrator->extract($set);

        //@todo Quem deveria fazer isso é o hydrator!
        if ($row instanceof Metadata\ArrayObject) {
            $row = $row->toArray();
            if (isset($row['metadata'])) {
                $row[$row->getMappedKeyname('metadata', true)] = $row['metadata'];
                unset($row['metadata']);
            }
        }

        // Não sei de onde está vindo esse info em array
        if (isset($row['infos']) && is_array($row['infos'])) {
            $row['infos'] = json_encode($row['infos']);
        }

        // Remove os campos vazios
        foreach ($set as $field => $value) {
            if (is_string($value)) {
                $set[$field] = trim($value);
                if ($set[$field] === '') {
                    $set[$field] = null;
                }
            }
        }

        // Verifica se há o que atualizar
        $diff = array_diff_assoc($set, $row);

        // Grava os dados alterados para referencia
        $this->lastUpdateSet = $set;
        $this->lastUpdateKey = $key;

        // Grava o que foi alterado
        $this->lastUpdateDiff = [];
        foreach ($diff as $field => $value) {
            $this->lastUpdateDiff[$field] = [$row[$field], $value];
        }

        // Verifica se há algo para atualizar
        if (empty($diff)) {
            return false;
        }

        // Salva os dados alterados
        $return = $this->getTableGateway()->update($diff, $this->getKeyWhere($key));

        // Limpa o cache, se necessário
        if ($this->getUseCache() && $this->getAutoCleanCache()) {
            $this->getCache()->flush();
        }

        // Retorna que o registro foi alterado
        return $return;
    }

    /**
     *
     * @return array
     */
    public function getLastInsertSet()
    {
        return $this->lastInsertSet;
    }

    /**
     *
     * @return int
     */
    public function getLastInsertKey()
    {
        return $this->lastInsertKey;
    }

    /**
     *
     * @return array
     */
    public function getLastUpdateSet()
    {
        return $this->lastUpdateSet;
    }

    /**
     *
     * @return array
     */
    public function getLastUpdateDiff()
    {
        return $this->lastUpdateDiff;
    }

    /**
     *
     * @return int
     */
    public function getLastUpdateKey()
    {
        return $this->lastUpdateKey;
    }

    /**
     *
     * @return int
     */
    public function getLastDeleteKey()
    {
        return $this->lastDeleteKey;
    }

    /**
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return array $tableJoinLeft
     */
    public function getTableJoinLeft()
    {
        return $this->tableJoinLeft;
    }

    /**
     * @param array $tableJoinLeft
     *
     * @return MapperAbstract
     */
    public function setTableJoinLeft($tableJoinLeft)
    {
        $this->tableJoinLeft = $tableJoinLeft;

        return $this;
    }

    /**
     *
     * @return string|array
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     *
     * @param string|array|Expression $order
     *
     * @return MapperAbstract
     */
    public function setOrder($order)
    {
        if (empty($order) && !is_string($order) && !is_array($order) && (!$order instanceof Expression)) {
            throw new \InvalidArgumentException('Ordem inválida em ' . get_class($this));
        }

        $this->order = $order;

        return $this;
    }
}
