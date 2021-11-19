<?php
namespace Littled\Tests\Request;


use Littled\Request\StringTextField;
use PHPUnit\Framework\TestCase;
use Littled\Database\MySQLConnection;
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

	public function setUp() : void
	{
		$this->obj = new StringTextField("Test date", 'p_date');
	}

	public function testTemplateFilename()
	{
		$new_filename = 'new-string-template.php';
		$default = $this->obj::getTemplateFilename();

		// make sure the new value is different from the default
		$this->assertNotEquals($new_filename, $default);

		// test the object property after it has been set to a new value
		StringTextField::setTemplateFilename('new-string-template.php');
		$this->assertNotEquals($default, $this->obj::getTemplateFilename());
		$this->assertEquals(StringTextField::getTemplateFilename(), $this->obj::getTemplateFilename());

		// parent class's template value should remain unchanged
		$this->assertNotEquals(RequestInput::getTemplateFilename(), $this->obj::getTemplateFilename());
		$this->assertNotEquals(StringInput::getTemplateFilename(), $this->obj::getTemplateFilename());
	}
}