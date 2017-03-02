<?php

namespace RealejoTest\Utils;

/**
 * Version test case.
 */
use Realejo\Utils\DateFormatter;

class DateFormatterTest extends \PHPUnit\Framework\TestCase
{
    public function testToMysqlFromDateTimeObject()
    {
        $data = \DateTime::createFromFormat('d/m/Y H:i:s', '12/02/2016 00:00:00');
        $dataTest = DateFormatter::toMySQL($data);
        $this->assertEquals('2016-02-12 00:00:00', $dataTest);
    }

    public function testToMysqlFromString()
    {
        $dataTest = DateFormatter::toMySQL('12/02/2016 00:00:00');
        $this->assertEquals('2016-02-12 00:00:00', $dataTest);
    }

    public function testStaticDiffFromDateTimeObject()
    {
        $data1 = \DateTime::createFromFormat('d/m/Y H:i:s', '12/02/2016 01:02:03');
        $data2 = \DateTime::createFromFormat('d/m/Y H:i:s', '12/05/2018 03:02:01');

        //diferenca de anos entre as datas
        $dataDiffAno = DateFormatter::staticDiff($data1, $data2, 'y');
        $this->assertEquals(2, $dataDiffAno);

        $dataDiffMes = DateFormatter::staticDiff($data1, $data2, 'm');
        $this->assertEquals(27, $dataDiffMes);

        $dataDiffSemana = DateFormatter::staticDiff($data1, $data2, 'w');
        $this->assertEquals(117, $dataDiffSemana);

        $dataDiffDia = DateFormatter::staticDiff($data1, $data2, 'd');
        $this->assertEquals(820, $dataDiffDia);

        $dataDiffHora = DateFormatter::staticDiff($data1, $data2, 'h');
        $this->assertEquals(19682, $dataDiffHora);

        $dataDiffMinuto = DateFormatter::staticDiff($data1, $data2, 'i');
        $this->assertEquals(70858798, $dataDiffMinuto);

        $dataDiffSegundo = DateFormatter::staticDiff($data1, $data2, 's');
        $this->assertEquals(70858798, $dataDiffSegundo);
    }
}
