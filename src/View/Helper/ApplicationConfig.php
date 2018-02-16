<?php

namespace Realejo\View\Helper;

class ApplicationConfig extends \Zend\View\Helper\AbstractHelper
{
    public function __construct($config)
    {
        $this->config = $config;
    }

    public function __invoke()
    {
        return $this->config;
    }

}
