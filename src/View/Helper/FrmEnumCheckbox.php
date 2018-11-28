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
use Realejo\Enum\EnumFlagged;
use Zend\View\Helper\AbstractHelper;

class FrmEnumCheckbox extends AbstractHelper
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

        $inputName = ($enum instanceof EnumFlagged) ? $name . '[]' : $name;

        $required = (isset($options['required']) && $options['required'] === true) ? 'required' : '';

        $showDescription = (isset($options['show-description']) && $options['show-description'] === true);

        $readOnly = $options['read-only'] ?? 0;

        // Monta as opções
        $checkbox = [];
        if (!empty($names)) {
            foreach ($names as $v => $n) {
                if ($showDescription) {
                    $n .= ' <span class="tip" title="' . $enum->getValueDescription($v) . '"><i class="fa fa fa-question-circle"></i></span>';
                }

                $checked = ($enum instanceof EnumFlagged) ? $enum->has($v) : $enum->is($v);
                $isReadOnly = ($readOnly && ($v & $readOnly));

                if ($isReadOnly) {
                    $checked = ($checked) ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>';
                    $checkbox[] = "<p class=\"form-control-static\"> $checked $n </p>";
                } else {

                    $checked = ($checked) ? 'checked="checked"' : '';
                    $checkbox[] = '<div class="checkbox"> <label>'
                        . "<input type=\"checkbox\" $checked
                            id=\"$name\" $required
                            name=\"$inputName\" 
                            value=\"$v\">$n"
                        . '</label></div>';
                }
            }

            if (isset($options['cols'])) {
                $countCheckbox = count($checkbox);
                $slice = ceil($countCheckbox / $options['cols']);
                $columns = [];
                $columnSize = round(12 / $options['cols']);
                for ($c = 1; $c <= $options['cols']; $c++) {
                    $columns[$c] = "<div class=\"col-xs-$columnSize\">"
                        . implode('', array_slice($checkbox, ($c - 1) * $slice, $slice))
                        . '</div>';
                }
                return '<div class="row">' . implode('', $columns) . '</div>';
            }
        }

        return implode('', $checkbox);
    }
}
