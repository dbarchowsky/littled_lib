<?php
namespace Littled\Tests\Request;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\Request\RequestInput;
use Littled\Request\BooleanCheckbox;
use Littled\Tests\Request\DataProvider\BooleanInputTestData;
use PHPUnit\Framework\TestCase;

class BooleanCheckboxTest extends TestCase
{
    const DEFAULT_TEMPLATE_FILENAME = 'boolean-checkbox-field.php';

    function setUp(): void
    {
        parent::setUp();
    }
	public function __construct(?string $name = null, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		BooleanCheckbox::setTemplateBasePath(LITTLED_TEMPLATE_DIR.'forms/input-elements/');
		BooleanCheckbox::setTemplateFilename('boolean-checkbox-field.php');
	}

	/**
	 * @dataProvider \Littled\Tests\Request\DataProvider\BooleanInputTestDataProvider::saveInFormProvider()
	 * @param BooleanInputTestData $data
	 * @return void
	 */
    public function testSaveInForm(BooleanInputTestData $data)
    {
	    $o = new BooleanCheckbox(BooleanInputTestData::DEFAULT_LABEL, BooleanInputTestData::DEFAULT_KEY);
	    $o->setInputValue($data->value);
        $this->expectOutputRegex($data->expected_regex);
        $o->saveInForm();
    }

    /**
     * @dataProvider \Littled\Tests\Request\DataProvider\BooleanInputTestDataProvider::renderTestProvider()
     * @param BooleanInputTestData $data
     * @return void
     */
    function testRender(BooleanInputTestData $data)
    {
        ob_start();
        $data->obj->render($data->label_override, $data->class_override);
        $markup = ob_get_contents();
        ob_end_clean();
        $this->assertMatchesRegularExpression($data->expected_regex, $markup, $data->msg);
    }

	public function testTemplatePath()
	{
		$original = BooleanCheckbox::getTemplateFilename();
		BooleanCheckbox::setTemplateFilename('special-bool-template.php');
		$this->assertEquals(RequestInput::getTemplateBasePath().BooleanCheckbox::getTemplateFilename(), BooleanCheckbox::getTemplatePath());
		$this->assertNotEquals(RequestInput::getTemplateBasePath().BooleanCheckbox::getTemplateFilename(), RequestInput::getTemplatePath());
		BooleanCheckbox::setTemplateFilename($original);
	}
}
