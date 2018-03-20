<?php
/**
 * Classe para envio de emails
 *
 * @link      http://github.com/realejo/libraray-zf2
 * @copyright Copyright (c) 2014 Realejo (http://realejo.com.br)
 * @license   http://unlicense.org
 */
namespace Realejo\Utils;

use Zend\Stdlib\ArrayUtils;
use Zend\Mail;
use Zend\Mime;

class MailSender
{

    /**
     * Nome padrão para os emails enviados pelo site.
     * @var string
     */
    private $senderName;

    /**
     * Email padrão utilizado no site
     * @var string
     */
    private $senderEmail;

    /**
     * @var Mail\Transport\TransportInterface
     */
    private $transport;

    /**
     * Configurações de SMTP
     * @var string
     */
    private $smtpUsername;

    /**
     *  @var string
     */
    private $smtpPassword;

    /**
     * @var string
     */
    private $smtpHost;

    /**
     * @var int
     */
    private $smtpPort = 25;

    /**
     * @var Mail\Message
     */
    private $message;

    /**
     * Mail constructor.
     * @param null|array $config
     * @param bool $isException
     */
    public function __construct($config = null, $isException = false)
    {
        if (empty($config)) {
            if (defined('APPLICATION_PATH')) {
                $config = include APPLICATION_PATH . "/config/autoload/email_config.php";
            } else {
                throw new \RuntimeException('Error loading email configuration in '.get_class($this).'::__construct()');
            }
        }

        $this->senderName  = $config['name'];
        $this->senderEmail = $config['email'];
        $this->smtpHost    = $config['host'];
        $this->smtpPort    = $config['port'];

        $this->smtpUsername   = isset($config['username']) ? $config['username'] : '';
        $this->smtpPassword   = isset($config['password']) ? $config['password'] : '';

        $smtpConfig = [
            'name'     => $this->smtpHost,
            'host'     => $this->smtpHost,
            'port'     => $this->smtpPort,
            'connection_class' => 'login',
            'connection_config' => [
                'username' => $this->smtpUsername,
                'password' => $this->smtpPassword,
                'ssl' => 'tls',
            ],
        ];

        // Verifica se há SSL
        if (isset($config['ssl']) && $config['ssl'] != '') {
            $smtpConfig['connection_config']['ssl'] = $config['ssl'];
        }

        // verifica se há uma porta definida
        if (isset($config['port']) &&  $config['port'] != '') {
            $smtpConfig['port'] = $config['port'];
        }

        // Configura o transport
        $this->setTransport(new Mail\Transport\Smtp(new Mail\Transport\SmtpOptions($smtpConfig)));
    }

    /**
     * @param $replyName
     * @param $replyEmail
     * @param $toName
     * @param $toEmail
     * @param $subject
     * @param $message
     * @param array $opt
     * @return bool
     */
    public function sendEmail($replyName = null, $replyEmail = null, $toName = null, $toEmail, $subject, $message, $opt = [])
    {
        $this->setEmailMessage($replyName, $replyEmail, $toName, $toEmail, $subject, $message, $opt);
        $this->getTransport()->send($this->getMessage());
        return true;
    }

    public function send()
    {
        return $this->getTransport()->send($this->getMessage());
    }


