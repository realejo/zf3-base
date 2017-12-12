<?php

/**
 * There is a bug when retrieve cache for adapter based on filesystem.
 * So, this class is used to override the _getCacheInternalId method
 *
 * https://github.com/zendframework/zend-paginator/issues/1
 * https://github.com/zendframework/zend-paginator/issues/41
 */

namespace Realejo\MvcUtils;

class Paginator extends \Zend\Paginator\Paginator
{
    protected function _getCacheInternalId()
    {
        return md5(
            json_encode(
                get_object_vars($this->getAdapter())
            ) . $this->getItemCountPerPage()
        );
    }
}