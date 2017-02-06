<?php
namespace Realejo\Mapper;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;
use Zend\Db\TableGateway\TableGateway;
use Zend\Hydrator\ArraySerializable;
use Realejo\Stdlib\ArrayObject;

abstract class MapperAbstract
{
    const KEY_STRING  = 'STRING';
    const KEY_INTEGER = 'INTEGER';

    /**
     * @var \Realejo\Stdlib\ArrayObject
     */
    protected $hydratorEntity = null;

    /**
     * @var \Zend\Hydrator\ArraySerializable
     */
    protected $hydrator = null;

    /**
     * Nome da tabela a ser usada
     * @var string
     */
    protected $tableName;

    /**
     * @todo tem que colocar no service locator
     * @var TableGateway
     */
    protected $tableGateway;

    /**
     * Define o nome da chave
     * @var string|array
     */
    protected $tableKey;

    /**
     * Join lefts que dfevem ser usados no mapper
     *
     * @var array
     */
    protected $tableJoinLeft = false;

    /**
     * Join lefts que devem ser usados no mapper
     *
     * @var array
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
     * @param string       $tableName Nome da tabela a ser usada
     * @param string|array $tableKey   Nome ou array de chaves a serem usadas
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
            $this->tableKey  = $tableKey;
        }
    }

    /**
     * {@inheritDoc}
     * @see RW_App_Model_Base::fetchAll()
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
    // Cria a assinatura da consulta
        if ($where instanceof Select) {
            $cacheKey = 'fetchAll'. md5($where->getSqlString());
        } else {
            $cacheKey = 'fetchAll'. md5(var_export($this->showDeleted, true) . var_export($where, true) . var_export($order, true) . var_export($count, true) . var_export($offset, true));
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
        if ( !is_null($fetchAll) && count($fetchAll) > 0 ) {
            // Passa o $fetch para array para poder incluir campos extras
            $fetchAll = $fetchAll->toArray();
        } else {
            $fetchAll = null;
        }

        // Grava a consulta no cache
        if ($this->getUseCache()) {
            $this->getCache()->setItem($cacheKey, $fetchAll);
        }

        if (empty($fetchAll)) {
            return $fetchAll;
        }

        $hydrator = $this->getHydrator();
        if (empty($hydrator)) {
            return $fetchAll;
        }
        $hydratorEntity = $this->getHydratorEntity();

        foreach ($fetchAll as $id=>$row) {
            $fetchAll[$id] = $hydrator->hydrate($row, new $hydratorEntity);
        }

        return $fetchAll;
    }

    /**
     * Recupera um registro
     *
     * @param mixed $where condições para localizar o registro
     *
     * @return array|null Array com o registro ou null se não localizar
     */
    public function fetchRow($where, $order = null)
    {
        // Define se é a chave da tabela
        if (is_numeric($where) || is_string($where)) {
            // Verifica se há chave definida
            if (empty($this->tableKey)) {
                throw new \InvalidArgumentException('Chave não definida em ' . get_class($this) . '::fetchRow()');

                // Verifica se é uma chave múltipla ou com cast
            } elseif (is_array($this->tableKey)) {

                // Verifica se é uma chave simples com cast
                if (count($this->tableKey) == 1) {
                    $where = array($this->getTableKey(true)=>$where);

                    // Não é possível acessar um registro com chave multipla usando apenas uma delas
                } else {
                    throw new \InvalidArgumentException('Não é possível acessar chaves múltiplas informando apenas uma em ' . get_class($this) . '::fetchRow()');
                }

            } else {
                $where = array($this->tableKey=>$where);
            }
        }

        // Recupera o registro
        $fetchRow = $this->fetchAll($where, $order, 1);

        // Retorna o registro se algum foi encontrado
        return (!empty($fetchRow))? $fetchRow[0] : null;
    }

