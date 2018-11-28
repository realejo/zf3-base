<?php

namespace RealejoTest\Service\Metadata;

use Realejo\Cache\CacheService;
use Realejo\Service\Metadata\MetadataMapper;
use Realejo\Service\Metadata\MetadataService;
use Realejo\Stdlib\ArrayObject;
use RealejoTest\BaseTestCase;
use Zend\Db\Sql\Expression;

/**
 * MetadataService test case.
 */
class MetadataServiceTest extends BaseTestCase
{

    /**
     *
     * @var MetadataService
     */
    private $metadataService;

    private $schema = [
        [
            'id_info' => 123,
            'type' => MetadataService::BOOLEAN,
            'nick' => 'bool'
        ],
        [
            'id_info' => 321,
            'type' => MetadataService::DATE,
            'nick' => 'date'
        ],
        [
            'id_info' => 159,
            'type' => MetadataService::DATETIME,
            'nick' => 'datetime'
        ],
        [
            'id_info' => 753,
            'type' => MetadataService::DECIMAL,
            'nick' => 'decimal'
        ],
        [
            'id_info' => 78,
            'type' => MetadataService::INTEGER,
            'nick' => 'integer'
        ]
        ,
        [
            'id_info' => 456,
            'type' => MetadataService::TEXT,
            'nick' => 'text'
        ]
    ];

    private $cacheFetchAllKey;

    private $cacheSchemaKey = 'metadataschema_metadata_schema';

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->metadataService = new MetadataService();
        $cacheService = new CacheService();
        $cacheService->setCacheDir($this->getDataDir() . '/cache');
        $this->metadataService->setCache($cacheService->getFrontend());

        $this->metadataService
            ->setMapper(MetadataMapperReference::class)
            ->setMetadataMappers('metadata_schema', 'metadata_value', 'fk_reference')
            ->setUseCache(true);

        $this->cacheFetchAllKey = 'fetchAll' . md5(var_export(false, true)
                . var_export(false, true)
                . var_export(null, true)
                . var_export(null, true)
                . var_export(null, true)
                . var_export(null, true));

        // Grava no cache um fetchAll ficticio
        $fetchAll = [];
        foreach ($this->schema as $row) {
            $fetchAll[] = new ArrayObject($row);
        }
        $this->metadataService
            ->getCache()
            ->setItem($this->cacheFetchAllKey, $fetchAll);

        $this->assertEquals($fetchAll, $this->metadataService->getCache()->getItem($this->cacheFetchAllKey));

        // Cria o schema associado pelo id
        $schemaById = [];
        foreach ($this->schema as $s) {
            $schemaById[$s['id_info']] = $s;
        }

        // Grava no cache um metadata ficticio
        $this->metadataService
            ->getCache()
            ->setItem($this->cacheSchemaKey, $schemaById);

