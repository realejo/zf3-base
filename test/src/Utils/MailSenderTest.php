<?php

namespace RealejoTest\Utils;

/**
 * Version test case.
 */
use Realejo\Utils\MailSender;

class MailSenderTest extends \PHPUnit\Framework\TestCase
{
    private $defaultConfig = [
        'name'       => 'Bobs Fa',
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
     *
     */
    public function test__setEmailComAnexoStrings()
    {
        $oMailer = new MailSender($this->defaultConfig);

        $files = [
            TEST_ROOT . '/assets/sql/album.create.sql',
            TEST_ROOT . '/assets/sql/album.drop.sql'
        ];

        $oMailer->setEmailMessage(null, null, 'Mario Costa', 'mario.costa@realejo.com.br', 'Olá', 'Olá mundo, teste do anexo com array de strings', ['anexos'=>$files]);

        //verifica se os remetentes e destinatarios estao ok
        $this->assertEquals('Bobs Fa (teste local)', $oMailer->getMessage()->getFrom()->current()->getName());
        $this->assertEquals('sistemas@realejo.com.br', $oMailer->getMessage()->getFrom()->current()->getEmail());
        $this->assertEquals('Mario Costa', $oMailer->getMessage()->getTo()->current()->getName());
        $this->assertEquals('mario.costa@realejo.com.br', $oMailer->getMessage()->getTo()->current()->getEmail());

        //define e verifica o reply-to
        $oMailer->getMessage()->setReplyTo($this->defaultConfig['email'], $this->defaultConfig['name']);
        $this->assertEquals('Bobs Fa', $oMailer->getMessage()->getReplyTo()->current()->getName());
        $this->assertEquals('***REMOVED***', $oMailer->getMessage()->getReplyTo()->current()->getEmail());

        //verifica o assunto
        $this->assertEquals('Olá', $oMailer->getMessage()->getSubject());

        //verifica se existe o mime part html
        $this->assertNotEmpty($oMailer->getMessage()->getBody());
        $this->assertNotEmpty(3, count($oMailer->getMessage()->getBody()->getParts()));

        $parts = $oMailer->getMessage()->getBody()->getParts();
        $this->assertInstanceOf('\Zend\Mime\Part', $parts[0]);
        $this->assertInstanceOf('\Zend\Mime\Part', $parts[1]);
        $this->assertInstanceOf('\Zend\Mime\Part', $parts[2]);

        $this->assertEquals('Olá mundo, teste do anexo com array de strings', $parts[0]->getContent());
        $this->assertEquals('album.create.sql', $parts[1]->getFileName());
        $this->assertEquals('album.drop.sql', $parts[2]->getFileName());
    }

    /**
     * Verificar
     */
    public function test__envioEmailComAnexoSource()
    {
        $oMailer = new MailSender($this->defaultConfig);

        $file1 = fopen(TEST_ROOT . '/assets/sql/album.create.sql', 'r');
        $file2 = fopen(TEST_ROOT . '/assets/sql/album.drop.sql', 'r');

        $files = [
            $file1, $file2,
        ];

        $oMailer->setEmailMessage(null, null, 'Mario Costa', 'mario.costa@realejo.com.br', 'Olá', 'Olá mundo, teste do anexo com array de strings', ['anexos'=>$files]);

        //verifica se os remetentes e destinatarios estao ok
        $this->assertEquals('Bobs Fa (teste local)', $oMailer->getMessage()->getFrom()->current()->getName());
        $this->assertEquals('sistemas@realejo.com.br', $oMailer->getMessage()->getFrom()->current()->getEmail());
        $this->assertEquals('Mario Costa', $oMailer->getMessage()->getTo()->current()->getName());
        $this->assertEquals('mario.costa@realejo.com.br', $oMailer->getMessage()->getTo()->current()->getEmail());

        //define e verifica o reply-to
        $oMailer->getMessage()->setReplyTo($this->defaultConfig['email'], $this->defaultConfig['name']);
        $this->assertEquals('Bobs Fa', $oMailer->getMessage()->getReplyTo()->current()->getName());
        $this->assertEquals('***REMOVED***', $oMailer->getMessage()->getReplyTo()->current()->getEmail());

        //verifica o assunto
        $this->assertEquals('Olá', $oMailer->getMessage()->getSubject());

        //verifica se existe o mime part html
        $this->assertNotEmpty($oMailer->getMessage()->getBody());
        $this->assertNotEmpty(3, count($oMailer->getMessage()->getBody()->getParts()));

        $parts = $oMailer->getMessage()->getBody()->getParts();
        $this->assertInstanceOf('\Zend\Mime\Part', $parts[0]);
        $this->assertInstanceOf('\Zend\Mime\Part', $parts[1]);
        $this->assertInstanceOf('\Zend\Mime\Part', $parts[2]);

        $this->assertEquals('Olá mundo, teste do anexo com array de strings', $parts[0]->getContent());
        $this->assertEquals('application/octet-stream', $parts[1]->getType());
        $this->assertEquals('application/octet-stream', $parts[2]->getType());
    }

    /**
     * Ok
     */
    public function test__envioEmailHtmlSucesso()
    {
        $oMailer = new MailSender($this->defaultConfig);
        $htmlEmail = '<html><head><title>Olá mundo</title></head><body><h2>Teste do html</h2>Aqui é um post em html<br/></body></html>';

        $oMailer->setEmailMessage(null, null, 'Mario Costa', 'mario.costa@realejo.com.br', 'Olá', $htmlEmail);

        //verifica se os remetentes e destinatarios estao ok
        $this->assertEquals('Bobs Fa (teste local)', $oMailer->getMessage()->getFrom()->current()->getName());
        $this->assertEquals('sistemas@realejo.com.br', $oMailer->getMessage()->getFrom()->current()->getEmail());
        $this->assertEquals('Mario Costa', $oMailer->getMessage()->getTo()->current()->getName());
        $this->assertEquals('mario.costa@realejo.com.br', $oMailer->getMessage()->getTo()->current()->getEmail());

        //define e verifica o reply-to
        $oMailer->getMessage()->setReplyTo($this->defaultConfig['email'], $this->defaultConfig['name']);
        $this->assertEquals('Bobs Fa', $oMailer->getMessage()->getReplyTo()->current()->getName());
        $this->assertEquals('***REMOVED***', $oMailer->getMessage()->getReplyTo()->current()->getEmail());

        //verifica o assunto
        $this->assertEquals('Olá', $oMailer->getMessage()->getSubject());

        //verifica se existe o mime part html
        $this->assertNotEmpty($oMailer->getMessage()->getBody());
        $this->assertNotEmpty(1, count($oMailer->getMessage()->getBody()->getParts()));

        $parts = $oMailer->getMessage()->getBody()->getParts();
        $this->assertInstanceOf('\Zend\Mime\Part', $parts[0]);

        $this->assertEquals('<html><head><title>Olá mundo</title></head><body><h2>Teste do html</h2>Aqui é um post em html<br/></body></html>', $parts[0]->getContent());

        $this->assertNull($oMailer->send());
    }
}
