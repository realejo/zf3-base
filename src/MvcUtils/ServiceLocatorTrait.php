<?php

namespace Realejo\MvcUtils;

use Zend\ServiceManager\ServiceManager;

trait ServiceLocatorTrait
{

    /**
     * @var ServiceManager
     */
    public $serviceLocator;

    /**
     * @return ServiceManager
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param ServiceManager $serviceLocator
     * @return self
     */
    public function setServiceLocator(ServiceManager $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    public function hasServiceLocator()
    {
        return null !== $this->serviceLocator;
    }

    public function getFromServiceLocator($class)
    {
        if (!$this->hasServiceLocator()) {
            throw new \RuntimeException('Service locator not defined!');
        }

        if (!$this->getServiceLocator()->has($class) && $this->getServiceLocator() instanceof ServiceManager) {
            $newService = new $class();
            if (method_exists($newService, 'setServiceLocator')) {
                $newService->setServiceLocator($this->getServiceLocator());
            }
            $this->getServiceLocator()->setService($class, $newService);
        }

        return $this->getServiceLocator()->get($class);
    }
}