    /**
     * Retorna o select para a consulta
     *
     * @param string|array $where  OPTIONAL An SQL WHERE clause
     * @param string|array $order  OPTIONAL An SQL ORDER clause.
     * @param int          $count  OPTIONAL An SQL LIMIT count.
     * @param int          $offset OPTIONAL An SQL LIMIT offset.
     *
     * @return \Zend\Db\Sql\Select
     */
    public function getSelect($where = null, $order = null, $count = null, $offset = null)
    {
        // Retorna o select para a tabela
        $select = $this->getTableSelect();

        // Verifica se existe ordem padrão
        if (empty($order) && isset($this->order)) {
            if (is_string($this->order) && strpos($this->order, '=') !== false) {
                $this->order = new Expression($this->order);
            }
            $order = $this->order;
        }

        // Define a ordem
        $select->order($order);

        // Verifica se há paginação, não confundir com o Zend_Paginator
        if (!is_null($count)) {
            $select->limit($count, $offset);
        }

        // Checks $where is not null
        if (empty($where)) {
            if ($this->getUseDeleted() && !$this->getShowDeleted()) {
                $where =  new Expression($this->getTableGateway()->getTable().'deleted = 0');
            }
        }

        // Verifica se é um array para fazer o processamento abaixo
        if (!is_array($where)) {
            $where = (empty($where)) ? array() : array($where);
        }

        // Checks $where is deleted
        if ($this->getUseDeleted() && !$this->getShowDeleted() && !isset($where['deleted'])) {
            $where['deleted'] = 0;
        }

        // Verifica as clausulas especiais se houver
        $where = $this->getWhere($where);

        // processa as clausulas
        foreach($where as $id=>$w) {
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
                if (strpos($id,'.') === false) $id = "{$this->tableName}.$id";
                $select->where(new \Zend\Db\Sql\Predicate\Operator($id, '=',$w));

                // Texto e Data
            } elseif (!is_numeric($id)) {
                if (strpos($id,'.') === false) $id = "{$this->tableName}.$id";
                $select->where(new \Zend\Db\Sql\Predicate\Operator($id, '=',$w));
            } else {
                throw new \LogicException("Condição inválida '$w' em " . get_class($this) . '::getSelect()');
            }
        }

