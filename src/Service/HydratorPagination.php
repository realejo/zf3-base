<?php

namespace Realejo\Service;

use Realejo\Stdlib\ArrayObject;
use Zend\Hydrator\ArraySerializable;
use Zend\Paginator\Adapter\DbSelect;

/**
 * Class HydratorPagination
 * @deprecated passamos a usar o HydratingResultSet
 */
class HydratorPagination extends DbSelect
{
    /**
     * @var ArrayObject
     */
    protected $hydratorEntity = null;

    /**
     * @var ArraySerializable
     */
    protected $hydrator = null;

    /**
     *
     * @return ArraySerializable
     */
    public function getHydrator()
    {
        if (!isset($this->hydrator)) {
            $this->hydrator = new ArraySerializable();
        }

        return $this->hydrator;
    }

    /**
     * @param ArraySerializable $hydrator
     * @return HydratorPagination
     */
    public function setHydrator(ArraySerializable $hydrator = null)
    {
        $this->hydrator = $hydrator;
        return $this;
    }

    /**
     * @return ArrayObject
     */
    public function getHydratorEntity()
    {
        if (isset($this->hydratorEntity)) {
            $hydrator = $this->hydratorEntity;
            return new $hydrator();
        }

        return new ArrayObject();
    }

    /**
     * @param ArrayObject|null $hydratorEntity
     * @return HydratorPagination
     */
    public function setHydratorEntity(ArrayObject $hydratorEntity = null)
    {
        $this->hydratorEntity = $hydratorEntity;
        return $this;
    }

    public function getItems($offset, $itemCountPerPage)
    {
        $fetchAll = parent::getItems($offset, $itemCountPerPage);

        $hydrator = $this->getHydrator();
        if (empty($hydrator)) {
            return $fetchAll;
        }
        $hydratorEntity = $this->getHydratorEntity();

        foreach ($fetchAll as $id => $row) {
            if ($row instanceof ArrayObject) {
                $row = $row->getArrayCopy();
            }
            $fetchAll[$id] = $hydrator->hydrate($row, new $hydratorEntity);
        }

        return $fetchAll;
    }
}
