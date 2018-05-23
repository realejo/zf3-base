<?php

namespace RealejoTest\Service\Mapper;

use Realejo\Cache\CacheService;
use Realejo\Service\MapperAbstract;
use Realejo\Stdlib\ArrayObject;
use RealejoTest\BaseTestCase;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql;

class MapperAbstractTest extends BaseTestCase
{
    /**
     * @var string
     */
    protected $tableName = 'album';

    /**
     * @var string
     */
    protected $tableKeyName = 'id';

    protected $tables = ['album'];

    /**
     * @var MapperConcrete
     */
    private $mapper;

    protected $defaultValues = [
        [
            'id' => 1,
            'artist' => 'Rush',
            'title' => 'Rush',
            'deleted' => 0
        ],
        [
            'id' => 2,
            'artist' => 'Rush',
            'title' => 'Moving Pictures',
            'deleted' => 0
        ],
        [
            'id' => 3,
            'artist' => 'Dream Theater',
            'title' => 'Images And Words',
            'deleted' => 0
        ],
        [
            'id' => 4,
            'artist' => 'Claudia Leitte',
            'title' => 'Exttravasa',
            'deleted' => 1
        ]
    ];

    /**
     * @return self
     */
    public function insertDefaultRows()
    {
        foreach ($this->defaultValues as $row) {
            $this->getAdapter()->query("INSERT into {$this->tableName}({$this->tableKeyName}, artist, title, deleted)
                                        VALUES (
                                            {$row[$this->tableKeyName]},
                                            '{$row['artist']}',
                                            '{$row['title']}',
                                            {$row['deleted']}
                                        );", Adapter::QUERY_MODE_EXECUTE);
        }

        return $this;
    }

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->dropTables()->createTables()->insertDefaultRows();

        // Remove as pastas criadas
        $this->clearApplicationData();

        // Configura o mapper
        $this->mapper = new MapperConcrete($this->tableName, $this->tableKeyName);
        $cacheService = new CacheService();
        $cacheService->setCacheDir($this->getDataDir() . '/cache');
        $this->mapper->setCache($cacheService->getFrontend());

    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->dropTables();

        $this->clearApplicationData();
    }

    /**
     * Definição de chave invalido
     * @expectedException \InvalidArgumentException
     */
    public function testKeyNameInvalido()
    {
        $this->mapper->setTableKey(null);
    }

    /**
     * Definição de ordem invalido
     * @expectedException \InvalidArgumentException
     */
    public function testOrderInvalida()
    {
        $this->mapper->setOrder(null);
    }

    /**
     * Definição de ordem invalido
     * @expectedException \InvalidArgumentException
     */
    public function testfetchRowMultiKeyException()
    {
        // Cria a tabela com chave string
        $this->mapper->setTableKey([MapperConcrete::KEY_INTEGER => 'id_int', MapperConcrete::KEY_STRING => 'id_char']);
        $this->mapper->fetchRow(1);
    }

    /**
     * Definição de chave invalido
     */
    public function testGettersStters()
    {
        $this->assertEquals('meuid', $this->mapper->setTableKey('meuid')->getTableKey());
        $this->assertEquals('meuid', $this->mapper->setTableKey('meuid')->getTableKey(true));
        $this->assertEquals('meuid', $this->mapper->setTableKey('meuid')->getTableKey(false));

        $this->assertEquals(['meuid', 'com array'], $this->mapper->setTableKey(['meuid', 'com array'])->getTableKey());
        $this->assertEquals(['meuid', 'com array'],
            $this->mapper->setTableKey(['meuid', 'com array'])->getTableKey(false));
        $this->assertEquals('meuid', $this->mapper->setTableKey(['meuid', 'com array'])->getTableKey(true));

        $this->assertInstanceOf('\Zend\Db\Sql\Expression',
            $this->mapper->setTableKey(new Sql\Expression('chave muito exotica!'))->getTableKey());
        $this->assertInstanceOf('\Zend\Db\Sql\Expression', $this->mapper->setTableKey([
            new Sql\Expression('chave muito mais exotica!'),
            'não existo'
        ])->getTableKey(true));

        $this->assertEquals('minhaordem', $this->mapper->setOrder('minhaordem')->getOrder());
        $this->assertEquals(['minhaordem', 'comarray'],
            $this->mapper->setOrder(['minhaordem', 'comarray'])->getOrder());
        $this->assertInstanceOf('\Zend\Db\Sql\Expression',
            $this->mapper->setOrder(new Sql\Expression('ordem muito exotica!'))->getOrder());
    }

    /**
     * Test de criação com a conexão local de testes
     */
    public function testCreateBase()
    {
        $Base = new MapperConcrete($this->tableName, $this->tableKeyName);
        $this->assertInstanceOf(MapperAbstract::class, $Base);
        $this->assertEquals($this->tableKeyName, $Base->getTableKey());
        $this->assertEquals($this->tableName, $Base->getTableName());

        $Base = new MapperConcrete($this->tableName, [$this->tableKeyName, $this->tableKeyName]);
        $this->assertInstanceOf(MapperAbstract::class, $Base);
        $this->assertEquals([$this->tableKeyName, $this->tableKeyName], $Base->getTableKey());
        $this->assertEquals($this->tableName, $Base->getTableName());

        $Base = new MapperConcrete($this->tableName, $this->tableKeyName);
        $this->assertInstanceOf(MapperAbstract::class, $Base);
        $this->assertInstanceOf(get_class($this->getAdapter()), $Base->getTableGateway()->getAdapter(),
            'tem o Adapter padrão');
        $this->assertEquals($this->getAdapter(), $Base->getTableGateway()->getAdapter(),
            'tem a mesma configuração do adapter padrão');
    }

