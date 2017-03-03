<?php
/**
 * Aplicação de MPTT
 *
 * @link      http://github.com/realejo/libraray-zf2
 * @copyright Copyright (c) 2014 Realejo (http://realejo.com.br)
 * @license   http://unlicense.org
 *
 * @see http://www.sitepoint.com/print/hierarchical-data-database
 */
namespace Realejo\Service\Mptt;

use Realejo\Service\ServiceAbstract;
use Zend\Db\Sql\Predicate\Expression;

abstract class MpttServiceAbstract extends ServiceAbstract
{
    /**
     * Traversal tree information for
     * Modified Preorder Tree Traversal Model
     *
     * Values:
     *  'left'      => column name for left value, default: lft
     *  'right'     => column name for right value, default: rgt
     *  'column'    => column name for identifying row (primary key assumed)
     *  'refColumn' => column name for parent id (if not set, will look in reference map for own table match)
     *  'order'     => order by for rebuilding tree (e.g. "`name` ASC, `age` DESC")
     *
     * @var array
     */
    protected $traversal = [];

    /**
     * Automatically is set to true once traversal info is set and verified
     *
     * @var boolean
    */
    protected $isTraversable = false;

    /**
     * Modified to initialize traversal
     * @param null $mapper
     * @param null $key
     * @param null $dbAdapter
     */
    public function __construct($mapper = null, $key = null, $dbAdapter = null)
    {
        $this->setMapper($mapper);
        $this->getMapper()->setTableKey($key);
        $this->initTraversal();
    }

    /**
     * Prepares the traversal information
     *
     */
    protected function initTraversal()
    {
        if (empty($this->traversal)) {
            return;
        }

        $columns = $this->getColumns();

        // Verify 'left' value and column
        if (! isset($this->traversal['left'])) {
            $this->traversal['left'] = 'lft';
        }

        if (! in_array($this->traversal['left'], $columns)) {
            throw new \Exception("Column '" . $this->traversal['left'] . "' not found in table for tree traversal");
        }

        // Verify 'right' value and column
        if (! isset($this->traversal['right'])) {
            $this->traversal['right'] = 'rgt';
        }

        if (! in_array($this->traversal['right'], $columns)) {
            throw new \Exception("Column '" . $this->traversal['right'] . "' not found in table for tree traversal");
        }

        // Check for identifying column
        if (! isset($this->traversal['column'])) {
            $this->traversal['column'] = $this->getMapper()->getTableKey();
        }

        if (! in_array($this->traversal['column'], $columns)) {
            throw new \Exception("Column '" . $this->traversal['column'] . "' not found in table for tree traversal");
        }

        // Check for reference column
        if (! isset($this->traversal['refColumn'])) {
            throw new \Exception("Unable to determine reference column for traversal");
        }

        if (! in_array($this->traversal['refColumn'], $columns)) {
            throw new \Exception("Column '" . $this->traversal['refColumn'] . "' not found in table for tree traversal");
        }

        // Check the order
        if (! isset($this->traversal['order'])) {
            $this->traversal['order'] = $this->getMapper()->getTableKey();
        }

        if (! in_array($this->traversal['order'], $columns)) {
            throw new \Exception("Column '" . $this->traversal['order'] . "' not found in table for tree traversal");
        }

        $this->isTraversable = true;
    }

    /**
     * Public function to rebuild tree traversal. The recursive function
     * _rebuildTreeTraversal() must be called without arguments.
     *
     * @return $this - Fluent interface
     */
    public function rebuildTreeTraversal()
    {
        $this->rebuildTreeTraversalRecursive();

        return $this;
    }