        return $select;
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
            $key = array();
            foreach ($rightKeys as $k) {
                if (isset($set[$k])) {
                    $key[$k] = $set[$k];
                } else {
                    $key = false;
                    break;
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
     * Altera um registro
     *
     * @param array $set Dados a serem atualizados
     * @param int   $key Chave do registro a ser alterado
     *
     * @return boolean
     */
    public function update($set, $key)
    {
        // Verifica se o código é válido
        if ( empty($key) ) {
            throw new \InvalidArgumentException("O código <b>'$key'</b> inválido em " . get_class($this) . "::update()");
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
        if ($row instanceof \Realejo\Metadata\ArrayObject) {
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
        $this->lastUpdateSet  = $set;
        $this->lastUpdateKey  = $key;

        // Grava o que foi alterado
        $this->lastUpdateDiff = array();
        foreach ($diff as $field=>$value) {
            $this->lastUpdateDiff[$field] = array($row[$field], $value);
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
     * Excluí um registro
     *
     * @param int $key Código da registro a ser excluído
     *
     * @return bool Informa se teve o registro foi removido
     */
    public function delete($key)
    {
        if ( empty($key) ) {
            throw new \InvalidArgumentException("O código <b>'$key'</b> inválido em " . get_class($this) . "::delete()");
        }

        if ( !is_array($key) && is_array($this->getTableKey()) && count($this->getTableKey()) > 1) {
            throw new \InvalidArgumentException("Não é possível acessar direto uma coluna usando chaves múltiplas em " . get_class($this) . "::delete()");
        }

        // Grava os dados alterados para referencia
        $this->lastDeleteKey = $key;

        // Verifica se deve marcar como removido ou remover o registro
        if ($this->useDeleted === true) {
            $return = $this->getTableGateway()->update(array('deleted' => 1), $this->getKeyWhere($key));
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

    public function save($dados)
    {
        if (! isset($dados[$this->id])) {
            return $this->insert($dados);
        }

        // Caso não seja, envia um Exception
        if (! is_numeric($dados[$this->id])) {
            throw new \Exception("Inválido o Código '{$dados[$this->id]}' em '{$this->tableName}'::save()");
        }

        if ($this->fetchRow($dados[$this->id])) {
            return $this->update($dados, $dados[$this->id]);
        }

        throw new \Exception("{$this->id} key does not exist");
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
            throw new \LogicException('Chave mal definida em ' . get_class($this) . '::_getWhere()');
        }

        $where = array();
        $usedKeys = array();

        // Verifica as chaves definidas
        foreach ($this->getTableKey() as $type=>$definedKey) {

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

            }

            // Verifica se a chave definida foi informada
            elseif (is_array($key) && isset($key[$definedKey])) {

                // Grava a chave como integer
                if (is_numeric($type) || $type === self::KEY_INTEGER) {
                    $where[] = "$definedKey = {$key[$definedKey]}";

                    // Grava a chave como string
                } elseif ($type === self::KEY_STRING) {
                    $where[] = "$definedKey = '{$key[$definedKey]}'";
                }

                // Marca a chave com usada
                $usedKeys[] = $definedKey;

            }
        }

        // Verifica se alguma chave foi definida
        if (empty($where)) {
            throw new \LogicException('Nenhuma chave múltipla informada em ' . get_class($this) . '::_getWhere()');
        }

        // Verifica se todas as chaves foram usadas
        if ($this->getUseAllKeys() === true && is_array($this->getTableKey()) && count($usedKeys) !== count($this->getTableKey())) {
            throw new \LogicException('Não é permitido usar chaves parciais ' . get_class($this) . '::_getWhere()');
        }

        return '(' . implode(') AND (', $where). ')';
    }

    public function getCache()
    {
        if (!isset($this->cache)) {
            $this->cache = \Realejo\Utils\Cache::getFrontend(str_replace('\\',DIRECTORY_SEPARATOR, get_class($this)));
        }

        return $this->cache;
    }

    public function setCache($cache)
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * Define se deve usar o cache
     *
     * @param boolean $useCache
     * @return $this
     */
    public function setUseCache($useCache)
    {
        // Grava o cache
        $this->useCache = $useCache;

        // Mantem a cadeia
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
     * @return boolean
     */
    public function getUseAllKeys ()
    {
        return $this->useAllKeys;
    }

    /**
     * @param boolean $useAllKeys
     *
     * @return $this
     */
    public function setUseAllKeys ($useAllKeys)
    {
        $this->useAllKeys = $useAllKeys;

        return $this;
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
     * @param string $table
     *
     * @return TableGateway
     */
    public function getTableGateway($table = null)
    {
        if (empty($table) && isset($this->tableName)) {
            $table = $this->tableName;
        }

        if (empty($table)) {
            throw new \InvalidArgumentException('Tabela não definida em ' . get_class($this) . '::getTableGateway()');
        }

        // Verifica se a tabela já foi previamente carregada
        if (!isset($this->tableGateway)) {
            $this->tableGateway = new TableGateway($table, GlobalAdapterFeature::getStaticAdapter());
        }

        // Retorna a tabela
        return $this->tableGateway;
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
            foreach($this->tableJoinLeft as $tableJoinLeft) {
                #TODO validar se tem os tres campos no array

                if (empty($tableJoinLeft['table']) && !is_string($tableJoinLeft['table'])) {
                    throw new \InvalidArgumentException('Tabela não definida em ' . get_class($this) . '::getTableSelect()');
                }

                if (empty($tableJoinLeft['condition']) && !is_string($tableJoinLeft['condition'])) {
                    throw new \InvalidArgumentException('Condição não definida em ' . get_class($this) . '::getTableSelect()');
                }

                if (isset($tableJoinLeft['columns']) && !empty($tableJoinLeft['columns']) && !is_array($tableJoinLeft['columns'])) {
                    throw new \InvalidArgumentException('Colunas devem ser um array em ' . get_class($this) . '::getTableSelect()');
                }

                if (isset($tableJoinLeft['schema']) && !empty($tableJoinLeft['schema']) && !is_string($tableJoinLeft['schema'])) {
                    throw new \InvalidArgumentException('Schema devem ser uma string em ' . get_class($this) . '::getTableSelect()');
                }

                $select->join($tableJoinLeft['table'], $tableJoinLeft['condition'], $tableJoinLeft['columns'], $tableJoinLeft['schema']);
            }
        }

        return $select;
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
                foreach($key as $type=>$keyName) {
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
     * @return self
     */
    public function setTableKey($key)
    {
        if (empty($key) && !is_string($key) && !is_array($key)) {
            throw new \InvalidArgumentException('Chave inválida em ' . get_class($this) . '::setTableKey()');
        }

        $this->tableKey = $key;

        return $this;
    }

    /**
     * @return boolean $tableJoinLeft
     */
    public function getTableJoinLeft()
    {
        return $this->tableJoinLeft;
    }

    /**
     * @param array $tableJoinLeft
     */
    public function setTableJoinLeft($tableJoinLeft)
    {
        $this->tableJoinLeft = $tableJoinLeft;
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
     */
    public function setUseJoin($useJoin)
    {
        $this->useJoin = $useJoin;
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
     * @return self
     */
    public function setOrder($order)
    {
        if (empty($order) && !is_string($order) && !is_array($order) && ( ! $order instanceof Expression)) {
            throw new \InvalidArgumentException('Chave inválida em ' . get_class($this) . '::setOrder()');
        }

        $this->order = $order;

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
     * @return  self
     */
    public function setUseDeleted($useDeleted)
    {
        $this->useDeleted = $useDeleted;

        // Mantem a cadeia
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
     * @return  self
     */
    public function setShowDeleted($showDeleted)
    {
        $this->showDeleted = $showDeleted;

        // Mantem a cadeia
        return $this;
    }

    /**
     *
     * @return \Zend\Hydrator\ArraySerializable
     */
    public function getHydrator()
    {
        return new ArraySerializable();
    }

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
     * @param \Realejo\Stdlib\ArrayObject $hydratorEntity
     */
    public function setHydratorEntity($hydratorEntity)
    {
        $this->hydratorEntity = $hydratorEntity;
    }

    /**
     * @param \Zend\Hydrator\ArraySerializable $hydrator
     * @throws \Exception
     */
    public function setHydrator($hydrator)
    {
        if (empty($this->hydrator)) {
            throw new \Exception('Invalid hydrator at ' . get_class($this));
        }
        $this->hydrator = $hydrator;
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
     * @return self
     */
    public function setAutoCleanCache($autoCleanCache)
    {
        $this->autoCleanCache = $autoCleanCache;

        return $this;
    }

}
