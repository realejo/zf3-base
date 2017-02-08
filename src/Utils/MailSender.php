<?php
/**
 * Classe para envio de emails
 *
 * @link      http://github.com/realejo/libraray-zf2
 * @copyright Copyright (c) 2014 Realejo (http://realejo.com.br)
 * @license   http://unlicense.org
 */
namespace Realejo\Utils;

use Zend\Mail\Header\GenericHeader;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Stdlib\ArrayUtils;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp;

class MailSender
{

    /**
     * Nome padrão para os emails enviados pelo site.
     * @var string
     */
    private $_name;

    /**
     * Email padrão utilizado no site
     * @var string
     */
    private $_email;

    /**
     * @var int
     */
    private $_port = 25;

    /**
     * Método para ser usado no envio. Válido somente quando ENV == 'production'
     * @var string
     */
    private $_type;

    /**
     * Configurações de SMTP
     * @var string
     */
    private $_username;

    /**
     *  @var string
     */
    private $_password;


    /**
     * @var \Zend\Mail\Transport\Smtp
     */
    private $_transport;

    /**
     * @var string
     */
    private $_host;


    /**
     * Mail constructor.
     * @param null|array $config
     * @param bool $isException
     * @throws \Exception
     */
    public function __construct($config = null, $isException = false)
    {
        if (empty($config)) {
            if (defined('APPLICATION_PATH')) {
                $config = include APPLICATION_PATH . "/config/autoload/email_config.php";
            } else {
                throw new \Exception('Error loading email configuration in '.get_class($this).'::__construct()');
            }
        }

        $this->_name       = $config['name'];
        $this->_email      = $config['email'];
        $this->_host       = $config['host'];
        $this->_port       = $config['port'];

        // Verifica se está em ambiente de teste
        if (APPLICATION_ENV !== 'production') {
            $this->_name .= ' (teste local)';
            $this->_email = 'sistemas@realejo.com.br';
        }

        $this->_type       = ($isException) ? 'exception' : '';
        $this->_username   = isset($config['username']) ? $config['username'] : '';
        $this->_password   = isset($config['password']) ? $config['password'] : '';

        $serverconfig = [
            'name'     => $this->_host,
            'host'     => $this->_host,
            'port'     => $this->_port,
            'connection_class' => 'login',
            'connection_config' => [
                'username' => $this->_username,
                'password' => $this->_password,
                'ssl' => 'tls',
            ],
        ];

        // Verifica se há SSL
        if ( isset($config['ssl']) && $config['ssl'] != '') {
            $serverconfig['connection_config']['ssl'] = $config['ssl'];
        }

        // verifica se há uma porta definida
        if ( isset($config['port']) &&  $config['port'] != '') {
            $serverconfig['port'] = $config['port'];
        }

        // Configura o transport
        $this->setTransport(new Smtp(new SmtpOptions($serverconfig)));
    }

