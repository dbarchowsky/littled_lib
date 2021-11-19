<?php
namespace Littled\Tests\PageContent;

use Littled\Exception\ContentValidationException;
use Littled\PageContent\Serialized\SerializedContentValidation;
use Littled\Request\BooleanInput;
use Littled\Request\IntegerInput;
use Littled\Request\StringInput;
use PHPUnit\Framework\TestCase;

/**
 * Class SerializedContentUtilsChild
 * @package Littled\Tests\PageContent
 */
class SerializedContentValidationChild extends SerializedContentValidation
{
	public $vc_col1;
	public $vc_col2;
	public $int_col;
	public $bool_col;

	/**
	 * SerializedContentUtilsChild constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->vc_col1 = new StringInput('Test varchar value 1', 'p_vc1', true, '', 50);
		$this->vc_col2 = new StringInput('Test varchar value 1', 'p_vc2', false, '', 255);
		$this->int_col = new IntegerInput('Test int value', 'p_int');
		$this->bool_col = new BooleanInput('Test bool value', 'p_bool');
	}
}

class SerializedContentValidationTest extends TestCase
{
	/** @var SerializedContentValidationChild Test object. */
	public $obj;

	public function setUp(): void
	{
		$this->obj = new SerializedContentValidationChild();
	}

	public function testAddValidationError()
	{
		self::assertEquals(0, count($this->obj->validationErrors));
		$this->obj->addValidationError('Test error message.');
		self::assertEquals(1, count($this->obj->validationErrors));
	}

	public function testGetErrorsString()
	{
		$test_error_1 = 'error one';
		$test_error_2 = 'error two';
		$test_delimiter = ':';

		// test the default value with no errors
		$this->assertEquals('', $this->obj->getErrorsString());

		// test with a single error on the stack
		$this->obj->addValidationError($test_error_1);
		$this->assertEquals($test_error_1, $this->obj->getErrorsString());

		// test with multiple errors on stack
		$this->obj->addValidationError($test_error_2);
		$this->assertEquals("$test_error_1 \n$test_error_2", $this->obj->getErrorsString());

		// test with non-default delimiter
		$this->assertEquals("$test_error_1$test_delimiter$test_error_2", $this->obj->getErrorsString($test_delimiter));
	}

    public function testHasData()
    {
    	self::assertFalse($this->obj->hasData());

		$this->obj->vc_col1->required = true;
		$this->obj->vc_col2->required = false;
		$this->obj->vc_col2->value = 'foo';
	    self::assertFalse($this->obj->hasData());

	    $this->obj->vc_col1->value = 'bar';
	    self::assertTrue($this->obj->hasData());

	    $this->obj->vc_col1->required = false;
	    self::assertFalse($this->obj->hasData());
    }

	public function testHasValidationErrors()
	{
		self::assertFalse($this->obj->hasValidationErrors());

		$this->obj->addValidationError('Test validation error');
		self::assertTrue($this->obj->hasValidationErrors());

		$this->obj->addValidationError('2nd test validation error');
		self::assertTrue($this->obj->hasValidationErrors());

		self::assertEquals(2, count($this->obj->validationErrors));
	}

	public function testValidateInput()
	{
		try {
			$this->obj->validateInput();
		}
		catch(ContentValidationException $ex) {
			self::assertEquals('Some required information is missing.', $ex->getMessage());
			self::assertEquals(1, count($this->obj->validationErrors));
			self::assertEquals('Test varchar value 1 is required.', $this->obj->validationErrors[0]);
		}
	}
}