<?php
/**
 * Retorna o HTML de um <select> apra usar em formulários
 *
 * @param string $nome Name/ID a ser usado no <select>
 * @param string $selecionado Valor pré selecionado
 * @param string $opts Opções adicionais
 *
 * Os valores de option serão os valores dos campos definidos em $status
 *
 * As opções adicionais podem ser
 *  - placeholder => legenda quando nenhum estiver selecionado e/ou junto com show-empty
 *                   se usado com FALSE, nunca irá mostrar o vazio, mesmo que não tenha um selecionado
 *  - show-empty  => mostra um <option> vazio no inicio mesmo com um selecionado
 *
 * @return string
 */

namespace Realejo\View\Helper;

use Realejo\Enum\Enum;
use Zend\View\Helper\AbstractHelper;

class FrmEnumSelect extends AbstractHelper
{
    /**
     * @param Enum $enum
     * @param string $name
     * @param array $options
     * @return string
     */
    public function __invoke(Enum $enum, $name, $options = [])
    {
        // Recupera os registros
        $names = $enum::getNames();

        // Remove the names that cannot be user
        if (isset($options['not-in'])) {
            foreach ($options['not-in'] as $v) {
                unset($names[$v]);
            }
        }

        // Verifica se deve mostrar a primeira opção em branco
        $showEmpty = (isset($options['show-empty']) && $options['show-empty'] === true);
        $neverShowEmpty = (isset($options['show-empty']) && $options['show-empty'] === false);
        $required = (isset($options['required']) && $options['required'] === true);

        // Defines the correct holder
        $placeholder = $selectPlaceholder = $options['placeholder'] ?? '';
        if (!empty($placeholder)) {
            $selectPlaceholder = "placeholder=\"$selectPlaceholder\"";
        }

        if ($required) {
            $selectPlaceholder .= ' required ';
        }

        // Monta as opções
        $options = '';
        if (!empty($names)) {
            foreach ($names as $v => $n) {
                $selected = ($enum->is($v)) ? 'selected="selected"' : '';
                $options .= "<option value=\"$v\" $selected>$n</option>";
            }
        }

        $hasSelected = strpos($options, 'selected="selected"') !== false;

        // Abre o select
        $select = "<select class=\"form-control\" name=\"$name\" id=\"$name\" $selectPlaceholder>";

        // Verifica se tem valor padrão selecionado
        if ((!$hasSelected || $showEmpty) && !$neverShowEmpty) {
            $select .= "<option value=\"\">$placeholder</option>";
        }

        // Coloca as opções
        $select .= $options;

        // Fecha o select
        $select .= '</select>';

        // Retorna o select
        return $select;
    }
}