    /**
     * @param $replyName
     * @param $replyEmail
     * @param $toName
     * @param $toEmail
     * @param $subject
     * @param $message
     * @param array $opt
     * @throws \Exception
     */
    public function sendEmail($replyName = null, $replyEmail = null, $toName = null, $toEmail, $subject, $message, $opt = array() )
    {
        // Verifica a codificação
        $replyName  = $this->_fixEncoding($replyName);
        $replyEmail = $this->_fixEncoding($replyEmail);
        $toName     = $this->_fixEncoding($toName);
        $toEmail    = $this->_fixEncoding($toEmail);
        $subject    = $this->_fixEncoding($subject);
        $message    = $this->_fixEncoding($message);

        // Verifica o email do destinatário
        if ( empty($toEmail) ) {
            throw new \Exception ( 'Não há email de destino definido em RW_Mail');
        }

        // Verifica o nome do destinatário
        if ( empty($toName) ) {
            $toName = $toEmail;
        }

        // Verifica o nome do remetente
        if ( empty($replyName) ) {
            $replyName = $this->_name;
        }

        // Verifica o email de resposta do remetente
        if ( empty($replyEmail) ) {
            $replyEmail = $this->_email;
        }

        // Cria o Zend_Mail
        $oMessage = new Message();

        // Nome e Email do Remetente
        $oMessage->setFrom($replyEmail, $replyName);

        // Nome e Email do Destinatario
        if (is_array($toEmail)) {
            foreach ($toEmail as $e=>$n) {
                if (is_numeric($e) && self::isEmail($n)) {
                    $oMessage->addTo($n);
                } elseif ($n != $e) {
                    $oMessage->addTo($e, $n);
                } else {
                    $oMessage->addTo($e);
                }
            }
        } else {
            if ($toName != $toEmail) {
                $oMessage->addTo($toEmail, $toName);
            } else {
                $oMessage->addTo($toEmail);
            }
        }

        // Resposta do email
        if ($replyEmail != $this->_email) {
            $oMessage->setReplyTo($replyEmail);
        }

        // E-mail com cópia oculta
        if ( is_array($opt) && isset($opt['bcc']) ) {
            $bcc = $opt['bcc'];
            if ( is_string($bcc) ) {
                $oMessage->addBcc($bcc);
            } else {
                foreach ($bcc as $email) {
                    $oMessage->addBcc($email);
                }
            }
        }

        // Email com cópia
        if ( is_array($opt) && isset($opt['cc']) ) {
            $cc = $opt['cc'];
            if ( is_string($cc) ) {
                $oMessage->addCc($cc);
            } else {
                foreach ($cc as $name=>$email) {
                    $oMessage->addCc($email, $name);
                }
            }
        }

        // Assunto do E-mail
        $oMessage->setSubject($subject);

        // Verifica se há headers para serem adicionados ao email
        if ( is_array($opt) && isset($opt['headers']) &&  is_array($opt['headers'])) {
            foreach ($opt['headers'] as $h=>$v) {
                $oMessage->getHeaders()->addHeader(new GenericHeader($h, $v));
            }
        }

        // Cria a mensagem
        $msgText = null;
        $msgHtml = null;
        if (is_string($message) && !isset($opt['html']) ) {
            $msgText = $message;
        } elseif (is_string($message) && isset($opt['html']) ) {
            $msgHtml = $message;
            $html = new \Zend\Mime\Part($msgHtml);
            $html->type = 'text/html';
            $body = new \Zend\Mime\Message();
            $body->setParts([$html]);
            $oMessage->setBody($body);
        } elseif ( is_array($message) ) {
            if ( isset($message['text']) ) {
                $msgText = $message['text'];
            }
            if ( isset($message['html']) ) {
                $msgHtml = $message['html'];
            }
        }

        // Cria o TXT a partir do HTML
        if (is_null($msgText) && !is_null($msgHtml)) {
            $msgText = $this->_extractText($msgHtml);
        }

        if ( !is_null($msgText) ) {
            $html = new \Zend\Mime\Part($msgText);
            $html->type = 'text/html';
            $body = new \Zend\Mime\Message();
            $body->setParts([$html]);
            $oMessage->setBody($body);
        }
        if ( !is_null($msgHtml) ) {
            $html = new \Zend\Mime\Part($msgHtml);
            $html->type = 'text/html';
            $body = new \Zend\Mime\Message();
            $body->setParts([$html]);
            $oMessage->setBody($body);
        }
        if ( is_null($msgText) && is_null($msgHtml) ) {
            die("Não foi possível definir o corpo da mensagem.");
        }

        // Verifica se tem anexos
        if (is_array($opt) && isset($opt['anexos']) && is_array($opt['anexos']) ) {

            // Pega as partes antigas definidas acima
            $mimeMessage = $oMessage->getBody();
            if (is_string($mimeMessage)) {
                $originalBodyPart = new \Zend\Mime\Part($mimeMessage);
                $isHtml = $mimeMessage !== strip_tags($mimeMessage);
                $originalBodyPart->type = $isHtml ? \Zend\Mime\Mime::TYPE_HTML : \Zend\Mime\Mime::TYPE_TEXT;

                $oMessage->setBody($originalBodyPart);
                $mimeMessage = $oMessage->getBody();
            }
            $oldParts = $mimeMessage->getParts();

            //cria o array de anexos
            $attachmentParts = [];
            $info = null;
            foreach ($opt['anexos'] as $filename=>$f) {
                $encodingAndDispositionAreSet = false;

                // Verifica se o anexo já é do formato Part e só adiciona
                if ($f instanceof \Zend\Mime\Part) {
                    if (is_null($f->disposition)) {
                        $f->disposition = \Zend\Mime\Mime::DISPOSITION_INLINE;
                    }
                    $part = $f;
                    $encodingAndDispositionAreSet = true;

                // Verifica se foi passado o path completo do arquivo e se o arquivo existe
                } elseif (is_string($f) && is_file($f)) {

                    //pega as infos do arquivo
                    $info = $info !== null ? $info : new \finfo(FILEINFO_MIME_TYPE);
                    //pega o nome do arquivo
                    $filename = is_string($filename) ? $filename : basename($f);
                    //add o arquivo
                    $part = new \Zend\Mime\Part(fopen($f, 'r+b'));
                    $part->type = $info->file($f);

                //vefifica se é um resource
                #todo verificar pois não está enviando certo o anexo no email
                } elseif (is_resource($f)) {
                    $part = new \Zend\Mime\Part($f);

                //verifica se veio como array (ex: quando se faz o upload)
                } elseif (is_array($f)) {
                    $part = new \Zend\Mime\Part();
                    $encodingAndDispositionAreSet = true;
                    // seta o tipo
                    $f = ArrayUtils::merge([
                        'encoding' => \Zend\Mime\Mime::ENCODING_BASE64,
                        'disposition' => \Zend\Mime\Mime::DISPOSITION_ATTACHMENT,
                    ], $f);
                    //Seta as propriedades
                    foreach ($f as $property => $value) {
                        $method = 'set' . $property;
                        if (method_exists($part, $method)) {
                            $part->{$method}($value);
                        }
                    }
                } else {
                    // Ignora qualquer outro tipo de anexo
                    continue;
                }

                // Define o nome e id do anexo caso tenha
                if (is_string($filename)) {
                    $part->id = $filename;
                    $part->filename = $filename;
                }

                // Verifica se foi setado o encoding e disposition
                if (! $encodingAndDispositionAreSet) {
                    $part->encoding = \Zend\Mime\Mime::ENCODING_BASE64;
                    $part->disposition = \Zend\Mime\Mime::DISPOSITION_ATTACHMENT;
                }

                //adiciona ao array
                $attachmentParts[] = $part;
            }

            //adiciona as partes novas e altigas no corpo do email
            $body = new \Zend\Mime\Message();
            $body->setParts(array_merge($oldParts, $attachmentParts));
            $oMessage->setBody($body);
        }

        //Envia o email
        return $this->getTransport()->send($oMessage);
    }

