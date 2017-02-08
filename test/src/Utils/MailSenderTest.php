<?php

namespace RealejoTest\Utils;

/**
 * Version test case.
 */
use Realejo\Utils\MailSender;

class MailSenderTest extends \PHPUnit\Framework\TestCase
{
    private $defaultConfig = [
        'name'       => 'Bobs',
        'email'      => '***REMOVED***',
        'host'       => '***REMOVED***',
        'username'   => '***REMOVED***',
        'password'   => '***REMOVED***',
        'port'       => '2525'
    ];

    /**
     * @expectedException \Exception
     */
    public function test__consturctError()
    {
        $oMailer = new MailSender();
    }

    /**
     */
    public function test__consturctSuccess()
    {
        $oMailer = new MailSender($this->defaultConfig);
        $this->assertInstanceOf('\Realejo\Utils\MailSender', $oMailer);
        $this->assertInstanceOf('\Zend\Mail\Transport\Smtp', $oMailer->getTransport());
    }

    /**
     * Ok
     */
    public function test__envioEmailHtmlSucesso()
    {
        $this->markTestIncomplete(
            'This test has not been revised yet.'
        );

        $oMailer = new MailSender($this->defaultConfig);
        $htmlEmail = '<html><head><title>Ola mundo</title></head><body><h2>Teste do html</h2>Aqui é um post em html<br/></body></html>';

        $return = $oMailer->sendEmail(null, null, null, 'mario.costa@realejo.com.br', 'Ola', $htmlEmail);
        $this->assertNull($return, 'Envio com sucesso');
    }

    /**
     *
     */
    public function test__envioEmailComAnexoStrings()
    {
        $this->markTestIncomplete(
            'This test has not been revised yet.'
        );

        $oMailer = new MailSender($this->defaultConfig);

        $files = [
            TEST_ROOT . '/assets/sql/album.create.sql',
            TEST_ROOT . '/assets/sql/album.drop.sql'
        ];

        $return = $oMailer->sendEmail(null, null, null, 'mario.costa@realejo.com.br', 'Ola', 'Ola mundo, teste do anexo com array de strings', ['anexos'=>$files]);

        $this->assertNull($return, 'Envio com sucesso');
    }

    /**
     * Verificar
     */
    public function test__envioEmailComAnexoSource()
    {
        $this->markTestIncomplete(
            'This test has not been revised yet.'
        );

        $oMailer = new MailSender($this->defaultConfig);

        $file1 = fopen(TEST_ROOT . '/assets/sql/album.create.sql', 'r');
        $file2 = fopen(TEST_ROOT . '/assets/sql/album.drop.sql', 'r');

        $files = [
            $file1, $file2,
        ];

        $return = $oMailer->sendEmail(null, null, null, 'mario.costa@realejo.com.br', 'Ola', 'Ola mundo, teste do anexo com array de resources', ['anexos'=>$files]);
        $this->assertNull($return, 'Envio com sucesso');
    }
}
