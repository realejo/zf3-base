<?php
namespace Realejo\Service;

use Realejo\Stdlib\ArrayObject;

abstract class ServiceAbstract
{

    /**
     * @var MapperAbstract
     */
    protected $mapper;

    /**
     * @var string
     */
    protected $mapperClass = null;

    /**
     * @var boolean
     */
    protected $useCache = false;

    /**
     * @var \Zend\Cache\Service\StorageCacheFactory
     */
    protected $cache;

    /**
     * Campo a ser usado no <option>
     *
     * @var string
     */
    protected $htmlSelectOption = '{nome}';

    /**
     * Campos a serem adicionados no <option> como data
     *
     * @var string|array
     */
    protected $htmlSelectOptionData;

    /**
     * Retorna o HTML de um <select> apra usar em formulários
     *
     * @param string $nome        Name/ID a ser usado no <select>
     * @param string $selecionado Valor pré seleiconado
     * @param string $opts        Opções adicionais
     *
     * Os valores de option serão os valores dos campos definidos em $htmlSelectOption
     * Aos options serão adicionados data-* de acordo com os campos definidos em $htmlSelectOptionData
     *
     * Quando usar chaves multiplas será usada sempre a primeira, a menos que use o parametro 'key' abaixo
     *
     * As opções adicionais podem ser
     *  - where       => filtro para ser usando no fetchAll()
     *  - placeholder => legenda quando nenhum estiver selecionado e/ou junto com show-empty
     *                   se usdo com FALSE, nunca irá mostrar o vazio, mesmo que não tenha um selecionado
     *  - show-empty  => mostra um <option> vazio no inicio mesmo com um selecionado
     *  - grouped     => mostra o <optgroup> usando com label e agregador o campo informado
     *  - key         => campo a ser usado como chave, se não informado será usado a chave definida
     *
     * @return string
     */
    public function getHtmlSelect($nome, $selecionado = null, $opts = null)
    {
        // Recupera os registros
        $where = (isset($opts['where'])) ? $opts['where'] : null;
        $findAll = $this->findAll($where);

        // Verifica o select_option_data
        if (isset($this->htmlSelectOptionData) && is_string($this->htmlSelectOptionData)) {
            $this->htmlSelectOptionData = [
                $this->htmlSelectOptionData
            ];
        }

        // Verifica se deve mostrar a primeira opção em branco
        $showEmpty = (isset($opts['show-empty']) && $opts['show-empty'] === true);
        $neverShowEmpty = (isset($opts['show-empty']) && $opts['show-empty'] === false);

        // Define ao placeholder a ser usado
        $placeholder = $selectPlaceholder = (isset($opts['placeholder'])) ? $opts['placeholder'] : '';
        if (! empty($placeholder)) {
            $selectPlaceholder = "placeholder=\"$selectPlaceholder\"";
        }

        $grouped = (isset($opts['grouped'])) ? $opts['grouped'] : false;

        // Define a chave a ser usada
        if (isset($opts['key']) && ! empty($opts['key']) && is_string($opts['key'])) {
            $key = $opts['key'];
        } else {
            $key = $this->getMapper()->getTableKey(true);
        }

        // Monta as opções
        $options = '';
        $group = false;
        if (! empty($findAll)) {
            foreach ($findAll as $row) {
                preg_match_all('/\{([a-z_]*)\}/', $this->htmlSelectOption, $matches);

                // Troca pelos valores
                foreach ($matches[1] as $i => $m) {
                    $matches[1][$i] = (isset($row[$m])) ? $row[$m] : '';
                }

                // Define o option
                $option = str_replace($matches[0], $matches[1], $this->htmlSelectOption);

                // Verifica se deve adicionar campos ao data
                $data = '';
                if (isset($this->htmlSelectOptionData)) {
                    $data = '';
                    foreach ($this->htmlSelectOptionData as $name => $field) {
                        if (is_numeric($name)) {
                            $name = $field;
                        }
                        $data .= " data-$name=\"{$row[$field]}\"";
                    }
                }

                // Verifica se deve usar optgroup e cria o label
                if ($grouped !== false) {
                    if ($group !== $row[$grouped]) {
                        if ($group !== false) {
                            $options .= '</optgroup>';
                        }
                        $options .= '<optgroup label="' . $row[$grouped] . '">';
                        $group = $row[$grouped];
                    }
                }

                $options .= "<option value=\"{$row[$key]}\" $data>$option</option>";
            }

            // Fecha o último grupo se ele existir
            if ($grouped !== false && $group !== false) {
                $options .= '</optgroup>';
            }
        }

        // Verifica se tem valor padrão
        if (! is_null($selecionado)) {
            $temp = str_replace("<option value=\"$selecionado\"", "<option value=\"$selecionado\" selected=\"selected\"", $options);
            if ($temp === $options) {
                $selecionado = null;
            }
            $options = $temp;
        }

        // Abre o select
        $select = "<select class=\"form-control\" name=\"$nome\" id=\"$nome\" $selectPlaceholder>";

        // Verifica se tem valor padrão selecionado
        if ((empty($selecionado) || $showEmpty) && ! $neverShowEmpty) {
            $select .= "<option value=\"\">$placeholder</option>";
        }

        // Coloca as opções
        $select .= $options;

        // Fecha o select
        $select .= '</select>';

        // Retorna o select
        return $select;
    }

