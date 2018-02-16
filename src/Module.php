<?php

namespace Realejo;

use Realejo\MvcUtils\AccessControlListInterface;
use RealejoAdmin\Model\Permissions\Acl;
use Zend\Console\Request as ConsoleRequest;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Router\RouteMatch;
use Zend\Session;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
