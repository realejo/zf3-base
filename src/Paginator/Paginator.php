<?php

/**
 * There is a bug when retrieve cache for adapter based on filesystem.
 * So, this class is used to override the _getCacheInternalId method
 *
 * https://github.com/zendframework/zend-paginator/issues/1
 * https://github.com/zendframework/zend-paginator/issues/41
 */

namespace Realejo\Paginator;

use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator as ZendPaginator;

class Paginator extends ZendPaginator
{
    protected function _getCacheInternalId()
    {
        $adapter = $this->getAdapter();

        if ($adapter instanceof DbSelect) {
            $reflection = new \ReflectionObject($adapter);

            /**
             * @var  $select \Zend\Db\Sql\Select
             */
            $property = $reflection->getProperty('select');
            $property->setAccessible(true);
            $select = $property->getValue($adapter);

            /**
             * @var  $sql \Zend\Db\Sql\Sql
             */
            $property = $reflection->getProperty('sql');
            $property->setAccessible(true);
            $sql = $property->getValue($adapter);

            return md5(
                $reflection->getName()
                . hash('sha512', $select->getSQLString($sql->getAdapter()->getPlatform()))
                . $this->getItemCountPerPage()
            );
        }

        return parent::_getCacheInternalId();
    }
}
