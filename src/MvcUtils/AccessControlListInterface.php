<?php

namespace Realejo\MvcUtils;

/**
 * Describes the interface of a module that exposes access control list to admin.
 */
interface AccessControlListInterface
{
    /**
     * Retuns an arrays contains two items. First, the \Zend\Permissions\Acl\Acl object containing the definitions of
     * roles and resources. Second, an array containing roles descriptions.
     *
     * @return array[\Zend\Permissions\Acl\Acl, array]
     */
    public function getAcl();
}