        $this->assertEquals($schemaById, $this->metadataService->getCache()->getItem($this->cacheSchemaKey));
    }

    private function createTableSchema()
    {
        $this->createTables(['metadata_schema', 'metadata_value']);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->metadataService->cleanCache();
        $this->metadataService = null;
        $this->dropTables(['metadata_schema', 'metadata_value']);
        parent::tearDown();
    }

    /**
     * Tests MetadataService->getSchemaByKeyNames()
     */
    public function testGetSchemaByKeyNames()
    {
        // Cria o schema exemplo para keyname
        $schemaByKeyName = [];
        foreach ($this->schema as $s) {
            $schemaByKeyName[$s['nick']] = $s;
        }
        $this->assertEquals($schemaByKeyName, $this->metadataService->getSchemaByKeyNames());
        $this->assertEquals($schemaByKeyName, $this->metadataService->getSchemaByKeyNames(true));
        $this->assertEquals($schemaByKeyName, $this->metadataService->getSchemaByKeyNames(false));
    }

    /**
     * Tests MetadataService->getCorrectSetKey()
     */
    public function testGetCorrectSetKey()
    {
        $service = new MetadataService();
        $reflection = new \ReflectionClass(get_class($service));
        $method = $reflection->getMethod('getCorrectSetKey');
        $method->setAccessible(true);

        $this->assertEquals('value_boolean', $method->invokeArgs($service, [['type' => MetadataService::BOOLEAN]]));
        $this->assertEquals('value_date', $method->invokeArgs($service, [['type' => MetadataService::DATE]]));
        $this->assertEquals('value_datetime', $method->invokeArgs($service, [['type' => MetadataService::DATETIME]]));
        $this->assertEquals('value_decimal', $method->invokeArgs($service, [['type' => MetadataService::DECIMAL]]));
        $this->assertEquals('value_integer', $method->invokeArgs($service, [['type' => MetadataService::INTEGER]]));
        $this->assertEquals('value_text', $method->invokeArgs($service, [['type' => MetadataService::TEXT]]));
    }

    /**
     * Tests MetadataService->getCorrectSetKey()
     */
    public function testGetCorrectSetValue()
    {
        $service = new MetadataService();
        $reflection = new \ReflectionClass(get_class($service));
        $method = $reflection->getMethod('getCorrectSetValue');
        $method->setAccessible(true);

        $this->assertEquals(1, $method->invokeArgs($service, [['type' => MetadataService::BOOLEAN], 1]));
        $this->assertEquals(1, $method->invokeArgs($service, [['type' => MetadataService::BOOLEAN], true]));
        $this->assertEquals(0, $method->invokeArgs($service, [['type' => MetadataService::BOOLEAN], 0]));
        $this->assertEquals(0, $method->invokeArgs($service, [['type' => MetadataService::BOOLEAN], false]));

        $this->assertNull($method->invokeArgs($service, [['type' => MetadataService::BOOLEAN], null]));
        $this->assertEquals(0, $method->invokeArgs($service, [['type' => MetadataService::BOOLEAN], '']));

        $this->assertEquals('2016-12-10',
            $method->invokeArgs($service, [['type' => MetadataService::DATE], '10/12/2016']));
        $this->assertEquals('2016-12-10',
            $method->invokeArgs($service, [['type' => MetadataService::DATE], '10/12/2016 14:25:24']));
        $this->assertEquals('0', $method->invokeArgs($service, [['type' => MetadataService::DATE], '0']));
        $this->assertNull(null, $method->invokeArgs($service, [['type' => MetadataService::DATE], null]));

        $this->assertEquals('value_datetime',
            $method->invokeArgs($service, [['type' => MetadataService::DATETIME], 'value_datetime']));
        $this->assertEquals('2016-12-10 00:00:00',
            $method->invokeArgs($service, [['type' => MetadataService::DATETIME], '10/12/2016']));
        $this->assertEquals('2016-12-10 13:13:12',
            $method->invokeArgs($service, [['type' => MetadataService::DATETIME], '10/12/2016 13:13:12']));

        $this->assertEquals(0, $method->invokeArgs($service, [['type' => MetadataService::DECIMAL], 'value_decimal']));
        $this->assertEquals('0',
            $method->invokeArgs($service, [['type' => MetadataService::DECIMAL], 'value_decimal']));
        $this->assertEquals(0, $method->invokeArgs($service, [['type' => MetadataService::INTEGER], 'value_integer']));
        $this->assertEquals('0',
            $method->invokeArgs($service, [['type' => MetadataService::INTEGER], 'value_integer']));
        $this->assertEquals('value_text',
            $method->invokeArgs($service, [['type' => MetadataService::TEXT], 'value_text']));

        $this->assertNull($method->invokeArgs($service, [['type' => MetadataService::DECIMAL], null]));
        $this->assertNull($method->invokeArgs($service, [['type' => MetadataService::INTEGER], null]));
        $this->assertNull($method->invokeArgs($service, [['type' => MetadataService::TEXT], null]));
    }

    /**
     * Tests MetadataService->getMapperSchema()
     */
    public function testGetMappersSchema()
    {
        $service = new MetadataService();
        $cacheService = new CacheService();
        $cacheService->setCacheDir($this->getDataDir() . '/cache');
        $service->setCache($cacheService->getFrontend());

        $this->assertNull($service->getMapperSchema());
        $this->assertNull($service->getMapperValue());
        $this->assertInstanceOf(MetadataService::class,
            $service->setMetadataMappers('schemaTable', 'valuesTable', 'foreignKeyName'));
        $this->assertInstanceOf(MetadataMapper::class, $service->getMapperSchema());
        $this->assertEquals('schemaTable', $service->getMapperSchema()->getTableName());
        $this->assertInstanceOf(MetadataMapper::class, $service->getMapperValue());
        $this->assertEquals('valuesTable', $service->getMapperValue()->getTableName());
        $this->assertEquals(['fk_info', 'foreignKeyName'], $service->getMapperValue()->getTableKey());
        $this->assertEquals('fk_info', $service->getMapperValue()->getTableKey(true));
    }

    public function testCache()
    {
        $service = new MetadataService();
        $cacheService = new CacheService();
        $cacheService->setCacheDir($this->getDataDir() . '/cache');
        $service->setCache($cacheService->getFrontend());

        $this->assertInstanceOf(MetadataService::class,
            $service->setMetadataMappers('tableone', 'tablesecond', 'keyname'));
        $service->setMapper(MetadataMapperReference::class);

        $this->assertFalse($service->getUseCache());
        $this->assertFalse($service->getMapperSchema()->getUseCache());
        $this->assertFalse($service->getMapperValue()->getUseCache());
        $this->assertFalse($service->getMapper()->getUseCache());

        $this->assertInstanceOf(MetadataService::class, $service->setUseCache(true));
        $this->assertTrue($service->getUseCache());
        $this->assertTrue($service->getMapperSchema()->getUseCache());
        $this->assertTrue($service->getMapperValue()->getUseCache());

        $this->assertInstanceOf(MetadataService::class, $service->setUseCache(false));

        $this->assertFalse($service->getUseCache());
        $this->assertFalse($service->getMapperSchema()->getUseCache());
        $this->assertFalse($service->getMapperValue()->getUseCache());

        $this->assertInstanceOf(MetadataService::class, $service->setUseCache(true));

        $this->assertTrue($service->getCache()->setItem('servicekey', 'servicedata'));
        $this->assertNotEmpty($service->getCache()->hasItem('servicekey'));
        $this->assertEquals('servicedata', $service->getCache()->getItem('servicekey'));

        $this->assertTrue($service->getMapperSchema()->getCache()->setItem('schemakey', 'schemadata'));
        $this->assertNotEmpty($service->getMapperSchema()->getCache()->hasItem('schemakey'));
        $this->assertNotEmpty($service->getCache()->hasItem('schemakey'));
        $this->assertEquals('schemadata', $service->getMapperSchema()->getCache()->getItem('schemakey'));
        $this->assertEquals('schemadata', $service->getCache()->getItem('schemakey'));

        $this->assertTrue($service->getMapperValue()->getCache()->setItem('valuekey', 'valuedata'));
        $this->assertNotEmpty($service->getMapperValue()->getCache()->hasItem('valuekey'));
        $this->assertNotEmpty($service->getCache()->hasItem('valuekey'));
        $this->assertEquals('valuedata', $service->getMapperValue()->getCache()->getItem('valuekey'));
        $this->assertEquals('valuedata', $service->getCache()->getItem('valuekey'));

        $this->assertTrue($service->getCache()->flush());

        $this->assertFalse($service->getCache()->hasItem('servicekey'));
        $this->assertNull($service->getCache()->getItem('servicekey'));
        $this->assertFalse($service->getCache()->hasItem('schemakey'));
        $this->assertNull($service->getCache()->getItem('schemakey'));
        $this->assertFalse($service->getCache()->hasItem('valuekey'));
        $this->assertNull($service->getCache()->getItem('valuekey'));
        $this->assertFalse($service->getMapperSchema()->getCache()->hasItem('schemakey'));
        $this->assertNull($service->getMapperSchema()->getCache()->getItem('schemakey'));
        $this->assertFalse($service->getMapperValue()->getCache()->hasItem('valuekey'));
        $this->assertNull($service->getMapperValue()->getCache()->getItem('valuekey'));
    }

    /**
     * Tests MetadataService->getSchema()
     */
    public function testGetSchema()
    {
        // Cria o schema associado pelo id
        $schemaById = [];
        foreach ($this->schema as $s) {
            $schemaById[$s['id_info']] = $s;
        }

        $this->assertEquals($schemaById, $this->metadataService->getSchema());
        $this->assertEquals($schemaById, $this->metadataService->getSchema(true));
        $this->assertEquals($schemaById, $this->metadataService->getSchema(false));

        // apaga o cache do schema, mas mantem do fetchAll
        $this->assertTrue($this->metadataService->getCache()->removeItem($this->cacheSchemaKey));

        $this->assertEquals($schemaById, $this->metadataService->getSchema());
        $this->assertEquals($schemaById, $this->metadataService->getSchema(true));
        $this->assertEquals($schemaById, $this->metadataService->getSchema(false));
    }

    /**
     * Tests MetadataMapper->getWhere()
     * @depends testGetMappersSchema
     */
    public function testGetWhere()
    {
        $this->assertInternalType('array', $this->metadataService->getWhere([]));
        $this->assertEquals([], $this->metadataService->getWhere([]));

        $this->assertNull($this->metadataService->getWhere(null));

        $this->assertInternalType('array', $this->metadataService->getWhere(['metadata' => []]));
        $this->assertEquals([], $this->metadataService->getWhere(['metadata' => []]));

        $this->assertInternalType('array', $this->metadataService->getWhere(['metadata' => null]));
        $this->assertEquals([], $this->metadataService->getWhere(['metadata' => null]));
    }

    /**
     * Tests MetadataMapper->getWhere()
     * @depends testGetMappersSchema
     */
    public function testGetWhereBoolean()
    {
        // Cria as tabelas
        $this->createTableSchema();

        /**
         * @var $where Expression[]
         */
        $where = $this->metadataService->getWhere(['metadata' => ['bool' => true]]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(123)} AND value_boolean = 1)",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['bool' => true]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(123)} AND value_boolean = 1)",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['metadata' => ['bool' => false]]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(123)} AND value_boolean = 0)",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['bool' => false]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(123)} AND value_boolean = 0)",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['metadata' => ['bool' => 1]]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(123)} AND value_boolean = 1)",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['bool' => 1]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(123)} AND value_boolean = 1)",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['metadata' => ['bool' => 0]]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(123)} AND value_boolean = 0)",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['bool' => 0]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(123)} AND value_boolean = 0)",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['metadata' => ['bool' => null]]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(123)} AND value_boolean IS NULL) OR NOT EXISTS ({$this->getSqlSchemaString(123)})",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['bool' => null]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(123)} AND value_boolean IS NULL) OR NOT EXISTS ({$this->getSqlSchemaString(123)})",
            $where[0]->getExpression());

        /*  array(
         'cd_info' => 321,
         'type' => MetadataService::DATE,
         'nick' => 'date'
         ),
         array(
         'cd_info' => 159,
         'type' => MetadataService::DATETIME,
         'nick' => 'datetime'
         ),
         array(
         'cd_info' => 753,
         'type' => MetadataService::DECIMAL,
         'nick' => 'decimal'
         ),
         ) */
    }

    /**
     * Tests MetadataMapper->getWhere()
     * @depends testGetMappersSchema
     */
    public function testGetWhereInteger()
    {
        // Cria as tabelas
        $this->createTableSchema();

        /**
         * @var $where Expression[]
         */
        $where = $this->metadataService->getWhere(['metadata' => ['integer' => 10]]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(78)} AND value_integer = 10)",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['integer' => 10]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(78)} AND value_integer = 10)",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['metadata' => ['integer' => 0]]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(78)} AND value_integer = 0)",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['integer' => 0]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(78)} AND value_integer = 0)",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['metadata' => ['integer' => null]]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(78)} AND value_integer IS NULL) OR NOT EXISTS ({$this->getSqlSchemaString(78)})",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['integer' => null]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(78)} AND value_integer IS NULL) OR NOT EXISTS ({$this->getSqlSchemaString(78)})",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['integer' => -99]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(78)} AND value_integer = -99)",
            $where[0]->getExpression());
    }

    /**
     * Tests MetadataMapper->getWhere()
     * @depends testGetMappersSchema
     */
    public function testGetWhereString()
    {
        // Cria as tabelas
        $this->createTableSchema();

        /**
         * @var $where Expression[]
         */
        $where = $this->metadataService->getWhere(['metadata' => ['text' => 10]]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(456)} AND value_text = '10')",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['text' => 10]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(456)} AND value_text = '10')",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['metadata' => ['text' => 0]]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(456)} AND value_text = '0')",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['text' => 0]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(456)} AND value_text = '0')",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['metadata' => ['text' => 'qwerty']]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(456)} AND value_text = 'qwerty')",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['text' => 'qwerty']);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(456)} AND value_text = 'qwerty')",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['metadata' => ['text' => '']]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(456)} AND value_text IS NULL) OR NOT EXISTS ({$this->getSqlSchemaString(456)})",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['text' => '']);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(456)} AND value_text IS NULL) OR NOT EXISTS ({$this->getSqlSchemaString(456)})",
            $where[0]->getExpression());


        $where = $this->metadataService->getWhere(['metadata' => ['text' => null]]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(456)} AND value_text IS NULL) OR NOT EXISTS ({$this->getSqlSchemaString(456)})",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['text' => null]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(456)} AND value_text IS NULL) OR NOT EXISTS ({$this->getSqlSchemaString(456)})",
            $where[0]->getExpression());
    }

    /**
     * Tests MetadataMapper->getWhere()
     * @depends testGetMappersSchema
     */
    public function testGetWhereDate()
    {
        // Cria as tabelas
        $this->createTableSchema();

        /**
         * @var $where Expression[]
         */
        $where = $this->metadataService->getWhere(['metadata' => ['date' => '15/10/2016']]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(321)} AND value_date = '2016-10-15')",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['date' => '15/10/2016']);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(321)} AND value_date = '2016-10-15')",
            $where[0]->getExpression());


        $where = $this->metadataService->getWhere(['metadata' => ['date' => '2016-10-15']]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(321)} AND value_date = '2016-10-15')",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['date' => '2016-10-15']);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(321)} AND value_date = '2016-10-15')",
            $where[0]->getExpression());


        $where = $this->metadataService->getWhere(['metadata' => ['date' => '15/10/2016 14:24:35']]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(321)} AND value_date = '2016-10-15')",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['date' => '15/10/2016 14:24:35']);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(321)} AND value_date = '2016-10-15')",
            $where[0]->getExpression());


        $where = $this->metadataService->getWhere(['metadata' => ['date' => '2016-10-15 14:24:35']]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(321)} AND value_date = '2016-10-15')",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['date' => '2016-10-15 14:24:35']);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(321)} AND value_date = '2016-10-15')",
            $where[0]->getExpression());


        $where = $this->metadataService->getWhere(['metadata' => ['date' => '']]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(321)} AND value_date = '')",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['date' => '']);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(321)} AND value_date = '')",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['metadata' => ['date' => null]]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(321)} AND value_date IS NULL) OR NOT EXISTS ({$this->getSqlSchemaString(321)})",
            $where[0]->getExpression());

        $where = $this->metadataService->getWhere(['date' => null]);
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf(Expression::class, $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(321)} AND value_date IS NULL) OR NOT EXISTS ({$this->getSqlSchemaString(321)})",
            $where[0]->getExpression());
    }

    private function getSqlSchemaString($idInfo)
    {
        return "SELECT * FROM metadata_value WHERE metadata_value.fk_info=$idInfo AND tblreference.id_reference=metadata_value.fk_reference";
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage schemaTable invalid
     */
    public function testSetSchemaMapper()
    {
        $service = new MetadataService();
        $service->setMetadataMappers(new \Realejo\Service\Metadata\MetadataMapper('tablename', 'keyname'), null, null);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage valueTable invalid
     */
    public function testSetValuesMapper()
    {
        $service = new MetadataService();
        $service->setMetadataMappers('tableone', new \Realejo\Service\Metadata\MetadataMapper('tablename', 'keyname'),
            null);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage mapperForeignKey invalid
     */
    public function testSetForeignKey()
    {
        $service = new MetadataService();
        $service->setMetadataMappers('tableone', 'tableone', null);
    }
}
