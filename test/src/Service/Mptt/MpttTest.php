<?php

namespace RealejoTest\Service\Mptt;

/**
 * MpttTest test case.
 *
 * @link      http://github.com/realejo/libraray-zf2
 * @copyright Copyright (c) 2014 Realejo (http://realejo.com.br)
 * @license   http://unlicense.org
 */

use Realejo\Cache\CacheService;
use Realejo\Service;
use RealejoTest\BaseTestCase;

/**
 * Mptt test case.
 */
class MpttTest extends BaseTestCase
{
    /**
     * Árvore mptt completa e *correta*
     * com left,right ordenado pelo id
     *
     * @var array
     */
    protected $idOrderedTree = [
        [1, 'Food', null, 1, 24],
        [2, 'Fruit', 1, 2, 13],
        [3, 'Red', 2, 3, 6],
        [4, 'Yellow', 2, 7, 10],
        [5, 'Green', 2, 11, 12],
        [6, 'Cherry', 3, 4, 5],
        [7, 'Banana', 4, 8, 9],
        [8, 'Meat', 1, 14, 19],
        [9, 'Beef', 8, 15, 16],
        [10, 'Pork', 8, 17, 18],
        [11, 'Vegetable', 1, 20, 23],
        [12, 'Carrot', 11, 21, 22],
    ];

    /**
     * Árvore mptt completa e *correta*
     * com left,right ordenado pelo name
     *
     * @var array
     */
    protected $nameOrderedTree = [
        [1, 'Food', null, 1, 24],
        [2, 'Fruit', 1, 2, 13],
        [3, 'Red', 2, 5, 8],
        [4, 'Yellow', 2, 9, 12],
        [5, 'Green', 2, 3, 4],
        [6, 'Cherry', 3, 6, 7],
        [7, 'Banana', 4, 10, 11],
        [8, 'Meat', 1, 14, 19],
        [9, 'Beef', 8, 15, 16],
        [10, 'Pork', 8, 17, 18],
        [11, 'Vegetable', 1, 20, 23],
        [12, 'Carrot', 11, 21, 22],
    ];

    /**
     * Será populada com os valores da arvore completa
     * @var array
     */
    protected $idOrderedRows = [];
    protected $nameOrderedRows = [];

    /**
     * Será populada com os valores da arvore completa sem as informações left,right
     * @var array
     */
    protected $defaultRows = [];

    protected $tables = ['mptt'];

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        $fields = ['id', 'name', 'parent_id', 'lft', 'rgt'];
        foreach ($this->idOrderedTree as $values) {
            $row = array_combine($fields, $values);
            $this->idOrderedRows[] = $row;
            unset($row['lft']);
            unset($row['rgt']);
            $this->defaultRows[] = $row;
        }

        foreach ($this->nameOrderedTree as $values) {
            $row = array_combine($fields, $values);
            $this->nameOrderedRows[] = $row;
        }