    /**
     * Recursively rebuilds the modified preorder tree traversal
     * data based on a parent id column
     *
     * @param int $parentId
     * @param int $leftValue
     * @return int new right value
     */
    protected function rebuildTreeTraversalRecursive($parentId = null, $leftValue = 0)
    {
        $this->verifyTraversable();

        // Do not use getSQLSelect() to avoid defined joins
        $select = $this->getMapper()->getTableGateway()
                       ->getSql()->select();

        if (! empty($parentId)) {
            $select->where([$this->traversal['refColumn'] => $parentId]);
        } else {
            $select->where(new Expression("{$this->traversal['refColumn']} IS NULL OR {$this->traversal['refColumn']} = 0"));
        }

        // Define the order
        $select->order($this->traversal['order']);

        $rightValue = $leftValue + 1;

        $rowset = $this->getMapper()->getTableGateway()->selectWith($select);
        foreach ($rowset as $row) {
            $rightValue = $this->rebuildTreeTraversalRecursive($row->{$this->traversal['column']}, $rightValue);
        }

        if (! empty($parentId)) {
            $this->getMapper()->getTableGateway()
                 ->update([
                     $this->traversal['left'] => $leftValue,
                     $this->traversal['right'] => $rightValue
                 ], [$this->traversal['column'] => $parentId]);
        }

        return $rightValue + 1;
    }

    /**
     * Override insert method
     *
     * @param mixed $set
     *
     * @return primary key
     */
    public function insert($set)
    {
        return $this->isTraversable() ? $this->insertTraversable($set) : parent::getMapper()->insert($set);
    }

    /**
     * Calculates left and right values for new row and inserts it.
     * Also adjusts all rows to make room for the new row.
     *
     * @param array $set
     * @return int $id
     * @throws \Exception
     */
    protected function insertTraversable($set)
    {
        $this->verifyTraversable();

        // Disable traversable flag to prevent automatic traversable manipulation during updates.
        $isTraversable = $this->isTraversable;
        $this->isTraversable = false;

        if (array_key_exists($this->traversal['refColumn'], $set) && $set[$this->traversal['refColumn']] > 0) {
            // Find parent
            $parent_id = $set[$this->traversal['refColumn']];
            $parent = $this->getMapper()->getTableGateway()->select([$this->getMapper()->getTableKey() => $parent_id])->current();
            if (null === $parent) {
                throw new \Exception("Traversable error: Parent id {$parent_id} not found");
            }

            $lt = (double) $parent->{$this->traversal['left']};
            $rt = (double) $parent->{$this->traversal['right']};

            // Find siblings
            $select = $this->getMapper()->getTableGateway()->getSql()->select();
            $select->where([$this->traversal['refColumn'] => $parent_id]);

            $siblings = $this->getMapper()->getTableGateway()->selectWith($select);

            // Define the position of the new node
            // Checks if it has any sibling on the left, considering the defined order
            $previousSibling = null;
            foreach ($siblings as $s) {
                if (is_string($s[$this->traversal['order']])) {
                    if (strcmp($s[$this->traversal['order']], $set[$this->traversal['order']]) > 0) {
                        break;
                    }
                } else {
                    if ($s[$this->traversal['order']] >= $set[$this->traversal['order']]) {
                        break;
                    }
                }
                $previousSibling = $s;
            }

            // If there is a sibling on the left, use it for positioning
            if (! empty($previousSibling)) {
                $lt = (double) $previousSibling->{$this->traversal['left']};
                $rt = (double) $previousSibling->{$this->traversal['right']};
                $pos = $rt;

                $set[$this->traversal['left']] = $rt + 1;
                $set[$this->traversal['right']] = $rt + 2;

            // Insert o the start os the list os siblings or alone
            } else {
                $set[$this->traversal['left']] = $lt + 1;
                $set[$this->traversal['right']] = $lt + 2;
                $pos = $lt;
            }

            // Make room for the new node
            $this->getMapper()->getTableGateway()->update(
                [
                    $this->traversal['left'] => new Expression("{$this->traversal['left']} + 2"),
                ],
                new Expression("{$this->traversal['left']} > $pos")
            );

            $this->getMapper()->getTableGateway()->update(
                [
                    $this->traversal['right'] => new Expression("{$this->traversal['right']} + 2"),
                ],
                new Expression("{$this->traversal['right']} > $pos")
            );
        } else {
            $select = $this->getMapper()->getTableGateway()->getSql()->select();
            $select->reset('columns')->columns(['theMax' => new Expression("MAX({$this->traversal['right']})")]);
            $maxRt = (double) $this->getMapper()->getTableGateway()->selectWith($select)->current()->theMax;
            $set[$this->traversal['left']] = $maxRt + 1;
            $set[$this->traversal['right']] = $maxRt + 2;
        }

        // Do insert
        $id = $this->getMapper()->getTableGateway()->insert($set);

        // Reset isTraversable flag to previous value.
        $this->isTraversable = $isTraversable;

        return $id;
    }

