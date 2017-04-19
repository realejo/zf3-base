<?php
namespace Realejo\View\Helper;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Zend\Form\Form;
use Zend\I18n\Translator\Translator;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\I18n\Validator\IsInt;
use Zend\I18n\Validator\PhoneNumber;
use Zend\Validator\Between;
use Zend\Validator\CreditCard;
use Zend\Validator\Date;
use Zend\Validator\EmailAddress;
use Zend\Validator\Identical;
use Zend\Validator\Ip;
use Zend\Validator\NotEmpty;
use Zend\Validator\Regex;
use Zend\Validator\StringLength;
use Zend\Validator\Uri;
use Zend\View\Helper\AbstractHelper;

/**
 * View helper plugin to fetch the authenticated identity.
 */
class GetInputFilter extends AbstractHelper
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator = null)
    {
        if (null === $translator) {
            $translator = new Translator();

            // Coloca as mensagens de tradução em Português se existir
            if (file_exists('./vendor/zendframework/zend-i18n-resources/languages/pt_BR/Zend_Validate.php')) {
                // Define o local onde se encontra o arquivo de tradução de mensagens
                $translator->addTranslationFile('phparray', './vendor/zendframework/zend-i18n-resources/languages/pt_BR/Zend_Validate.php');
                // Define o local (você também pode definir diretamente no método acima
                $translator->setLocale('pt_BR');
            }

            $this->translator = $translator;
        } else {
            // Define o translator padrão recebido
            $this->translator = $translator;
        }
    }

    /**
     * Array ou json com as configurações dos inputs do formvalidation
     *
     * @param Form $form  form a ser validado
     * @param boolean $json OPCIONAL retornar json ou array
     *
     * @return array|string        Campos do formulario com regras e mensagens
     */
    private function getFormValidationFields(Form $form, $json = true)
    {
        $result = [];

        foreach ($form->getElements() as $element) {
            $validators = $form->getInputFilter()->get($element->getName())->getValidatorChain()->getValidators();
            foreach ($validators as $validator) {
                switch ($validator['instance']) {
                    case $validator['instance'] instanceof NotEmpty:
                        $messages = $validator['instance']->getMessageTemplates();
                        $result[$element->getName()]['validators']['notEmpty']['message'] = $this->getTranslator()->translate($messages[NotEmpty::IS_EMPTY]);
                        break;
                    #TODO O regex do formvalidation é diferente do php, entao tem que trocar na mão la na view
                    case $validator['instance'] instanceof Regex:
                        $messages = $validator['instance']->getMessageTemplates();
                        $result[$element->getName()]['validators']['regexp']['message'] = $this->getTranslator()->translate($messages[Regex::NOT_MATCH]);
                        $result[$element->getName()]['validators']['regexp']['message'] = str_replace('%pattern%', $validator['instance']->getPattern(), $result[$element->getName()]['validators']['regexp']['message']);
                        $result[$element->getName()]['validators']['regexp']['regexp'] = $validator['instance']->getPattern();
                        break;
                    case $validator['instance'] instanceof StringLength:
                        $messages = $validator['instance']->getMessageTemplates();
                        #fixado mensagem na mão pois o ZendForm não possui a mensagem junta, somente msg de min e max distintas.
                        $result[$element->getName()]['validators']['stringLength']['message'] = "O tamanho do valor de entrada deve conter entre {$validator['instance']->getMin()} e {$validator['instance']->getMax()} caracteres";
                        $result[$element->getName()]['validators']['stringLength']['max'] = $validator['instance']->getMax();
                        $result[$element->getName()]['validators']['stringLength']['min'] = $validator['instance']->getMin();
                        break;
                    case $validator['instance'] instanceof Date:
                        $messages = $validator['instance']->getMessageTemplates();
                        $result[$element->getName()]['validators']['date']['message'] = $this->getTranslator()->translate($messages[Date::FALSEFORMAT]);
                        $result[$element->getName()]['validators']['date']['message'] = str_replace('%format%', $validator['instance']->getFormat(), $result[$element->getName()]['validators']['date']['message']);
                        if ($validator['instance']->getFormat() == 'd/m/Y') {
                            $result[$element->getName()]['validators']['date']['format'] = 'DD/MM/YYYY';
                        } elseif ($validator['instance']->getFormat() == 'd/m/Y H:i:s') {
                            $result[$element->getName()]['validators']['date']['format'] = 'DD/MM/YYYY h:m:s';
                        } elseif ($validator['instance']->getFormat() == 'd/m/Y H:i') {
                            $result[$element->getName()]['validators']['date']['format'] = 'DD/MM/YYYY h:m';
                        } elseif ($validator['instance']->getFormat() == 'Y-m-d') {
                            $result[$element->getName()]['validators']['date']['format'] = 'YYYY/MM/DD';
                        } elseif ($validator['instance']->getFormat() == 'Y-m-d H:i:s') {
                            $result[$element->getName()]['validators']['date']['format'] = 'YYYY/MM/DD h:m:s';
                        } else {
                            throw new \RuntimeException('Não foi possível mapear o formato de data '.$validator['instance']->getFormat(). ' para o JS');
                        }
                        break;
                    case $validator['instance'] instanceof EmailAddress:
                        $messages = $validator['instance']->getMessageTemplates();
                        $result[$element->getName()]['validators']['emailAddress']['message'] = $this->getTranslator()->translate($messages[EmailAddress::INVALID_FORMAT]);
                        break;
                    case $validator['instance'] instanceof PhoneNumber:
                        $messages = $validator['instance']->getMessageTemplates();
                        $result[$element->getName()]['validators']['phone']['message'] = $this->getTranslator()->translate($messages[PhoneNumber::NO_MATCH]);
                        $result[$element->getName()]['validators']['phone']['country'] = $validator['instance']->getCountry();
                        break;
                    case $validator['instance'] instanceof Between:
                        $messages = $validator['instance']->getMessageTemplates();
                        $result[$element->getName()]['validators']['between']['message'] = str_replace('%max%', $validator['instance']->getMax(), str_replace('%min%', $validator['instance']->getMin(), $this->getTranslator()->translate($messages[Between::NOT_BETWEEN])));
                        $result[$element->getName()]['validators']['between']['max'] = $validator['instance']->getMax();
                        $result[$element->getName()]['validators']['between']['min'] = $validator['instance']->getMin();
                        break;
                    case $validator['instance'] instanceof Ip:
                        $messages = $validator['instance']->getMessageTemplates();
                        $result[$element->getName()]['validators']['ip']['message'] = $this->getTranslator()->translate($messages[Ip::NOT_IP_ADDRESS]);
                        break;
                    case $validator['instance'] instanceof Uri:
                        $messages = $validator['instance']->getMessageTemplates();
                        $result[$element->getName()]['validators']['uri']['message'] = $this->getTranslator()->translate($messages[Uri::NOT_URI]);
                        break;
                    case $validator['instance'] instanceof CreditCard:
                        $result[$element->getName()]['validators']['creditCard']['message'] = 'Número do cartão inválido';
                        break;
                    case $validator['instance'] instanceof IsInt:
                        $messages = $validator['instance']->getMessageTemplates();
                        $result[$element->getName()]['validators']['integer']['message'] = $this->getTranslator()->translate($messages[IsInt::NOT_INT]);
                        break;
                    case $validator['instance'] instanceof Identical:
                        $messages = $validator['instance']->getMessageTemplates();
                        $result[$element->getName()]['validators']['identical']['message'] = $this->getTranslator()->translate($messages[Identical::NOT_SAME]);
                        $result[$element->getName()]['validators']['identical']['field'] = $validator['instance']->getToken();
                        break;
                }
            }
        }

        return $json == false ? $result : json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * @param Form $form
     * @return array
     */
    public function getFormValidationFieldsArray(Form $form)
    {
        return $this->getFormValidationFields($form, false);
    }

    /**
     * @param Form $form
     * @return string
     */
    public function getFormValidationFieldsJSON(Form $form)
    {
        return $this->getFormValidationFields($form);
    }

    /**
     * @param Form $form
     * @param string $target Id do form
     * @param bool $useTooltip
     * @return string
     */
    public function getFormValidationJS(Form $form, $target, $useTooltip = false)
    {
        if (empty($form)) {
            throw new \InvalidArgumentException('Form não pode estar vazio em '.get_class($this).'::getFormValidationJS()');
        }

        if (empty($target)) {
            throw new \InvalidArgumentException('Target não pode estar vazio em '.get_class($this).'::getFormValidationJS()');
        }

        $script = "$('#$target').formValidation({
                    framework: 'bootstrap',";

        if ($useTooltip) {
            $script .= "err: {
                        container: 'tooltip'
                    },";
        }

        $script .= "icon: {
                        valid: 'glyphicon glyphicon-ok',
                        invalid: 'glyphicon glyphicon-remove',
                        validating: 'glyphicon glyphicon-refresh'
                    },
                    fields:" . $this->getFormValidationFieldsJSON($form) . "
                });";

        return $script;
    }
        /**
     * @return Translator
     */
    public function getTranslator()
    {
        return $this->translator;
    }
}
