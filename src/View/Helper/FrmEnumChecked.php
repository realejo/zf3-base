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

class FrmEnumChecked extends AbstractHelper
{
    /**
     * @param Enum $enum
     * @param array $options
     * @return string
     */
    public function __invoke(Enum $enum, $options = [])
    {
        // Recupera os registros
        $names = $enum::getNames();

        // Remove the names that cannot be user
        if (isset($options['not-in'])) {
            foreach ($options['not-in'] as $v) {
                unset($names[$v]);
            }
        }

        // Monta as opções
        $values = [];
        if (! empty($names)) {
            foreach ($names as $v => $n) {
                if ($enum instanceof EnumFlagged) {
                    $checked = ($enum->has($v)) ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>';
                } else {
                    $checked = ($enum->is($v)) ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>';
                }

                $values[] = "<p class=\"form-control-static\"> $checked $n </p>";
            }
        }

        if (isset($options['cols'])) {
            $countValues = count($values);
            $slice = ceil($countValues / $options['cols']);
            $columns = [];
            $columnSize = round(12 / $options['cols']);
            for ($c = 1; $c <= $options['cols']; $c++) {
                $columns[$c] = "<div class=\"col-xs-$columnSize\">"
                        . implode('', array_slice($values, ($c - 1) * $slice, $slice))
                    .'</div>';
            }
            return '<div class="row">' . implode('', $columns) . '</div>';
        }

        return implode('', $values);
    }
}