    /**
     * @param null|string  $replyName
     * @param null|string  $replyEmail
     * @param null|string  $toName
     * @param string $toEmail
     * @param string $subject
     * @param string $message
     * @param array $opt
     * @return Mail\Message
     */
    public function setEmailMessage($replyName = null, $replyEmail = null, $toName = null, $toEmail, $subject, $message, $opt = [])
    {
        // Verifica a codificação
        $replyName  = $this->fixEncoding($replyName);
        $replyEmail = $this->fixEncoding($replyEmail);
        $toName     = $this->fixEncoding($toName);
        $toEmail    = $this->fixEncoding($toEmail);
        $subject    = $this->fixEncoding($subject);
        $message    = $this->fixEncoding($message);

        // Verifica o email do destinatário
        if (empty($toEmail)) {
            throw new \InvalidArgumentException('Não há email de destino definido em '.get_class($this).'::setMailMessage()');
        }

        // Verifica o nome do destinatário
        if (empty($toName)) {
            $toName = $toEmail;
        }

        // Verifica o nome do remetente
        if (empty($replyName)) {
            $replyName = $this->fixEncoding($this->senderName);
        }

        // Verifica o email de resposta do remetente
        if (empty($replyEmail)) {
            $replyEmail = $this->fixEncoding($this->senderEmail);
        }

        // Cria o Zend_Mail
        $oMessage = new Mail\Message();

        // Nome e Email do Remetente
        $oMessage->setFrom($replyEmail, $replyName);

        // Nome e Email do Destinatario
        if (is_array($toEmail)) {
            foreach ($toEmail as $e => $n) {
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
        if ($replyEmail != $this->senderEmail) {
            $oMessage->setReplyTo($replyEmail);
        }

        // E-mail com cópia oculta
        if (is_array($opt) && isset($opt['bcc'])) {
            $bcc = $opt['bcc'];
            if (is_string($bcc)) {
                $oMessage->addBcc($bcc);
            } else {
                foreach ($bcc as $email) {
                    $oMessage->addBcc($email);
                }
            }
        }

        // Email com cópia
        if (is_array($opt) && isset($opt['cc'])) {
            $cc = $opt['cc'];
            if (is_string($cc)) {
                $oMessage->addCc($cc);
            } else {
                foreach ($cc as $name => $email) {
                    $oMessage->addCc($email, $name);
                }
            }
        }

        // Assunto do E-mail
        $oMessage->setSubject($subject);

        // Verifica se há headers para serem adicionados ao email
        if (is_array($opt) && isset($opt['headers']) &&  is_array($opt['headers'])) {
            foreach ($opt['headers'] as $h => $v) {
                $oMessage->getHeaders()->addHeader(new Mail\Header\GenericHeader($h, $v));
            }
        }

        // Cria a mensagem
        $msgText = null;
        $msgHtml = null;
        if (is_string($message) && ! isset($opt['html'])) {
            $msgText = $message;
        } elseif (is_string($message) && isset($opt['html'])) {
            $msgHtml = $message;
            $html = new Mime\Part($msgHtml);
            $html->type = 'text/html';
            $body = new Mime\Message();
            $body->setParts([$html]);
            $oMessage->setBody($body);
        } elseif (is_array($message)) {
            if (isset($message['text'])) {
                $msgText = $message['text'];
            }
            if (isset($message['html'])) {
                $msgHtml = $message['html'];
            }
        }

        // Cria o TXT a partir do HTML
        if (is_null($msgText) && ! is_null($msgHtml)) {
            $msgText = $this->extractText($msgHtml);
        }

        if (! is_null($msgText)) {
            $html = new Mime\Part($msgText);
            $html->type = 'text/html';
            $body = new Mime\Message();
            $body->setParts([$html]);
            $oMessage->setBody($body);
        }
        if (! is_null($msgHtml)) {
            $html = new Mime\Part($msgHtml);
            $html->type = 'text/html';
            $body = new Mime\Message();
            $body->setParts([$html]);
            $oMessage->setBody($body);
        }
        if (is_null($msgText) && is_null($msgHtml)) {
            throw new \InvalidArgumentException("Could not define message body");
        }

        // Verifica se tem anexos
        if (is_array($opt) && isset($opt['anexos']) && is_array($opt['anexos'])) {
            // Pega as partes antigas definidas acima
            $mimeMessage = $oMessage->getBody();
            if (is_string($mimeMessage)) {
                $originalBodyPart = new Mime\Part($mimeMessage);
                $isHtml = $mimeMessage !== strip_tags($mimeMessage);
                $originalBodyPart->type = $isHtml ? Mime\Mime::TYPE_HTML : Mime\Mime::TYPE_TEXT;

                $oMessage->setBody($originalBodyPart);
                $mimeMessage = $oMessage->getBody();
            }
            $oldParts = $mimeMessage->getParts();

            //cria o array de anexos
            $attachmentParts = [];
            $info = null;
            foreach ($opt['anexos'] as $filename => $f) {
                $encodingAndDispositionAreSet = false;

                // Verifica se o anexo já é do formato Part e só adiciona
                if ($f instanceof Mime\Part) {
                    if (is_null($f->disposition)) {
                        $f->disposition = Mime\Mime::DISPOSITION_INLINE;
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
                    $part = new Mime\Part(fopen($f, 'r+b'));
                    $part->type = $info->file($f);

                    //verifica se é um resource
                    #todo verificar pois não está enviando certo o anexo no email
                } elseif (is_resource($f)) {
                    $part = new Mime\Part($f);

                    //verifica se veio como array (ex: quando se faz o upload)
                } elseif (is_array($f)) {
                    $part = new Mime\Part();
                    $encodingAndDispositionAreSet = true;
                    // seta o tipo
                    $f = ArrayUtils::merge([
                        'encoding' => Mime\Mime::ENCODING_BASE64,
                        'disposition' => Mime\Mime::DISPOSITION_ATTACHMENT,
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
                    $part->encoding = Mime\Mime::ENCODING_BASE64;
                    $part->disposition = Mime\Mime::DISPOSITION_ATTACHMENT;
                }

                //adiciona ao array
                $attachmentParts[] = $part;
            }

            //adiciona as partes novas e antigas no corpo do email
            $body = new Mime\Message();
            $body->setParts(array_merge($oldParts, $attachmentParts));
            $oMessage->setBody($body);
        }

        $this->message = $oMessage;

        return $this->message;
    }

    /**
     * Remove o encoding UTF-8 para não gerar caracteres inválidos no email
     * @todo não detecta UTF-8 depois de utf8_decode
     *
     * @param array|string  $str Texto a ser corrigido
     * @return array|string
     */
    private function fixEncoding($str)
    {
        if (is_array($str)) {
            foreach ($str as $key => $value) {
                $key   = $this->fixEncoding($key);
                $value = $this->fixEncoding($value);
                $str[$key] = $value;
            }
        } elseif ($this->checkUTF8($str)) {
            $str = mb_convert_encoding($str, 'UTF-8');
        }

        return $str;
    }

    /**
     * Verifica se está no padrão UTF-8
     * @todo descobrir pq não funciona mb_check_encoding
     *
     * @param  string $str Texto para identificar se é UTF8
     * @return boolean
     */
    private function checkUTF8($str)
    {
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $c = ord($str[$i]);
            if ($c > 128) {
                if (($c > 247)) {
                    return false;
                } elseif ($c > 239) {
                    $bytes = 4;
                } elseif ($c > 223) {
                    $bytes = 3;
                } elseif ($c > 191) {
                    $bytes = 2;
                } else {
                    return false;
                }
                if (($i + $bytes) > $len) {
                    return false;
                }
                while ($bytes > 1) {
                    $i++;
                    $b = ord($str[$i]);
                    if ($b < 128 || $b > 191) {
                        return false;
                    }
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
    public static function isEmail($email)
    {
        return \Zend\Validator\StaticValidator::execute($email, 'EmailAddress');
    }

    /**
     * Remove o final do sender substituindo por *
     *
     * Ex: contato@realejo.com.br => co*****@realejo.com.br
     *
     * @param string $email
     * @return bool|string
     */
    public static function maskEmail($email)
    {
        if (! empty($email)) {
            $explode = explode('@', $email);
            $email = substr($explode[0], 0, 2);
            $email .= str_repeat('*', strlen($explode[0]) - 2);
            $email .= '@' . $explode[1];
        }

        return $email;
    }

    /**
     * Extrai um texto de um HTML com quebras de linhas
     *
     * @param string $html HTML para ser transformado em TXT
     * @return string
     */
    private function extractText($html)
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
     * @param Mail\Transport\TransportInterface
     * @return $this
     */
    public function setTransport($transport)
    {
        $this->transport = $transport;

        return $this;
    }

    /**
     * @return Mail\Transport\TransportInterface
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @return Mail\Message
     */
    public function getMessage()
    {
        return $this->message;
    }
}
