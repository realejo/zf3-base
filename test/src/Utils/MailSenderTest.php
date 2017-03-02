<?php

namespace RealejoTest\Utils;

/**
 * Version test case.
 */
use Realejo\Utils\MailSender;
use Zend\Mime;
use Zend\Mail;

class MailSenderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @expectedException \Exception
     */
    public function testConstructError()
    {
        new MailSender();
    }

    private function getMailSenderConfig()
    {
        $configFile = __DIR__ . '/../../configs/mailsender.php';
        if (file_exists($configFile)) {
            return require $configFile;
        }

        return require $configFile . '.dist';
    }

    /**
     */
    public function testConstructSuccess()
    {
        $config = $this->getMailSenderConfig();
        $oMailer = new MailSender($config['mailsender']);
        $this->assertInstanceOf(MailSender::class, $oMailer);
        $this->assertInstanceOf(Mail\Transport\Smtp::class, $oMailer->getTransport());
    }

       /**
     *
     */
    public function testSetEmailComAnexoStrings()
    {
        $config = $this->getMailSenderConfig();
        $oMailer = new MailSender($config['mailsender']);

        $files = [
            TEST_ROOT . '/assets/sql/album.create.sql',
            TEST_ROOT . '/assets/sql/album.drop.sql'
        ];

        $oMailer->setEmailMessage(
            null, null,
            $config['test-name'], $config['test-email'],
            'Olá',
            'Olá mundo, teste do anexo com array de strings',
            ['anexos' => $files]
        );

        //verifica se os remetentes e destinatarios estao ok
        $this->assertEquals($config['mailsender']['name'], $oMailer->getMessage()->getFrom()->current()->getName());
        $this->assertEquals($config['mailsender']['email'], $oMailer->getMessage()->getFrom()->current()->getEmail());
        $this->assertEquals($config['test-name'], $oMailer->getMessage()->getTo()->current()->getName());
        $this->assertEquals($config['test-email'], $oMailer->getMessage()->getTo()->current()->getEmail());

        //define e verifica o reply-to
        $oMailer->getMessage()->setReplyTo($config['mailsender']['email'], $config['mailsender']['name']);
        $this->assertEquals($config['mailsender']['name'], $oMailer->getMessage()->getReplyTo()->current()->getName());
        $this->assertEquals($config['mailsender']['email'], $oMailer->getMessage()->getReplyTo()->current()->getEmail());

        //verifica o assunto
        $this->assertEquals('Olá', $oMailer->getMessage()->getSubject());

        //verifica se existe o mime part html
        $this->assertNotEmpty($oMailer->getMessage()->getBody());
        $this->assertNotEmpty(3, count($oMailer->getMessage()->getBody()->getParts()));

        $parts = $oMailer->getMessage()->getBody()->getParts();
        $this->assertInstanceOf(Mime\Part::class, $parts[0]);
        $this->assertInstanceOf(Mime\Part::class, $parts[1]);
        $this->assertInstanceOf(Mime\Part::class, $parts[2]);

        $this->assertEquals('Olá mundo, teste do anexo com array de strings', $parts[0]->getContent());
        $this->assertEquals('album.create.sql', $parts[1]->getFileName());
        $this->assertEquals('album.drop.sql', $parts[2]->getFileName());
    }

    /**
     * Verificar
     */
    public function testEnvioEmailComAnexoSource()
    {
        $config = $this->getMailSenderConfig();
        $oMailer = new MailSender($config['mailsender']);

        $file1 = fopen(TEST_ROOT . '/assets/sql/album.create.sql', 'r');
        $file2 = fopen(TEST_ROOT . '/assets/sql/album.drop.sql', 'r');

        $files = [
            $file1, $file2,
        ];

        $oMailer->setEmailMessage(
            null, null,
            $config['test-name'], $config['test-email'],
            'Olá',
            'Olá mundo, teste do anexo com array de strings',
            ['anexos' => $files]
        );

        //verifica se os remetentes e destinatarios estao ok
        $this->assertEquals($config['mailsender']['name'], $oMailer->getMessage()->getFrom()->current()->getName());
        $this->assertEquals($config['mailsender']['email'], $oMailer->getMessage()->getFrom()->current()->getEmail());
        $this->assertEquals($config['test-name'], $oMailer->getMessage()->getTo()->current()->getName());
        $this->assertEquals($config['test-email'], $oMailer->getMessage()->getTo()->current()->getEmail());

        //define e verifica o reply-to
        $oMailer->getMessage()->setReplyTo($config['mailsender']['email'], $config['mailsender']['name']);
        $this->assertEquals($config['mailsender']['name'], $oMailer->getMessage()->getReplyTo()->current()->getName());
        $this->assertEquals($config['mailsender']['email'], $oMailer->getMessage()->getReplyTo()->current()->getEmail());

        //verifica o assunto
        $this->assertEquals('Olá', $oMailer->getMessage()->getSubject());

        //verifica se existe o mime part html
        $this->assertNotEmpty($oMailer->getMessage()->getBody());
        $this->assertNotEmpty(3, count($oMailer->getMessage()->getBody()->getParts()));

        $parts = $oMailer->getMessage()->getBody()->getParts();
        $this->assertInstanceOf(Mime\Part::class, $parts[0]);
        $this->assertInstanceOf(Mime\Part::class, $parts[1]);
        $this->assertInstanceOf(Mime\Part::class, $parts[2]);

        $this->assertEquals('Olá mundo, teste do anexo com array de strings', $parts[0]->getContent());
        $this->assertEquals('application/octet-stream', $parts[1]->getType());
        $this->assertEquals('application/octet-stream', $parts[2]->getType());
    }

    /**
     * Ok
     */
    public function testEnvioEmailHtmlSuccess()
    {
        $config = $this->getMailSenderConfig();
        $oMailer = new MailSender($config['mailsender']);
        $htmlEmail = '<html><head><title>Olá mundo</title></head><body><h2>Teste do html</h2>Aqui é um post em html<br/></body></html>';

        $oMailer->setEmailMessage(
            null, null,
            $config['test-name'], $config['test-email'],
            'Olá', $htmlEmail
        );

        //verifica se os remetentes e destinatarios estao ok
        $this->assertEquals($config['mailsender']['name'], $oMailer->getMessage()->getFrom()->current()->getName());
        $this->assertEquals($config['mailsender']['email'], $oMailer->getMessage()->getFrom()->current()->getEmail());
        $this->assertEquals($config['test-name'], $oMailer->getMessage()->getTo()->current()->getName());
        $this->assertEquals($config['test-email'], $oMailer->getMessage()->getTo()->current()->getEmail());

        //define e verifica o reply-to
        $oMailer->getMessage()->setReplyTo($config['mailsender']['email'], $config['mailsender']['name']);
        $this->assertEquals($config['mailsender']['name'], $oMailer->getMessage()->getReplyTo()->current()->getName());
        $this->assertEquals($config['mailsender']['email'], $oMailer->getMessage()->getReplyTo()->current()->getEmail());

        //verifica o assunto
        $this->assertEquals('Olá', $oMailer->getMessage()->getSubject());

        //verifica se existe o mime part html
        $this->assertNotEmpty($oMailer->getMessage()->getBody());
        $this->assertNotEmpty(1, count($oMailer->getMessage()->getBody()->getParts()));

        $parts = $oMailer->getMessage()->getBody()->getParts();
        $this->assertInstanceOf(Mime\Part::class, $parts[0]);

        $this->assertEquals('<html><head><title>Olá mundo</title></head><body><h2>Teste do html</h2>Aqui é um post em html<br/></body></html>', $parts[0]->getContent());

        if ($config['test-really-send-email'] === true) {
            $this->assertNull($oMailer->send());
        }
    }

    /**
     * Ok
     */
    public function testMessageDifferentSender()
    {
        $config = $this->getMailSenderConfig();
        $oMailer = new MailSender($config['mailsender']);
        $htmlEmail = '<html><head><title>Olá mundo</title></head><body><h2>Teste do html</h2>Aqui é um post em html<br/></body></html>';

        $oMailer->setEmailMessage(
            'Another sender', 'another-email@somewhere.com',
            $config['test-name'], $config['test-email'],
            'Olá', $htmlEmail
        );

        //verifica se os remetentes e destinatarios estao ok
        $this->assertEquals('Another sender', $oMailer->getMessage()->getFrom()->current()->getName());
        $this->assertEquals('another-email@somewhere.com', $oMailer->getMessage()->getFrom()->current()->getEmail());
        $this->assertEquals($config['test-name'], $oMailer->getMessage()->getTo()->current()->getName());
        $this->assertEquals($config['test-email'], $oMailer->getMessage()->getTo()->current()->getEmail());

        //define e verifica o reply-to
        $oMailer->getMessage()->setReplyTo('another-email2@somewhere.com', 'Another sender 2');
        $this->assertEquals('Another sender 2', $oMailer->getMessage()->getReplyTo()->current()->getName());
        $this->assertEquals('another-email2@somewhere.com', $oMailer->getMessage()->getReplyTo()->current()->getEmail());

        //verifica o assunto
        $this->assertEquals('Olá', $oMailer->getMessage()->getSubject());

        //verifica se existe o mime part html
        $this->assertNotEmpty($oMailer->getMessage()->getBody());
        $this->assertNotEmpty(1, count($oMailer->getMessage()->getBody()->getParts()));

        $parts = $oMailer->getMessage()->getBody()->getParts();
        $this->assertInstanceOf(Mime\Part::class, $parts[0]);

        $this->assertEquals('<html><head><title>Olá mundo</title></head><body><h2>Teste do html</h2>Aqui é um post em html<br/></body></html>', $parts[0]->getContent());

        if ($config['test-really-send-email'] === true) {
            $this->assertNull($oMailer->send());
        }
    }

    public function testiIsValid()
    {
        $config = $this->getMailSenderConfig();
        $oMailer = new MailSender($config['mailsender']);

        $this->assertFalse($oMailer->isEmail('wrooong'));
        $this->assertTrue($oMailer->isEmail('test@email.com'));
    }
}