    /**
     * Override delete method
     *
     * @param mixed $key
     * @return bool|int
     */
    public function delete($key)
    {
        return $this->isTraversable() ? $this->deleteTraversable($key) : parent::getMapper()->delete($key);
    }

    /**
     * Remove the row and calculate left and right values for the remaining rows.
     * It will delete all child nodes from the row
     *
     * @param array $key
     * @return int
     */
    protected function deleteTraversable($key)
    {
        $this->verifyTraversable();

        // Disable traversable flag to prevent automatic traversable manipulation during updates.
        $isTraversable = $this->isTraversable;
        $this->isTraversable = false;

        // Get the row to be deleted
        $row = $this->getMapper()->fetchRow($key);

        // Delete the node and it's childs
        $delete = $this->getMapper()
                       ->getTableGateway()
                       ->delete(new Expression(
                           "{$this->traversal['left']} >= {$row[$this->traversal['left']]}"
                           . " and {$this->traversal['right']} <= {$row[$this->traversal['right']]}"
                       ));

        // Fixes the left,right for the remaining nodes
        $fix = $row[$this->traversal['right']] - $row[$this->traversal['left']] + 1;
        $this->getMapper()->getTableGateway()->update(
            [
                $this->traversal['left'] => new Expression("{$this->traversal['left']} - $fix"),
            ],
            new Expression("{$this->traversal['left']} > {$row[$this->traversal['left']]}")
        );
        $this->getMapper()->getTableGateway()->update(
            [
                $this->traversal['right'] => new Expression("{$this->traversal['right']} - $fix"),
            ],
            new Expression("{$this->traversal['right']} > {$row[$this->traversal['right']]}")
        );


        // Reset isTraversable flag to previous value.
        $this->isTraversable = $isTraversable;

        return $delete;
    }

    /**
     * Returns columns names
     *
     * @todo colocar no cache
     *
     * @return array columns
     */
    public function getColumns()
    {
        if (! isset($this->_columns)) {
            $metadata = new \Zend\Db\Metadata\Metadata($this->getMapper()->getTableGateway()->getAdapter());
            $this->_columns = $metadata->getColumnNames($this->getMapper()->getTableName());
        }

        return $this->_columns;
    }

    /**
     * Defines the traversal info
     *
     * if passes a string, assumes it's the refColumn
     *
     * @param string|array $traversal
     *
     * @return self
     */
    public function setTraversal($traversal)
    {
        // Verifica se é apenas o campo de referencia
        if (is_string($traversal)) {
            $traversal = ['refColumn' => $traversal];
        }

        $this->traversal = $traversal;

        $this->initTraversal();

        return $this;
    }

    /**
     * Return if the table is traversable
     * Only set to true after initTraversal
     */
    public function isTraversable()
    {
        return $this->isTraversable;
    }


    /**
     * Verifies that the current table is a traversable
     *
     * @throws \Exception - Table is not traversable
     */
    protected function verifyTraversable()
    {
        if (! $this->isTraversable()) {
            throw new \Exception("Table {$this->getMapper()->getTableName()} is not traversable");
        }
    }
}