    /**
     * Tests Base->getOrder()
     */
    public function testOrder()
    {
        // Verifica a ordem padrão
        $this->assertNull($this->mapper->getOrder());

        // Define uma nova ordem com string
        $this->mapper->setOrder('id');
        $this->assertEquals('id', $this->mapper->getOrder());

        // Define uma nova ordem com string
        $this->mapper->setOrder('title');
        $this->assertEquals('title', $this->mapper->getOrder());

        // Define uma nova ordem com array
        $this->mapper->setOrder(['id', 'title']);
        $this->assertEquals(['id', 'title'], $this->mapper->getOrder());
    }

    /**
     * Tests Base->getWhere()
     *
     * Apenas para ter o coverage completo
     */
    public function testWhere()
    {
        $this->assertEquals('123456789abcde', $this->mapper->getWhere('123456789abcde'));
    }

    /**
     * Tests campo deleted
     */
    public function testDeletedField()
    {
        // Verifica se deve remover o registro
        $this->mapper->setUseDeleted(false);
        $this->assertFalse($this->mapper->getUseDeleted());
        $this->assertTrue($this->mapper->setUseDeleted(true)->getUseDeleted());
        $this->assertFalse($this->mapper->setUseDeleted(false)->getUseDeleted());
        $this->assertFalse($this->mapper->getUseDeleted());

        // Verifica se deve mostrar o registro
        $this->mapper->setShowDeleted(false);
        $this->assertFalse($this->mapper->getShowDeleted());
        $this->assertFalse($this->mapper->setShowDeleted(false)->getShowDeleted());
        $this->assertTrue($this->mapper->setShowDeleted(true)->getShowDeleted());
        $this->assertTrue($this->mapper->getShowDeleted());
    }

    /**
     * Tests Base->getSQlString()
     */
    public function testGetSQlString()
    {
        // Verifica o padrão de não usar o campo deleted e não mostrar os removidos
        $this->mapper->setOrder('id');
        $this->assertEquals(
            'SELECT `album`.* FROM `album` ORDER BY `id` ASC',
            $this->mapper->getSelect()->getSqlString($this->adapter->getPlatform()),
            'showDeleted=false, useDeleted=false'
        );

        // Marca para usar o campo deleted
        $this->mapper->setUseDeleted(true);
        $this->assertEquals(
            'SELECT `album`.* FROM `album` WHERE `album`.`deleted` = \'0\' ORDER BY `id` ASC',
            $this->mapper->getSelect()->getSqlString($this->adapter->getPlatform()),
            'showDeleted=false, useDeleted=true'
        );

        // Marca para não usar o campo deleted
        $this->mapper->setUseDeleted(false);

        $this->assertEquals(
            'SELECT `album`.* FROM `album` WHERE `album`.`id` = \'1234\' ORDER BY `id` ASC',
            $this->mapper->getSelect(['id' => 1234])->getSqlString($this->adapter->getPlatform())
        );
        $this->assertEquals(
            'SELECT `album`.* FROM `album` WHERE `album`.`texto` = \'textotextotexto\' ORDER BY `id` ASC',
            $this->mapper->getSelect(['texto' => 'textotextotexto'])->getSqlString($this->adapter->getPlatform())
        );
    }

    /**
     * Tests Base->testGetSQlSelect()
     */
    public function testGetSQlSelect()
    {
        $select = $this->mapper->getTableSelect();
        $this->assertInstanceOf('\Zend\Db\Sql\Select', $select);
        $this->assertEquals($select->getSqlString(), $this->mapper->getTableSelect()->getSqlString());
    }

    /**
     * Tests Base->fetchAll()
     */
    public function testFetchAll()
    {
        $this->assertFalse($this->mapper->isUseHydrateResultSet());

        // O padrão é não usar o campo deleted
        $this->mapper->setOrder('id');
        $albuns = $this->mapper->fetchAll();
        $this->assertCount(4, $albuns, 'showDeleted=false, useDeleted=false');

        // Marca para mostrar os removidos e não usar o campo deleted
        $this->mapper->setShowDeleted(true)->setUseDeleted(false);
        $this->assertCount(4, $this->mapper->fetchAll(), 'showDeleted=true, useDeleted=false');

        // Marca pra não mostrar os removidos e usar o campo deleted
        $this->mapper->setShowDeleted(false)->setUseDeleted(true);
        $this->assertCount(3, $this->mapper->fetchAll(), 'showDeleted=false, useDeleted=true');

        // Marca pra mostrar os removidos e usar o campo deleted
        $this->mapper->setShowDeleted(true)->setUseDeleted(true);
        $albuns = $this->mapper->fetchAll();
        $this->assertCount(4, $albuns, 'showDeleted=true, useDeleted=true');

        // Marca não mostrar os removios
        $this->mapper->setUseDeleted(true)->setShowDeleted(false);

        $albuns = $this->defaultValues;
        unset($albuns[3]); // remove o deleted=1

        $fetchAll = $this->mapper->fetchAll();
        foreach ($fetchAll as $id => $row) {
            $fetchAll[$id] = $row->toArray();
        }
        $this->assertEquals($albuns, $fetchAll);

        // Marca mostrar os removios
        $this->mapper->setShowDeleted(true);

        $fetchAll = $this->mapper->fetchAll();
        foreach ($fetchAll as $id => $row) {
            $fetchAll[$id] = $row->toArray();
        }
        $this->assertEquals($this->defaultValues, $fetchAll);
        $this->assertCount(4, $this->mapper->fetchAll());
        $this->mapper->setShowDeleted(false);
        $this->assertCount(3, $this->mapper->fetchAll());

        // Verifica o where
        $this->assertCount(2, $this->mapper->fetchAll(['artist' => $albuns[0]['artist']]));
        $this->assertNull($this->mapper->fetchAll(['artist' => $this->defaultValues[3]['artist']]));

        // Apaga qualquer cache
        $this->assertTrue($this->mapper->getCache()->flush(), 'apaga o cache');

        // Define exibir os removidos
        $this->mapper->setShowDeleted(true);

        // Liga o cache
        $this->mapper->setUseCache(true);
        $fetchAll = $this->mapper->fetchAll();
        foreach ($fetchAll as $id => $row) {
            $fetchAll[$id] = $row->toArray();
        }
        $this->assertEquals($this->defaultValues, $fetchAll, 'fetchAll está igual ao defaultValues');
        $this->assertCount(4, $this->mapper->fetchAll(), 'Deve conter 4 registros');

        // Grava um registro "sem o cache saber"
        $this->mapper->getTableGateway()->insert([
            'id' => 10,
            'artist' => 'nao existo por enquanto',
            'title' => 'bla bla',
            'deleted' => 0
        ]);

        $this->assertCount(4, $this->mapper->fetchAll(),
            'Deve conter 4 registros depois do insert "sem o cache saber"');
        $this->assertTrue($this->mapper->getCache()->flush(), 'limpa o cache');
        $this->assertCount(5, $this->mapper->fetchAll(), 'Deve conter 5 registros');

        // Define não exibir os removidos
        $this->mapper->setShowDeleted(false);
        $this->assertCount(4, $this->mapper->fetchAll(), 'Deve conter 4 registros com showDeleted=false');

        // Apaga um registro "sem o cache saber"
        $this->mapper->getTableGateway()->delete("id=10");
        $this->mapper->setShowDeleted(true);
        $this->assertCount(5, $this->mapper->fetchAll(), 'Deve conter 5 registros');
        $this->assertTrue($this->mapper->getCache()->flush(), 'apaga o cache');
        $this->assertCount(4, $this->mapper->fetchAll(), 'Deve conter 4 registros 4');
    }