    /**
     * Remove o encoding UTF-8 para não gerar caracteres inválidos no email
     * @todo não detecta UTF-8 depois de utf8_decode
     *
     * @param array|string  $str Texto a ser corrigido
     * @return array|string
     */
    private function _fixEncoding($str) {
        if ( is_array($str) ) {
            foreach ($str as $key=>$value) {
                $key   = $this->_fixEncoding( $key );
                $value = $this->_fixEncoding($value);
                $str[$key] = $value;
            }
        } elseif ( $this->_check_utf8( $str ) ) {
            $str = utf8_decode( $str );
        }

        return $str;
    }

    /**
     * Verifica se está no padrão UTF-8
     * @todo descobrir pq não funciona mb_check_encoding
     *
     * @param  string $str Texto para indentificar se é UTF8
     * @return boolean
     */
    private function _check_utf8($str)
    {
        $len = strlen($str);
        for($i = 0; $i < $len; $i++){
            $c = ord($str[$i]);
            if ($c > 128) {
                if (($c > 247)) return false;
                elseif ($c > 239) $bytes = 4;
                elseif ($c > 223) $bytes = 3;
                elseif ($c > 191) $bytes = 2;
                else return false;
                if (($i + $bytes) > $len) return false;
                while ($bytes > 1) {
                    $i++;
                    $b = ord($str[$i]);
                    if ($b < 128 || $b > 191) return false;
                    $bytes--;
                }
            }
        }
        return true;
    }

    /**
     * Verifica se é um email válido
     *
     * @uses Zend_Validate
     * @param string $email Email a ser verificado
     * @return boolean
     */
    static function isEmail($email)
    {
        return \Zend\Validator\StaticValidator::execute($email, 'EmailAddress');
    }

    /**
     * Extrai um texto de um HTML com quebras de linhas
     *
     * @param string $html HTML para ser transformado em TXT
     * @return string
     */
    private function _extractText($html)
    {
        $text = str_replace("\n", '', $html);
        $text = str_replace("<br>", "\n", $text);
        $text = str_replace("<br/>", "\n", $text);
        $text = str_replace("<br />", "\n", $text);
        $text = str_replace("<p>", "\n", $text);
        $text = str_replace("</p>", "\n\n", $text);

        return strip_tags($text);
    }


    /**
     * @param \Zend\Mail\Transport\Smtp
     * @return $this
     */
    public function setTransport($transport)
    {
        $this->_transport = $transport;

        return $this;
    }

    /**
     * @return Smtp
     */
    public function getTransport()
    {
        return $this->_transport;
    }
}
