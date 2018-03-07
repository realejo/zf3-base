<?php

namespace Realejo\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Coloca no FormValidation na view
 *
 * @author     Realejo
 * @copyright  Copyright (c) 2018 Realejo Design Ltda. (http://www.realejo.com.br)
 *
 * @uses viewHelper AbstractHelper
 */
class FormValidation extends AbstractHelper
{
    private static $initialized = false;

    public function init()
    {
        if (! self::$initialized) {
            $config = $this->getView()->applicationConfig();

            if (! isset($config['realejo']['vendor']['form-validation'])) {
                throw new \InvalidArgumentException('Form Validation not defined.');
            }

            $config = $config['realejo']['vendor']['form-validation'];

            if (empty($config['js'])) {
                throw new \InvalidArgumentException('Javascript not defined for FormValidation.');
            }

            foreach ($config['js'] as $file) {
                $this->getView()->headScript()->appendFile($file);
            }

            if (! empty($config['css'])) {
                foreach ($config['css'] as $file) {
                    $this->getView()->headLink()->appendStylesheet($file);
                }
            }

            self::$initialized = true;
        }
    }

    /**
     * Grava o nome e marca o selecionado em um SELECT
     *
     * @return string valor formatado
     */
    public function __invoke()
    {
        $this->init();
        return $this;
    }
}
