<?php

namespace RealejoTest\View\Helper;

/**
 * Version test case.
 */
use PHPUnit\Framework\TestCase;
use Realejo\View\Helper\GetInputFilter;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\I18n\Translator\Translator;
use Zend\I18n\Validator\IsInt;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Between;
use Zend\Validator\NotEmpty;

class GetInputFilterTest extends TestCase
{
    public function testConstruct()
    {
        $helper = new GetInputFilter();
        $this->assertInstanceOf('Realejo\View\Helper\GetInputFilter', $helper);
        $this->assertInstanceOf('Zend\I18n\Translator\Translator', $helper->getTranslator());
        $this->assertEquals('pt_BR', $helper->getTranslator()->getLocale());
    }

    public function testConstructIngles()
    {
        $translator = new Translator();
        $helper = new GetInputFilter($translator);
        $this->assertEquals('en_US', $helper->getTranslator()->getLocale());
    }

    public function testGetFormValidationFieldsArray()
    {
        // Cria um form
        $form = new Form();

        // Adiciona um campo texto
        $form->add([
            'name' => 'campo1',
            'type' => Text::class
        ]);

        // Cria um input filter
        $inputFilter = new InputFilter();

        // Adiciona validação [obrigatório, inteiro, não vazio, entre 1 e 3]
        $inputFilter->add([
            'name'       => 'campo1',
            'required'   => true,
            'filters'    => [],
            'validators' => [
                ['name' => IsInt::class],
                ['name' => NotEmpty::class],
                [
                    'name' => Between::class,
                    'options' => [
                        'min' => 1,
                        'max' => 3
                    ]
                ],
            ],
        ]);

        $form->setInputFilter($inputFilter);

        $helper = new GetInputFilter();

        $fields = $helper->getFormValidationFieldsArray($form);
        $this->assertTrue(is_array($fields));
        $this->assertNotEmpty($fields);
        $this->assertCount(1, $fields);
        $this->assertArrayHasKey('campo1', $fields);
        $this->assertArrayHasKey('validators', $fields['campo1']);
        $this->assertCount(3, $fields['campo1']['validators']);
        $this->assertArraySubset(['integer', 'notEmpty', 'between'], array_keys($fields['campo1']['validators']));

        $this->assertArrayHasKey('message', $fields['campo1']['validators']['integer']);
        $this->assertArrayHasKey('message', $fields['campo1']['validators']['notEmpty']);

        $this->assertArrayHasKey('message', $fields['campo1']['validators']['between']);
        $this->assertArrayHasKey('min', $fields['campo1']['validators']['between']);
        $this->assertArrayHasKey('max', $fields['campo1']['validators']['between']);
    }

    public function testGetFormValidationFieldsJson()
    {
        // Cria um form
        $form = new Form();

        // Adiciona um campo texto
        $form->add([
            'name' => 'campo1',
            'type' => Text::class
        ]);

        // Cria um input filter
        $inputFilter = new InputFilter();

        // Adiciona validação [obrigatório, inteiro, não vazio, entre 1 e 3]
        $inputFilter->add([
            'name'       => 'campo1',
            'required'   => true,
            'filters'    => [],
            'validators' => [
                ['name' => IsInt::class],
                ['name' => NotEmpty::class],
                [
                    'name' => Between::class,
                    'options' => [
                        'min' => 1,
                        'max' => 3
                    ]
                ],
            ],
        ]);

        $form->setInputFilter($inputFilter);

        $helper = new GetInputFilter();
        $json = $helper->getFormValidationFieldsJSON($form);
        $this->assertTrue(is_string($json));

        $fields = json_decode($json, true);
        $this->assertTrue(is_array($fields));

        $this->assertNotEmpty($fields);
        $this->assertCount(1, $fields);
        $this->assertArrayHasKey('campo1', $fields);
        $this->assertArrayHasKey('validators', $fields['campo1']);
        $this->assertCount(3, $fields['campo1']['validators']);
        $this->assertArraySubset(['integer', 'notEmpty', 'between'], array_keys($fields['campo1']['validators']));

        $this->assertArrayHasKey('message', $fields['campo1']['validators']['integer']);
        $this->assertArrayHasKey('message', $fields['campo1']['validators']['notEmpty']);

        $this->assertArrayHasKey('message', $fields['campo1']['validators']['between']);
        $this->assertArrayHasKey('min', $fields['campo1']['validators']['between']);
        $this->assertArrayHasKey('max', $fields['campo1']['validators']['between']);
    }
}
