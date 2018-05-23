<?php

namespace RealejoTest\Service\Mptt;

use Realejo\Service\Mptt\MpttServiceAbstract;

class ServiceConcrete extends MpttServiceAbstract
{
    /**
     * @var string
     */
    protected $mapperClass = MapperConcrete::class;
}