    /**
     * Tests Base->fetchAll()
     */
    public function testFetchAllHydrateResultSet()
    {
        $this->mapper->setUseHydrateResultSet(true);
        $this->assertTrue($this->mapper->isUseHydrateResultSet());

        // O padrão é não usar o campo deleted
        $this->mapper->setOrder('id');
        $albuns = $this->mapper->fetchAll();
        $this->assertCount(4, $albuns, 'showDeleted=false, useDeleted=false');

        // Marca para mostrar os removidos e não usar o campo deleted
        $this->mapper->setShowDeleted(true)->setUseDeleted(false);
        $this->assertCount(4, $this->mapper->fetchAll(), 'showDeleted=true, useDeleted=false');

        // Marca pra não mostrar os removidos e usar o campo deleted
        $this->mapper->setShowDeleted(false)->setUseDeleted(true);
        $this->assertCount(3, $this->mapper->fetchAll(), 'showDeleted=false, useDeleted=true');

        // Marca pra mostrar os removidos e usar o campo deleted
        $this->mapper->setShowDeleted(true)->setUseDeleted(true);
        $albuns = $this->mapper->fetchAll();
        $this->assertCount(4, $albuns, 'showDeleted=true, useDeleted=true');

        // Marca não mostrar os removios
        $this->mapper->setUseDeleted(true)->setShowDeleted(false);

        $albuns = $this->defaultValues;
        unset($albuns[3]); // remove o deleted=1

        $fetchAll = $this->mapper->fetchAll();
        $this->assertInstanceOf(HydratingResultSet::class, $fetchAll);
        $fetchAll = $fetchAll->toArray();
        $this->assertEquals($albuns, $fetchAll);

        // Marca mostrar os removidos
        $this->mapper->setShowDeleted(true);

        $fetchAll = $this->mapper->fetchAll();
        $this->assertInstanceOf(HydratingResultSet::class, $fetchAll);
        $fetchAll = $fetchAll->toArray();

        $this->assertEquals($this->defaultValues, $fetchAll);
        $this->assertCount(4, $this->mapper->fetchAll());
        $this->mapper->setShowDeleted(false);
        $this->assertCount(3, $this->mapper->fetchAll());

        // Verifica o where
        $this->assertCount(2, $this->mapper->fetchAll(['artist' => $albuns[0]['artist']]));
        $this->assertNull($this->mapper->fetchAll(['artist' => $this->defaultValues[3]['artist']]));

        // Apaga qualquer cache
        $this->assertTrue($this->mapper->getCache()->flush(), 'apaga o cache');

        // Define exibir os removidos
        $this->mapper->setShowDeleted(true);

        // Liga o cache
        $this->mapper->setUseCache(true);
        $fetchAll = $this->mapper->fetchAll();
        $this->assertInstanceOf(HydratingResultSet::class, $fetchAll);
        $fetchAll = $fetchAll->toArray();

        $this->assertEquals($this->defaultValues, $fetchAll, 'fetchAll está igual ao defaultValues');
        $this->assertCount(4, $this->mapper->fetchAll(), 'Deve conter 4 registros');

        // Grava um registro "sem o cache saber"
        $this->mapper->getTableGateway()->insert([
            'id' => 10,
            'artist' => 'nao existo por enquanto',
            'title' => 'bla bla',
            'deleted' => 0
        ]);

        $this->assertCount(4, $this->mapper->fetchAll(),
            'Deve conter 4 registros depois do insert "sem o cache saber"');
        $this->assertTrue($this->mapper->getCache()->flush(), 'limpa o cache');
        $this->assertCount(5, $this->mapper->fetchAll(), 'Deve conter 5 registros');

        // Define não exibir os removidos
        $this->mapper->setShowDeleted(false);
        $this->assertCount(4, $this->mapper->fetchAll(), 'Deve conter 4 registros com showDeleted=false');

        // Apaga um registro "sem o cache saber"
        $this->mapper->getTableGateway()->delete("id=10");
        $this->mapper->setShowDeleted(true);
        $this->assertCount(5, $this->mapper->fetchAll(), 'Deve conter 5 registros');
        $this->assertTrue($this->mapper->getCache()->flush(), 'apaga o cache');
        $this->assertCount(4, $this->mapper->fetchAll(), 'Deve conter 4 registros 4');
    }