    /**
     *
     * @param string $htmlSelectOption
     *
     * @return self
     */
    public function setHtmlSelectOption($htmlSelectOption)
    {
        $this->htmlSelectOption = $htmlSelectOption;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getHtmlSelectOption()
    {
        return $this->htmlSelectOption;
    }

    /**
     *
     * @param array|string $htmlSelectOptionData
     *
     * @return self
     */
    public function setHtmlSelectOptionData($htmlSelectOptionData)
    {
        $this->htmlSelectOptionData = $htmlSelectOptionData;
        return $this;
    }

    /**
     *
     * @return array|string
     */
    public function getHtmlSelectOptionData()
    {
        return $this->htmlSelectOptionData;
    }

    public function getUniqueCacheKey()
    {
        return str_replace('\\', '_', get_class($this));
    }

    /**
     * Retorna vários registros
     *
     * @param string|array $where OPTIONAL An SQL WHERE clause
     * @param string|array $order OPTIONAL An SQL ORDER clause.
     * @param int          $count OPTIONAL An SQL LIMIT count.
     * @param int          $offset OPTIONAL An SQL LIMIT offset.
     *
     * @return ArrayObject[] | null
     */
    public function findAll($where = null, $order = null, $count = null, $offset = null)
    {
        // Cria a assinatura da consulta
        $cacheKey = 'findAll'.$this->getUniqueCacheKey().md5(var_export($where, true) . var_export($order, true) . var_export($count, true) . var_export($offset, true) . var_export($this->getMapper()->getShowDeleted(), true));

        // Verifica se tem no cache
        if ($this->getUseCache() && $this->getCache()->hasItem($cacheKey)) {
            return $this->getCache()->getItem($cacheKey);
        }

        $findAll = $this->getMapper()->fetchAll($where, $order, $count, $offset);

        // Grava a consulta no cache
        if ($this->getUseCache()) {
            $this->getCache()->setItem($cacheKey, $findAll);
        }

        return $findAll;
    }

    /**
     * Retorna um registro
     *
     * @param string|array $where OPTIONAL An SQL WHERE clause
     * @param string|array $order OPTIONAL An SQL ORDER clause.
     * @return null|ArrayObject
     */
    public function findOne($where = null, $order = null)
    {
        // Cria a assinatura da consulta
        $cacheKey = 'findOne'.md5(var_export($where, true) . var_export($order, true) . var_export($this->getMapper()->getShowDeleted(), true));

        // Verifica se tem no cache
        if ($this->getUseCache() && $this->getCache()->hasItem($cacheKey)) {
            return $this->getCache()->getItem($cacheKey);
        }

        $findOne = $this->getMapper()->fetchRow($this->getWhere($where), $order, 1);

        // Grava a consulta no cache
        if ($this->getUseCache()) {
            $this->getCache()->setItem($findOne, $cacheKey);
        }

        return $findOne;
    }

    /**
     * Retorna vários registros associados pela chave
     *
     * @param string|array oUsuario OPTIONAL An SQL WHERE clause
     * @param string|array oUsuario OPTIONAL An SQL ORDER clause.
     * @param int          oUsuario OPTIONAL An SQL LIMIT count.
     * @param int          oUsuario OPTIONAL An SQL LIMIT offset.
     *
     * @return ArrayObject[] | null
     */
    public function findAssoc($where = null, $order = null, $count = null, $offset = null)
    {
        // Cria a assinatura da consulta
        $cacheKey = 'findAssoc'.md5(var_export($where, true) . var_export($order, true) . var_export($count, true) . var_export($offset, true) . var_export($this->getMapper()->getShowDeleted(), true));

        // Verifica se tem no cache
        if ($this->getUseCache() && $this->getCache()->hasItem($cacheKey)) {
            return $this->getCache()->getItem($cacheKey);
        }

        $fetchAll = $this->getMapper()->fetchAll($this->getWhere($where), $order, $count, $offset);
        $findAssoc = [];
        if (! empty($fetchAll)) {
            foreach ($fetchAll as $row) {
                $findAssoc[$row[$this->getMapper()->getTableKey(true)]] = $row;
            }
        }

        // Grava a consulta no cache
        if ($this->getUseCache()) {
            $this->getCache()->setItem($findAssoc, $cacheKey);
        }

        return $findAssoc;
    }

    /**
     * Retorna a consulta paginada
     *
     * @param string|array $where  OPTIONAL An SQL WHERE clause
     * @param string|array $order  OPTIONAL An SQL ORDER clause.
     * @param int          $count  OPTIONAL An SQL LIMIT count.
     * @param int          $offset OPTIONAL An SQL LIMIT offset.
     *
     * @return \Zend\Paginator\Paginator
     */
    public function findPaginated($where = null, $order = null, $count = null, $offset = null)
    {
        // Define a consulta
        if ($where instanceof \Zend\Db\Sql\Select) {
            $select = $where;
        } else {
            $select = $this->getMapper()->getSelect($this->getWhere($where), $order, $count, $offset);
        }


        // Verifica se deve usar o cache
        $cacheKey = 'findPaginated'.md5($select->getSqlString($this->getMapper()->getTableGateway()->getAdapter()->getPlatform()));

        // Verifica se tem no cache
        if ($this->getUseCache() && $this->getCache()->hasItem($cacheKey)) {
            return $this->getCache()->getItem($cacheKey);
        }

        $paginator = new HydratorPagination($select, $this->getMapper()->getTableGateway()->getAdapter());
        $paginator->setHydrator($this->getMapper()->getHydrator())
                  ->setHydratorEntity($this->getMapper()->getHydratorEntity());
        $findPaginated = new \Zend\Paginator\Paginator($paginator);

        // Verifica se deve usar o cache
        if ($this->getUseCache()) {
            $findPaginated->setCacheEnabled(true)->setCache($this->getCache());
        }

        // Configura o paginator
        $findPaginated->setPageRange($this->getPaginatorOptions()->getPageRange());
        $findPaginated->setCurrentPageNumber($this->getPaginatorOptions()->getCurrentPageNumber());
        $findPaginated->setItemCountPerPage($this->getPaginatorOptions()->getItemCountPerPage());

        return $findPaginated;
    }

    /**
     * Consultas especiais do service
     *
     * @param array $where
     * @return array
     */
    public function getWhere($where)
    {
        return $where;
    }

    /**
     * Inclui um novo registro
     *
     * @param  array $set Dados do regsitro
     *
     * @return int|array Chave do registro criado
     */
    public function create($set)
    {
        return $this->getMapper()->insert($set);
    }

    /**
     * Altera um registro
     *
     * @param  array     $set Dados do registro
     * @param  int|array $key Chave do regsitro a ser alterado
     *
     * @return int Quantidade de registro alterados
     */
    public function update($set, $key)
    {
        return $this->getMapper()->update($set, $key);
    }

    /**
     * @param MapperAbstract|string $mapper
     * @return $this
     * @throws \Exception
     */
    public function setMapper($mapper)
    {
        if (is_string($mapper)) {
            $this->mapperClass = $mapper;
            $this->mapper      = null;
        } elseif ($mapper instanceof MapperAbstract) {
            $this->mapper      = $mapper;
            $this->mapperClass = get_class($mapper);
        } else {
            throw new \Exception('Mapper invalido em ' . get_class($this) . '::setMapper()');
        }

        return $this;
    }

    /**
     * @return MapperAbstract
     * @throws \Exception
     */
    public function getMapper()
    {
        if (! isset($this->mapper)) {
            if (! isset($this->mapperClass)) {
                throw new \Exception('Mapper class not defined at ' . get_class($this));
            }
            $this->mapper = new $this->mapperClass();
            $this->mapper->setCache($this->getCache());
        }

        return $this->mapper;
    }

    /**
     * @return boolean
     */
    public function getUseJoin()
    {
        return $this->getMapper()->getUseJoin();
    }

    /**
     * @param boolean $useJoin
     * @return self
     */
    public function setUseJoin($useJoin)
    {
        $this->getMapper()->setUseJoin($useJoin);
        return $this;
    }

    /**
     * @return PaginatorOptions
     */
    public function getPaginatorOptions()
    {
        if (! isset($this->paginatorOptions)) {
            $this->paginatorOptions = new PaginatorOptions();
        }

        return $this->paginatorOptions;
    }

    /**
     * CONTROLE DE CACHE
     */

    /**
     * Configura o cache
     *
     * @return \Zend\Cache\Storage\Adapter
     */
    public function getCache()
    {
        if (! isset($this->cache)) {
            $this->cache = \Realejo\Utils\Cache::getFrontend(str_replace('\\', DIRECTORY_SEPARATOR, get_class($this)));
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
     * @param boolean $useCache
     */
    public function setUseCache($useCache)
    {
        $this->useCache = $useCache;
        $this->getMapper()->setUseCache($useCache);

        // Mantem a cadeia
        return $this;
    }

    /**
     * Retorna se deve usar o cache
     * @return boolean
     */
    public function getUseCache()
    {
        return $this->useCache;
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
        return $this->getMapper()->autoCleanCache();
    }

    /**
     * @param boolean $autoCleanCache
     *
     * @return self
     */
    public function setAutoCleanCache($autoCleanCache)
    {
        $this->getMapper()->setAutoCleanCache($autoCleanCache);

        return $this;
    }
}
