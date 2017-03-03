<?php
/**
 * Realejo Library ZF2
 *
 * Gerenciador de cache utilizado pelo App_Model
 *
 * Ele cria automaticamente a pasta de cache, dentro de data/cache, baseado no nome da classe
 *
 * @link      http://github.com/realejo/libraray-zf2
 * @copyright Copyright (c) 2014 Realejo (http://realejo.com.br)
 * @license   http://unlicense.org
 */
namespace Realejo\App\Loader;

abstract class Awareness
{
    /**
     * @var Loader
     */
    private $loader;

    /**
     * Retorna o App_Loader a ser usado
     *
     * @return Loader
     */
    public function getLoader()
    {
        if (! isset($this->loader)) {
            $this->setLoader(new Loader());
        }

        return $this->loader;
    }

    /**
     * Grava o App_Loader que deve ser usado
     * Ele é usado com DI durante a criação do model no App_Loader
     *
     * @param Loader $loader
     */
    public function setLoader($loader)
    {
        $this->loader = $loader;
    }
}