    /**
     * Tests Base->fetchRow()
     */
    public function testFetchRow()
    {
        $this->assertFalse($this->mapper->isUseHydrateResultSet());

        // Marca pra usar o campo deleted
        $this->mapper->setUseDeleted(true);
        $this->mapper->setOrder('id');

        // Verifica os itens que existem
        $this->assertInstanceOf(ArrayObject::class, $this->mapper->fetchRow(1));
        $this->assertEquals($this->defaultValues[0], $this->mapper->fetchRow(1)->toArray());
        $this->assertInstanceOf(ArrayObject::class, $this->mapper->fetchRow(2));
        $this->assertEquals($this->defaultValues[1], $this->mapper->fetchRow(2)->toArray());
        $this->assertInstanceOf(ArrayObject::class, $this->mapper->fetchRow(3));
        $this->assertEquals($this->defaultValues[2], $this->mapper->fetchRow(3)->toArray());

        // Verifica o item removido
        $this->mapper->setShowDeleted(true);
        $this->assertInstanceOf(ArrayObject::class, $this->mapper->fetchRow(4));
        $this->assertEquals($this->defaultValues[3], $this->mapper->fetchRow(4)->toArray());
        $this->mapper->setShowDeleted(false);
    }

    /**
     * Tests Base->fetchRow()
     */
    public function testFetchRowHydrateResultaSet()
    {
        $this->mapper->setUseHydrateResultSet(true);
        $this->assertTrue($this->mapper->isUseHydrateResultSet());

        // Marca pra usar o campo deleted
        $this->mapper->setUseDeleted(true);
        $this->mapper->setOrder('id');

        // Verifica os itens que existem
        $this->assertInstanceOf(ArrayObject::class, $this->mapper->fetchRow(1));
        $this->assertEquals($this->defaultValues[0], $this->mapper->fetchRow(1)->toArray());
        $this->assertInstanceOf(ArrayObject::class, $this->mapper->fetchRow(2));
        $this->assertEquals($this->defaultValues[1], $this->mapper->fetchRow(2)->toArray());
        $this->assertInstanceOf(ArrayObject::class, $this->mapper->fetchRow(3));
        $this->assertEquals($this->defaultValues[2], $this->mapper->fetchRow(3)->toArray());

        // Verifica o item removido
        $this->mapper->setShowDeleted(true);
        $this->assertInstanceOf(ArrayObject::class, $this->mapper->fetchRow(4));
        $this->assertEquals($this->defaultValues[3], $this->mapper->fetchRow(4)->toArray());
        $this->mapper->setShowDeleted(false);
    }

    /**
     * Tests Base->fetchRow()
     */
    public function testFetchRowWithIntegerKey()
    {
        $this->mapper->setTableKey([MapperConcrete::KEY_INTEGER => 'id']);

        // Marca pra usar o campo deleted
        $this->mapper->setUseDeleted(true);
        $this->mapper->setOrder('id');

        // Verifica os itens que existem
        $this->assertInstanceOf(ArrayObject::class, $this->mapper->fetchRow(1));
        $this->assertEquals($this->defaultValues[0], $this->mapper->fetchRow(1)->toArray());
        $this->assertInstanceOf(ArrayObject::class, $this->mapper->fetchRow(2));
        $this->assertEquals($this->defaultValues[1], $this->mapper->fetchRow(2)->toArray());
        $this->assertInstanceOf(ArrayObject::class, $this->mapper->fetchRow(3));
        $this->assertEquals($this->defaultValues[2], $this->mapper->fetchRow(3)->toArray());

        // Verifica o item removido
        $this->mapper->setShowDeleted(true);
        $this->assertInstanceOf(ArrayObject::class, $this->mapper->fetchRow(4));
        $this->assertEquals($this->defaultValues[3], $this->mapper->fetchRow(4)->toArray());
        $this->mapper->setShowDeleted(false);
    }

