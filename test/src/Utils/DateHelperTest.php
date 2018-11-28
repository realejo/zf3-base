<?php

namespace RealejoTest\Utils;

/**
 * Version test case.
 */

use PHPUnit\Framework\TestCase;
use Realejo\Utils\DateHelper;

class DateHelperTest extends TestCase
{
    public function testToMysqlFromDateTimeObject()
    {
        $data = \DateTime::createFromFormat('d/m/Y H:i:s', '12/02/2016 00:00:00');
        $dataTest = DateHelper::toMySQL($data);
        $this->assertEquals('2016-02-12 00:00:00', $dataTest);
    }

    public function testToMysqlFromString()
    {
        $dataTest = DateHelper::toMySQL('12/02/2016 00:00:00');
        $this->assertEquals('2016-02-12 00:00:00', $dataTest);
    }

    public function testStaticDiffFromDateTimeObject()
    {
        $data1 = \DateTime::createFromFormat('d/m/Y H:i:s', '12/02/2016 01:02:03',
            new \DateTimeZone('America/Sao_Paulo'));
        $data2 = \DateTime::createFromFormat('d/m/Y H:i:s', '12/05/2018 03:02:01',
            new \DateTimeZone('America/Sao_Paulo'));

        //diferenca de anos entre as datas
        $dataDiffAno = DateHelper::staticDiff($data1, $data2, 'y');
        $this->assertEquals(2, $dataDiffAno);

        $dataDiffMes = DateHelper::staticDiff($data1, $data2, 'm');
        $this->assertEquals(27, $dataDiffMes);

        $dataDiffSemana = DateHelper::staticDiff($data1, $data2, 'w');
        $this->assertEquals(117, $dataDiffSemana);

        $dataDiffDia = DateHelper::staticDiff($data1, $data2, 'd');
        $this->assertEquals(820, $dataDiffDia);

        $dataDiffHora = DateHelper::staticDiff($data1, $data2, 'h');
        $this->assertEquals(19682, $dataDiffHora);

        $dataDiffMinuto = DateHelper::staticDiff($data1, $data2, 'i');
        $this->assertEquals(1180979, $dataDiffMinuto);

        $dataDiffSegundo = DateHelper::staticDiff($data1, $data2, 's');
        $this->assertEquals(70858798, $dataDiffSegundo);

        $dataDiffSegundo = DateHelper::staticDiff($data1, $data2);
        $this->assertEquals(70858798, $dataDiffSegundo);
    }
}