        $this->dropTables()->createTables();
    }

    /**
     * Tests Mptt->__construct()
     */
    public function testConstruct()
    {
        $cacheService = new CacheService();
        $cacheService->setCacheDir($this->getDataDir() . '/cache');

        // Cria a tabela sem a implementação do transversable
        $mapper = new MapperConcrete();
        $mapper->setCache($cacheService->getFrontend());

        $mptt = new ServiceConcrete($mapper, 'id');
        $mptt->setCache($cacheService->getFrontend());

        $this->assertInstanceOf(Service\Mptt\MpttServiceAbstract::class, $mptt);
        $this->assertInstanceOf(Service\ServiceAbstract::class, $mptt);
        $this->assertInstanceOf(MapperConcrete::class, $mptt->getMapper());
    }

    /**
     * Tests Mptt->setTraversal()
     * @expectedException \Exception
     */
    public function testSetTraversalIncomplete()
    {
        $cacheService = new CacheService();
        $cacheService->setCacheDir($this->getDataDir() . '/cache');

        // Cria a tabela sem a implementação do transversable
        $mapper = new MapperConcrete();
        $mapper->setCache($cacheService->getFrontend());

        $mptt = new ServiceConcrete($mapper, 'id');
        $mptt->setCache($cacheService->getFrontend());

        $this->assertInstanceOf(Service\Mptt\MpttServiceAbstract::class, $mptt);
        $this->assertInstanceOf(Service\ServiceAbstract::class, $mptt);

        $mptt = $mptt->setTraversal([]);

        $this->assertInstanceOf(Service\Mptt\MpttServiceAbstract::class, $mptt);

        // The Exception
        $mptt->setTraversal(['invalid' => 'invalid']);
    }

    /**
     * Tests Mptt->getColumns()
     */
    public function testGetColumns()
    {
        $cacheService = new CacheService();
        $cacheService->setCacheDir($this->getDataDir() . '/cache');

        // Cria a tabela sem a implementação do transversable
        $mapper = new MapperConcrete();
        $mapper->setCache($cacheService->getFrontend());

        $mptt = new ServiceConcrete($mapper, 'id');
        $mptt->setCache($cacheService->getFrontend());
        $this->assertInternalType('array', $mptt->getColumns());
        $this->assertNotNull($mptt->getColumns());
        $this->assertNotEmpty($mptt->getColumns());
        $this->assertEquals(['id', 'name', 'parent_id', 'lft', 'rgt'], $mptt->getColumns());
    }

    /**
     * Tests Mptt->setTraversal()
     */
    public function testSetTraversal()
    {
        $cacheService = new CacheService();
        $cacheService->setCacheDir($this->getDataDir() . '/cache');

        // Cria a tabela sem a implementação do transversable
        $mapper = new MapperConcrete();
        $mapper->setCache($cacheService->getFrontend());

        $mptt = new ServiceConcrete($mapper, 'id');
        $mptt->setCache($cacheService->getFrontend());

        $this->assertFalse($mptt->isTraversable());
        $mptt->setTraversal('parent_id');
        $this->assertTrue($mptt->isTraversable());
    }

    /**
     * Tests Mptt->rebuildTreeTraversal()
     */
    public function testRebuildTreeTraversal()
    {
        // Cria a tabela com os valores padrões
        $cacheService = new CacheService();
        $cacheService->setCacheDir($this->getDataDir() . '/cache');

        // Cria a tabela sem a implementação do transversable
        $mapper = new MapperConcrete();
        $mapper->setCache($cacheService->getFrontend());

        $mptt = new ServiceConcrete($mapper, 'id');
        $mptt->setCache($cacheService->getFrontend());

        $this->assertNull($mptt->getMapper()->fetchAll());
        foreach ($this->defaultRows as $row) {
            $mptt->insert($row);
        }
        $this->assertNotNull($mptt->getMapper()->fetchAll());
        $this->assertCount(count($this->defaultRows), $mptt->getMapper()->fetchAll());

        // Set traversal
        $this->assertFalse($mptt->isTraversable());
        $mptt->setTraversal('parent_id');
        $this->assertTrue($mptt->isTraversable());

        // Rebuild Tree
        $mptt->rebuildTreeTraversal();

        $fetchArray = [];
        foreach ($mptt->getMapper()->fetchAll() as $value) {
            $fetchArray[] = $value->toArray();
        }
        $this->assertEquals($this->idOrderedRows, $fetchArray);
        $mptt->setTraversal(['refColumn' => 'parent_id', 'order' => 'name']);

        // Rebuild Tree
        $mptt->rebuildTreeTraversal();
        $this->assertTrue($mptt->isTraversable());

        $fetchArray = [];
        foreach ($mptt->getMapper()->fetchAll() as $value) {
            $fetchArray[] = $value->toArray();
        }
        $this->assertEquals($this->nameOrderedRows, $fetchArray);
    }

    /**
     * Tests Mptt->rebuildTreeTraversal()
     */
    public function testInsert()
    {
        // Cria a tabela com os valores padrões
        $cacheService = new CacheService();
        $cacheService->setCacheDir($this->getDataDir() . '/cache');

        // Cria a tabela sem a implementação do transversable
        $mapper = new MapperConcrete();
        $mapper->setCache($cacheService->getFrontend());

        $mptt = new ServiceConcrete($mapper, 'id');
        $mptt->setCache($cacheService->getFrontend());

        $mptt->getMapper()->setOrder('id');
        $this->assertNull($mptt->getMapper()->fetchAll());

        // Set traversal
        $this->assertFalse($mptt->isTraversable());
        $mptt->setTraversal(['refColumn' => 'parent_id', 'order' => 'name']);
        $this->assertTrue($mptt->isTraversable());

        // Insert default rows
        foreach ($this->defaultRows as $row) {
            $mptt->insert($row);
        }
        $this->assertNotNull($mptt->getMapper()->fetchAll());
        $this->assertCount(count($this->defaultRows), $mptt->getMapper()->fetchAll());

        // Assert if left/right is correct
        $fetchArray = [];
        foreach ($mptt->getMapper()->fetchAll() as $value) {
            $fetchArray[] = $value->toArray();
        }
        $this->assertEquals($this->nameOrderedRows, $fetchArray);

        // reset the table
        $this->dropTables()->createTables();
        $this->assertNull($mptt->getMapper()->fetchAll());

        // Set traversal ordered by id
        $mptt->setTraversal(['refColumn' => 'parent_id']);
        $this->assertTrue($mptt->isTraversable());

        // insert default rows
        foreach ($this->defaultRows as $row) {
            $mptt->insert($row);
        }
        $this->assertNotNull($mptt->getMapper()->fetchAll());
        $this->assertCount(count($this->defaultRows), $mptt->getMapper()->fetchAll());

        // Assert if left/right is correct
        $fetchArray = [];
        foreach ($mptt->getMapper()->fetchAll() as $value) {
            $fetchArray[] = $value->toArray();
        }
        $this->assertEquals($this->idOrderedRows, $fetchArray);
    }

    /**
     * Tests Mptt->rebuildTreeTraversal()
     */
    public function testDelete()
    {
        // Cria a tabela com os valores padrões
        $cacheService = new CacheService();
        $cacheService->setCacheDir($this->getDataDir() . '/cache');

        // Cria a tabela sem a implementação do transversable
        $mapper = new MapperConcrete();
        $mapper->setCache($cacheService->getFrontend());

        $mptt = new ServiceConcrete($mapper, 'id');
        $mptt->setCache($cacheService->getFrontend());

        $mptt->getMapper()->setOrder('id');
        $this->assertNull($mptt->getMapper()->fetchAll());

        // Set traversal
        $this->assertFalse($mptt->isTraversable());
        $mptt->setTraversal(['refColumn' => 'parent_id', 'order' => 'name']);
        $this->assertTrue($mptt->isTraversable());

        // Insert default rows
        foreach ($this->defaultRows as $row) {
            $mptt->insert($row);
        }
        $this->assertNotNull($mptt->getMapper()->fetchAll());
        $this->assertCount(count($this->defaultRows), $mptt->getMapper()->fetchAll());

        // Assert if left/right is correct
        $fetchArray = [];
        foreach ($mptt->getMapper()->fetchAll() as $value) {
            $fetchArray[] = $value->toArray();
        }
        $this->assertEquals($this->nameOrderedRows, $fetchArray);

        // Remove a single node (Beef/9)
        $mptt->delete(9);

        // Verify its parent (Meat/8)
        $row = $mptt->getMapper()->fetchRow(8);
        $this->assertNotNull($row);
        $this->assertEquals(14, $row['lft']);
        $this->assertEquals(17, $row['rgt']);

        // Verify its sibling (Pork/10)
        $row = $mptt->getMapper()->fetchRow(10);
        $this->assertNotNull($row);
        $this->assertEquals(15, $row['lft']);
        $this->assertEquals(16, $row['rgt']);

        // Verify the root (Food/1)
        $row = $mptt->getMapper()->fetchRow(1);
        $this->assertNotNull($row);
        $this->assertEquals(1, $row['lft']);
        $this->assertEquals(22, $row['rgt']);

        // Verify its uncle (Vegetable/11)
        $row = $mptt->getMapper()->fetchRow(11);
        $this->assertNotNull($row);
        $this->assertEquals(18, $row['lft']);
        $this->assertEquals(21, $row['rgt']);

        // Verify its another uncle (Fruit/2)
        $row = $mptt->getMapper()->fetchRow(2);
        $this->assertNotNull($row);
        $this->assertEquals(2, $row['lft']);
        $this->assertEquals(13, $row['rgt']);

        // Put it back
        $mptt->insert($this->defaultRows[9 - 1]);

        // Assert if left/right is correct
        $fetchArray = [];
        foreach ($mptt->getMapper()->fetchAll() as $value) {
            $fetchArray[] = $value->toArray();
        }
        $this->assertEquals($this->nameOrderedRows, $fetchArray);

        // Remove a node with child (Meat/8)
        $mptt->delete(8);

        // Verify its childs is gone
        $this->assertNull($mptt->getMapper()->fetchRow(8));
        $this->assertNull($mptt->getMapper()->fetchRow(9));
        $this->assertNull($mptt->getMapper()->fetchRow(10));

        // Verify the root (Food/1)
        $row = $mptt->getMapper()->fetchRow(1);
        $this->assertNotNull($row);
        $this->assertEquals(1, $row['lft']);
        $this->assertEquals(18, $row['rgt']);

        // Verify its uncle (Vegetable/11)
        $row = $mptt->getMapper()->fetchRow(11);
        $this->assertNotNull($row);
        $this->assertEquals(14, $row['lft']);
        $this->assertEquals(17, $row['rgt']);

        // Verify its another uncle (Fruit/2)
        $row = $mptt->getMapper()->fetchRow(2);
        $this->assertNotNull($row);
        $this->assertEquals(2, $row['lft']);
        $this->assertEquals(13, $row['rgt']);

        // Put them back
        $mptt->insert($this->defaultRows[8 - 1]);
        $mptt->insert($this->defaultRows[10 - 1]);
        $mptt->insert($this->defaultRows[9 - 1]);

        // Assert if left/right is correct
        $fetchArray = [];
        foreach ($mptt->getMapper()->fetchAll() as $value) {
            $fetchArray[] = $value->toArray();
        }
        $this->assertEquals($this->nameOrderedRows, $fetchArray);
    }
}