    /**
     * Tests Base->fetchRow()
     */
    public function testFetchRowWithStringKey()
    {
        $this->dropTables()->createTables(['album_string']);
        $defaultValues = [
            [
                'id' => 'A',
                'artist' => 'Rush',
                'title' => 'Rush',
                'deleted' => 0
            ],
            [
                'id' => 'B',
                'artist' => 'Rush',
                'title' => 'Moving Pictures',
                'deleted' => 0
            ],
            [
                'id' => 'C',
                'artist' => 'Dream Theater',
                'title' => 'Images And Words',
                'deleted' => 0
            ],
            [
                'id' => 'D',
                'artist' => 'Claudia Leitte',
                'title' => 'Exttravasa',
                'deleted' => 1
            ]
        ];
        foreach ($defaultValues as $row) {
            $this->getAdapter()->query("INSERT into {$this->tableName}({$this->tableKeyName}, artist, title, deleted)
                                        VALUES (
                                        '{$row['id']}',
                                        '{$row['artist']}',
                                        '{$row['title']}',
                                        {$row['deleted']}
                                        );", Adapter::QUERY_MODE_EXECUTE);
        }

        $this->mapper->setTableKey([MapperConcrete::KEY_STRING => 'id']);

        // Marca pra usar o campo deleted
        $this->mapper->setUseDeleted(true);

        $this->mapper->setOrder('id');

        // Verifica os itens que existem
        $this->assertInstanceOf(ArrayObject::class, $this->mapper->fetchRow('A'));
        $this->assertEquals($defaultValues[0], $this->mapper->fetchRow('A')->toArray());
        $this->assertInstanceOf(ArrayObject::class, $this->mapper->fetchRow('B'));
        $this->assertEquals($defaultValues[1], $this->mapper->fetchRow('B')->toArray());
        $this->assertInstanceOf(ArrayObject::class, $this->mapper->fetchRow('C'));
        $this->assertEquals($defaultValues[2], $this->mapper->fetchRow('C')->toArray());

        // Verifica o item removido
        $this->mapper->setShowDeleted(true);
        $this->assertInstanceOf(ArrayObject::class, $this->mapper->fetchRow('D'));
        $this->assertEquals($defaultValues[3], $this->mapper->fetchRow('D')->toArray());
        $this->mapper->setShowDeleted(false);
    }

    /**
     * Tests Base->fetchRow()
     */
    public function testFetchRowWithMultipleKey()
    {
        $this->dropTables()->createTables(['album_array']);
        $defaultValues = [
            [
                'id_int' => 1,
                'id_char' => 'A',
                'artist' => 'Rush',
                'title' => 'Rush',
                'deleted' => 0
            ],
            [
                'id_int' => 2,
                'id_char' => 'B',
                'artist' => 'Rush',
                'title' => 'Moving Pictures',
                'deleted' => 0
            ],
            [
                'id_int' => 3,
                'id_char' => 'C',
                'artist' => 'Dream Theater',
                'title' => 'Images And Words',
                'deleted' => 0
            ],
            [
                'id_int' => 4,
                'id_char' => 'D',
                'artist' => 'Claudia Leitte',
                'title' => 'Exttravasa',
                'deleted' => 1
            ]
        ];
        foreach ($defaultValues as $row) {
            $this->getAdapter()->query("INSERT into album (id_int, id_char, artist, title, deleted)
                                        VALUES (
                                        '{$row['id_int']}',
                                        '{$row['id_char']}',
                                        '{$row['artist']}',
                                        '{$row['title']}',
                                        {$row['deleted']}
                                        );", Adapter::QUERY_MODE_EXECUTE);
        }

        $this->mapper->setTableKey([MapperConcrete::KEY_STRING => 'id']);

        // Marca pra usar o campo deleted
        $this->mapper->setUseDeleted(true);

        $this->mapper->setOrder(['id_int', 'id_char']);

        // Verifica os itens que existem
        $this->assertInstanceOf(ArrayObject::class, $this->mapper->fetchRow(['id_char' => 'A', 'id_int' => 1]));
        $this->assertEquals($defaultValues[0], $this->mapper->fetchRow(['id_char' => 'A', 'id_int' => 1])->toArray());
        $this->assertInstanceOf(ArrayObject::class, $this->mapper->fetchRow(['id_char' => 'B', 'id_int' => 2]));
        $this->assertEquals($defaultValues[1], $this->mapper->fetchRow(['id_char' => 'B', 'id_int' => 2])->toArray());
        $this->assertInstanceOf(ArrayObject::class, $this->mapper->fetchRow(['id_char' => 'C', 'id_int' => 3]));
        $this->assertEquals($defaultValues[2], $this->mapper->fetchRow(['id_char' => 'C', 'id_int' => 3])->toArray());

        $this->assertNull($this->mapper->fetchRow(['id_char' => 'C', 'id_int' => 2]));

        // Verifica o item removido
        $this->mapper->setShowDeleted(true);
        $this->assertInstanceOf(ArrayObject::class, $this->mapper->fetchRow(['id_char' => 'D', 'id_int' => 4]));
        $this->assertEquals($defaultValues[3], $this->mapper->fetchRow(['id_char' => 'D', 'id_int' => 4])->toArray());
        $this->mapper->setShowDeleted(false);
    }

    /**
     * Tests Db->insert()
     */
    public function testInsert()
    {
        // Certifica que a tabela está vazia
        $this->dropTables()->createTables();
        $this->mapper->setOrder('id');
        $this->assertNull($this->mapper->fetchAll(), 'Verifica se há algum registro pregravado');

        $this->assertFalse($this->mapper->insert([]), 'Verifica inclusão inválida 1');
        $this->assertFalse($this->mapper->insert(null), 'Verifica inclusão inválida 2');

        $row = [
            'artist' => 'Rush',
            'title' => 'Rush',
            'deleted' => '0'
        ];

        $id = $this->mapper->insert($row);
        $this->assertEquals(1, $id, 'Verifica a chave criada=1');

        $this->assertNotNull($this->mapper->fetchAll(), 'Verifica o fetchAll não vazio');
        $this->assertEquals($row, $this->mapper->getLastInsertSet(), 'Verifica o set do ultimo insert');
        $this->assertCount(1, $this->mapper->fetchAll(), 'Verifica se apenas um registro foi adicionado');

        $row = array_merge(['id' => $id], $row);

        $this->assertEquals([new ArrayObject($row)], $this->mapper->fetchAll(),
            'Verifica se o registro adicionado corresponde ao original pelo fetchAll()');
        $this->assertEquals(new ArrayObject($row), $this->mapper->fetchRow(1),
            'Verifica se o registro adicionado corresponde ao original pelo fetchRow()');

        $row = [
            'id' => 2,
            'artist' => 'Rush',
            'title' => 'Test For Echos',
            'deleted' => '0'
        ];

        $id = $this->mapper->insert($row);
        $this->assertEquals(2, $id, 'Verifica a chave criada=2');

        $this->assertCount(2, $this->mapper->fetchAll(), 'Verifica que há DOIS registro');
        $this->assertEquals(new ArrayObject($row), $this->mapper->fetchRow(2),
            'Verifica se o SEGUNDO registro adicionado corresponde ao original pelo fetchRow()');
        $this->assertEquals($row, $this->mapper->getLastInsertSet());

        $row = [
            'artist' => 'Rush',
            'title' => 'Moving Pictures',
            'deleted' => '0'
        ];
        $id = $this->mapper->insert($row);
        $this->assertEquals(3, $id);
        $this->assertEquals($row, $this->mapper->getLastInsertSet(),
            'Verifica se o TERCEIRO registro adicionado corresponde ao original pelo getLastInsertSet()');

        $row = array_merge(['id' => $id], $row);

        $this->assertCount(3, $this->mapper->fetchAll());
        $this->assertEquals(new ArrayObject($row), $this->mapper->fetchRow(3),
            'Verifica se o TERCEIRO registro adicionado corresponde ao original pelo fetchRow()');

        // Teste com Zend_Db_Expr
        $id = $this->mapper->insert(['title' => new \Zend\Db\Sql\Expression('now()')]);
        $this->assertEquals(4, $id);
    }

    /**
     * Tests Db->update()
     */
    public function testUpdate()
    {
        // Apaga as tabelas
        $this->dropTables()->createTables();
        $this->mapper->setOrder('id');
        $this->assertEmpty($this->mapper->fetchAll(), 'tabela não está vazia');

        $row1 = [
            'id' => 1,
            'artist' => 'Não me altere',
            'title' => 'Presto',
            'deleted' => 0
        ];

        $row2 = [
            'id' => 2,
            'artist' => 'Rush',
            'title' => 'Rush',
            'deleted' => 0
        ];

        $this->mapper->insert($row1);
        $this->mapper->insert($row2);

        $this->assertNotNull($this->mapper->fetchAll());
        $this->assertCount(2, $this->mapper->fetchAll());
        $row = $this->mapper->fetchRow(1);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row1, $row->toArray(), 'row1 existe');
        $row = $this->mapper->fetchRow(2);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row2, $row->toArray(), 'row2 existe');

        $rowUpdate = [
            'artist' => 'Rush',
            'title' => 'Moving Pictures',
        ];

        $this->mapper->update($rowUpdate, 2);
        $rowUpdate['id'] = '2';
        $rowUpdate['deleted'] = '0';

        $this->assertNotNull($this->mapper->fetchAll());
        $this->assertCount(2, $this->mapper->fetchAll());
        $row = $this->mapper->fetchRow(2);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($rowUpdate, $row->toArray(), 'Alterou o 2?');

        $row = $this->mapper->fetchRow(1);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row1, $row->toArray(), 'Alterou o 1?');
        $row = $this->mapper->fetchRow(2);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertNotEquals($row2, $row->toArray(), 'O 2 não é mais o mesmo?');

