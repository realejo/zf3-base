<?php
namespace Realejo\Service;

use Zend\Hydrator\ArraySerializable;
use Realejo\Stdlib\ArrayObject;

class HydratorPagination extends \Zend\Paginator\Adapter\DbSelect
{
    /**
     * @var \Realejo\Stdlib\ArrayObject
     */
    protected $hydratorEntity = null;

    /**
     * @var \Zend\Hydrator\ArraySerializable
     */
    protected $hydrator = null;

    /**
     *
     * @return \Zend\Hydrator\ArraySerializable
     */
    public function getHydrator()
    {
        if (!isset($this->hydrator)) {
            $this->hydrator = new ArraySerializable();
        }

        return $this->hydrator;
    }

    /**
     * @param \Zend\Hydrator\ArraySerializable $hydrator
     * @return \Realejo\Mapper\MapperPagination
     */
    public function setHydrator(\Zend\Hydrator\ArraySerializable $hydrator = null)
    {
        $this->hydrator = $hydrator;
        return $this;
    }

    /**
     * @return \Realejo\Stdlib\ArrayObject
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
     * @param \Realejo\Stdlib\ArrayObject $hydrator
     * @return \Realejo\Mapper\MapperPagination
     */
    public function setHydratorEntity(\Realejo\Stdlib\ArrayObject $hydratorEntity = null)
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

        foreach ($fetchAll as $id=>$row) {
            $fetchAll[$id] = $hydrator->hydrate($row->getArrayCopy(), new $hydratorEntity);
        }

        return $fetchAll;
    }
}
