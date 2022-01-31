<?php
namespace Littled\Tests\Request;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\Request\StringTextField;
use PHPUnit\Framework\TestCase;
use Littled\Request\RequestInput;
use Littled\Request\StringInput;


/**
 * Class StringInputTest
 * @package Littled\Tests\Request
 */
class StringTextFieldTest extends TestCase
{
	/** @var StringTextField Test DateInput object. */
	public $obj;

	function __construct()
	{
        parent::__construct();
		$this->obj = new StringTextField("Test date", 'p_date');
	}

    function testSaveInForm()
    {
	    RequestInput::setTemplateBasePath(LITTLED_TEMPLATE_DIR.'forms/input-elements/');
        $this->expectOutputRegex('/<input type=\"hidden\" name=\"'.$this->obj->key.'\"/');
        $this->obj->saveInForm();
    }

	public function testTemplateFilename()
	{
		$new_filename = 'new-string-template.php';
		$default = $this->obj::getTemplateFilename();

		// make sure the new value is different from the default
		$this->assertNotEquals($new_filename, $default);

        // reset these in case they have been modified in the course of running previous unit tests
        RequestInput::setTemplateFilename('hidden-input.php');
        StringInput::setTemplateFilename('text-field-input.php');

		// test the object property after it has been set to a new value
		StringTextField::setTemplateFilename('new-string-template.php');
		$this->assertNotEquals($default, $this->obj::getTemplateFilename());
		$this->assertEquals(StringTextField::getTemplateFilename(), $this->obj::getTemplateFilename());

		// parent class's template value should remain unchanged
		$this->assertNotEquals(RequestInput::getTemplateFilename(), $this->obj::getTemplateFilename());
        $this->assertNotEquals(StringInput::getTemplateFilename(), $this->obj::getTemplateFilename());
	}
}