        $row = $row->toArray();
        unset($row['id']);
        unset($row['deleted']);
        $this->assertEquals($row, $this->mapper->getLastUpdateSet(), 'Os dados diferentes foram os alterados?');
        $this->assertEquals(['title' => [$row2['title'], $row['title']]], $this->mapper->getLastUpdateDiff(),
            'As alterações foram detectadas corretamente?');

        $this->assertFalse($this->mapper->update([], 2));
        $this->assertFalse($this->mapper->update(null, 2));
    }

    /**
     * Tests TableAdapter->delete()
     */
    public function testDelete()
    {
        // Apaga as tabelas
        $this->dropTables()->createTables();
        $this->mapper->setOrder('id');
        $this->assertEmpty($this->mapper->fetchAll(), 'tabela não está vazia');

        $row1 = [
            'id' => 1,
            'artist' => 'Rush',
            'title' => 'Presto',
            'deleted' => 0
        ];
        $row2 = [
            'id' => 2,
            'artist' => 'Rush',
            'title' => 'Moving Pictures',
            'deleted' => 0
        ];

        $this->mapper->insert($row1);
        $this->mapper->insert($row2);

        // Verifica se o registro existe
        $row = $this->mapper->fetchRow(1);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row1, $row->toArray(), 'row1 existe');
        $row = $this->mapper->fetchRow(2);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row2, $row->toArray(), 'row2 existe');

        // Marca para usar o campo deleted
        $this->mapper->setUseDeleted(true)->setShowDeleted(true);

        // Remove o registro
        $this->mapper->delete(1);
        $row1['deleted'] = 1;

        // Verifica se foi removido
        $row = $this->mapper->fetchRow(1);
        $this->assertEquals(1, $row['deleted'], 'row1 marcado como deleted');
        $row = $this->mapper->fetchRow(2);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row2, $row->toArray(), 'row2 ainda existe');

        // Marca para mostrar os removidos
        $this->mapper->setShowDeleted(true);

        // Verifica se o registro existe
        $row = $this->mapper->fetchRow(1);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row1, $row->toArray(), 'row1 ainda existe v1');
        $row = $this->mapper->fetchRow(2);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row2, $row->toArray(), 'row2 ainda existe v1');

        // Marca para remover o registro da tabela
        $this->mapper->setUseDeleted(false);

        // Remove o registro qwue não existe
        $this->mapper->delete(3);

        // Verifica se ele foi removido
        $row = $this->mapper->fetchRow(1);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row1, $row->toArray(), 'row1 ainda existe v2');
        $row = $this->mapper->fetchRow(2);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row2, $row->toArray(), 'row2 ainda existe v2');

        // Remove o registro
        $this->mapper->delete(1);

        // Verifica se ele foi removido
        $this->assertNull($this->mapper->fetchRow(1), 'row1 não existe v3');
        $row = $this->mapper->fetchRow(2);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row2, $row->toArray(), 'row2 ainda existe v3');
    }

    /**
     * Tests TableAdapter->delete()
     */
    public function testDeleteIntegerKey()
    {
        $this->dropTables()->createTables();
        $this->mapper->setOrder('id');
        $this->assertEmpty($this->mapper->fetchAll(), 'tabela não está vazia');

        $this->mapper->setTableKey([MapperConcrete::KEY_INTEGER => 'id']);

        // Abaixo é igual ao testDelete
        $row1 = [
            'id' => 1,
            'artist' => 'Rush',
            'title' => 'Presto',
            'deleted' => 0
        ];
        $row2 = [
            'id' => 2,
            'artist' => 'Rush',
            'title' => 'Moving Pictures',
            'deleted' => 0
        ];

        $this->mapper->insert($row1);
        $this->mapper->insert($row2);

        // Verifica se o registro existe
        $row = $this->mapper->fetchRow(1);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row1, $row->toArray(), 'row1 existe');
        $row = $this->mapper->fetchRow(2);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row2, $row->toArray(), 'row2 existe');

        // Marca para usar o campo deleted
        $this->mapper->setUseDeleted(true)->setShowDeleted(true);

        // Remove o registro
        $this->mapper->delete(1);
        $row1['deleted'] = 1;

        // Verifica se foi removido
        $row = $this->mapper->fetchRow(1);
        $this->assertEquals(1, $row['deleted'], 'row1 marcado como deleted');
        $row = $this->mapper->fetchRow(2);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row2, $row->toArray(), 'row2 ainda existe');

        // Marca para mostrar os removidos
        $this->mapper->setShowDeleted(true);

        // Verifica se o registro existe
        $row = $this->mapper->fetchRow(1);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row1, $row->toArray(), 'row1 ainda existe v1');
        $row = $this->mapper->fetchRow(2);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row2, $row->toArray(), 'row2 ainda existe v1');

        // Marca para remover o registro da tabela
        $this->mapper->setUseDeleted(false);

        // Remove o registro qwue não existe
        $this->mapper->delete(3);

        // Verifica se ele foi removido
        $row = $this->mapper->fetchRow(1);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row1, $row->toArray(), 'row1 ainda existe v2');
        $row = $this->mapper->fetchRow(2);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row2, $row->toArray(), 'row2 ainda existe v2');

        // Remove o registro
        $this->mapper->delete(1);

        // Verifica se ele foi removido
        $this->assertNull($this->mapper->fetchRow(1), 'row1 não existe v3');
        $row = $this->mapper->fetchRow(2);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row2, $row->toArray(), 'row2 ainda existe v3');
    }

    /**
     * Tests TableAdapter->delete()
     */
    public function testDeleteStringKey()
    {

        // Cria a tabela com chave string
        $this->mapper->setTableKey([MapperAbstract::KEY_STRING => 'id']);
        $this->dropTables()->createTables(['album_string']);
        $this->mapper->setOrder('id');

        // Abaixo é igual ao testDelete trocando 1, 2 por A, B
        $row1 = [
            'id' => 'A',
            'artist' => 'Rush',
            'title' => 'Presto',
            'deleted' => 0
        ];
        $row2 = [
            'id' => 'B',
            'artist' => 'Rush',
            'title' => 'Moving Pictures',
            'deleted' => 0
        ];

        $this->mapper->insert($row1);
        $this->mapper->insert($row2);

        // Verifica se o registro existe
        $row = $this->mapper->fetchRow('A');
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row1, $row->toArray(), 'row1 existe');
        $row = $this->mapper->fetchRow('B');
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row2, $row->toArray(), 'row2 existe');

        // Marca para usar o campo deleted
        $this->mapper->setUseDeleted(true)->setShowDeleted(true);

        // Remove o registro
        $this->mapper->delete('A');
        $row1['deleted'] = 1;

        // Verifica se foi removido
        $row = $this->mapper->fetchRow('A');
        $this->assertEquals(1, $row['deleted'], 'row1 marcado como deleted');
        $row = $this->mapper->fetchRow('B');
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row2, $row->toArray(), 'row2 ainda existe');

        // Marca para mostrar os removidos
        $this->mapper->setShowDeleted(true);

        // Verifica se o registro existe
        $row = $this->mapper->fetchRow('A');
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row1, $row->toArray(), 'row1 ainda existe v1');
        $row = $this->mapper->fetchRow('B');
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row2, $row->toArray(), 'row2 ainda existe v1');

        // Marca para remover o registro da tabela
        $this->mapper->setUseDeleted(false);

        // Remove o registro qwue não existe
        $this->mapper->delete('C');

        // Verifica se ele foi removido
        $row = $this->mapper->fetchRow('A');
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row1, $row->toArray(), 'row1 ainda existe v2');
        $row = $this->mapper->fetchRow('B');
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row2, $row->toArray(), 'row2 ainda existe v2');

        // Remove o registro
        $this->mapper->delete('A');

        // Verifica se ele foi removido
        $this->assertNull($this->mapper->fetchRow('A'), 'row1 não existe v3');
        $row = $this->mapper->fetchRow('B');
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row2, $row->toArray(), 'row2 ainda existe v3');
    }

    /**
     * Acesso de chave multiplica com acesso simples
     *
     * @expectedException \InvalidArgumentException
     */
    public function testDeleteInvalidArrayKey()
    {
        $this->mapper->setTableKey([MapperAbstract::KEY_INTEGER => 'id_int', MapperAbstract::KEY_STRING => 'id_char']);
        $this->mapper->delete('A');
    }

    /**
     * Acesso de chave multiplica com acesso simples
     *
     * @expectedException \InvalidArgumentException
     */
    public function testDeleteInvalidArrayMultiKey()
    {
        $this->mapper->setTableKey([
            MapperAbstract::KEY_INTEGER => 'id_int',
            MapperAbstract::KEY_STRING => ['id_char', 'id_char2']
        ]);
        $this->mapper->delete('A');
    }

    /**
     * Acesso de chave multiplica com acesso simples
     *
     * @expectedException \LogicException
     */
    public function testDeleteInvalidArraySingleKey()
    {
        $this->mapper->setTableKey([MapperAbstract::KEY_INTEGER => 'id_int', MapperAbstract::KEY_STRING => 'id_char']);
        $this->mapper->delete(['id_int' => 'A']);
    }


    /**
     * Tests TableAdapter->delete()
     */
    public function testDeleteArrayKey()
    {

        // Cria a tabela com chave string
        $this->mapper->setTableKey([MapperConcrete::KEY_INTEGER => 'id_int', MapperConcrete::KEY_STRING => 'id_char']);
        $this->dropTables()->createTables(['album_array']);
        $this->mapper->setUseAllKeys(false);
        $this->mapper->setOrder(['id_int', 'id_char']);

        // Abaixo é igual ao testDelete trocando 1, 2 por A, B
        $row1 = [
            'id_int' => 1,
            'id_char' => 'A',
            'artist' => 'Rush',
            'title' => 'Presto',
            'deleted' => 0
        ];
        $row2 = [
            'id_int' => 2,
            'id_char' => 'B',
            'artist' => 'Rush',
            'title' => 'Moving Pictures',
            'deleted' => 0
        ];

        $this->mapper->insert($row1);
        $this->mapper->insert($row2);

        // Verifica se o registro existe
        $row = $this->mapper->fetchRow(['id_char' => 'A', 'id_int' => 1]);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row1, $row->toArray(), 'row1 existe');
        $row = $this->mapper->fetchRow(['id_char' => 'B', 'id_int' => 2]);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row2, $row->toArray(), 'row2 existe');

        // Marca para usar o campo deleted
        $this->mapper->setUseDeleted(true)->setShowDeleted(true);

        // Remove o registro
        $this->mapper->delete(['id_char' => 'A']);
        $row1['deleted'] = 1;

        // Verifica se foi removido
        $row = $this->mapper->fetchRow(['id_char' => 'A', 'id_int' => 1]);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals(1, $row['deleted'], 'row1 marcado como deleted');

        $row = $this->mapper->fetchRow(['id_char' => 'B', 'id_int' => 2]);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row2, $row->toArray(), 'row2 ainda existe v1');

        // Marca para mostrar os removidos
        $this->mapper->setShowDeleted(true);

        // Verifica se o registro existe
        $row = $this->mapper->fetchRow(['id_char' => 'A', 'id_int' => 1]);
        $this->assertInstanceOf(ArrayObject::class, $row, 'row1 ainda existe v1');
        $this->assertEquals($row1, $row->toArray());
        $row = $this->mapper->fetchRow(['id_char' => 'B', 'id_int' => 2]);
        $this->assertInstanceOf(ArrayObject::class, $row, 'row2 ainda existe v1');
        $this->assertEquals($row2, $row->toArray());

        // Marca para remover o registro da tabela
        $this->mapper->setUseDeleted(false);

        // Remove o registro que não existe
        $this->mapper->delete(['id_char' => 'C']);

        // Verifica se ele foi removido
        $this->assertNotEmpty($this->mapper->fetchRow(['id_char' => 'A', 'id_int' => 1]), 'row1 ainda existe v3');
        $this->assertNotEmpty($this->mapper->fetchRow(['id_char' => 'B', 'id_int' => 2]), 'row2 ainda existe v3');

        // Remove o registro
        $this->mapper->delete(['id_char' => 'A']);

        // Verifica se ele foi removido
        $this->assertNull($this->mapper->fetchRow(['id_char' => 'A', 'id_int' => 1]), 'row1 não existe v4');
        $this->assertNotEmpty($this->mapper->fetchRow(['id_char' => 'B', 'id_int' => 2]), 'row2 ainda existe v4');
    }

    /**
     * Tests TableAdapter->update()
     */
    public function testUpdateArrayKey()
    {
        // Cria a tabela com chave string
        $this->mapper->setTableKey([
            MapperConcrete::KEY_INTEGER => 'id_int',
            MapperConcrete::KEY_STRING => ['id_char', 'artist']
        ]);
        $this->dropTables()->createTables(['album_array_two']);
        $this->mapper->setUseAllKeys(true);
        $this->mapper->setOrder(['id_int', 'id_char', 'artist']);

        // Abaixo é igual ao testDelete trocando 1, 2 por A, B
        $row1 = [
            'id_int' => 1,
            'id_char' => 'A',
            'artist' => 'Rush',
            'title' => 'Presto',
            'deleted' => 0
        ];
        $row2 = [
            'id_int' => 2,
            'id_char' => 'B',
            'artist' => 'Rush',
            'title' => 'Moving Pictures',
            'deleted' => 0
        ];

        $this->mapper->insert($row1);
        $this->mapper->insert($row2);

        // Verifica se o registro existe
        $row = $this->mapper->fetchRow(['id_char' => 'A', 'id_int' => 1, 'artist' => 'Rush']);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row1, $row->toArray(), 'row1 existe');
        $row = $this->mapper->fetchRow(['id_char' => 'B', 'id_int' => 2, 'artist' => 'Rush']);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals($row2, $row->toArray(), 'row2 existe');


        // atualizar o registro
        $this->mapper->update(['title' => 'New title'], ['id_char' => 'A', 'id_int' => 1, 'artist' => 'Rush']);

        // Verifica se foi removido
        $row = $this->mapper->fetchRow(['id_char' => 'A', 'id_int' => 1, 'artist' => 'Rush']);
        $this->assertInstanceOf(ArrayObject::class, $row);
        $this->assertEquals('New title', $row['title'], 'row1 atualizado ');
    }
}
