<?php
namespace Littled\Tests\PageContent\Serialized;

use Littled\Exception\ContentValidationException;
use Littled\PageContent\Serialized\SerializedContentValidation;
use Littled\Tests\TestHarness\PageContent\Serialized\SerializedContentValidationChild;
use PHPUnit\Framework\TestCase;


class SerializedContentValidationTest extends TestCase
{
	public SerializedContentValidationChild $obj;

	public function setUp(): void
	{
		$this->obj = new SerializedContentValidationChild();
	}

	public function testAddValidationError()
	{
		self::assertCount(0, $this->obj->validationErrors());
		$this->obj->addValidationError('Test error message.');
		self::assertCount(1, $this->obj->validationErrors());
	}

    public function testUnshiftValidationError()
    {
        $o = new SerializedContentValidation();
        $o->unshiftValidationError('number one');
        self::assertCount(1, $o->validationErrors());

        $o->unshiftValidationError('number two');
        self::assertCount(2, $o->validationErrors());
        self::assertEquals('number two', $o->validationErrors()[0]);

        $o->addValidationError('number three');
        self::assertEquals('number three', $o->validationErrors()[2]);

        $o->unshiftValidationError('number four');
        self::assertCount(4, $o->validationErrors());
        self::assertEquals('number four', $o->validationErrors()[0]);
        self::assertEquals('number two', $o->validationErrors()[1]);
        self::assertEquals('number three', $o->validationErrors()[3]);
    }

	public function testClearValidationErrors()
	{
		// confirm object state calling clearValidationErrors() on object without any errors pushed onto it
		$this->obj->clearValidationErrors();
		$this->assertFalse($this->obj->hasValidationErrors());
		$this->assertCount(0, $this->obj->validationErrors());

		// confirm with existing errors on stack
		$this->obj->addValidationError('This is the first error.');
		$this->obj->addValidationError('This is the second error.');
		$this->assertTrue($this->obj->hasValidationErrors());
		$this->assertCount(2, $this->obj->validationErrors());

		// confirm object state after clearValidationErrors()
		$this->obj->clearValidationErrors();
		$this->assertFalse($this->obj->hasValidationErrors());
		$this->assertCount(0, $this->obj->validationErrors());
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

		self::assertCount(2, $this->obj->validationErrors());
	}

	public function testValidateInput()
	{
		try {
			$this->obj->validateInput();
		}
		catch(ContentValidationException $ex) {
			self::assertEquals('Some required information is missing.', $ex->getMessage());
			self::assertCount(1, $this->obj->validationErrors());
			self::assertEquals('Test varchar value 1 is required.', $this->obj->validationErrors()[0]);
		}
	}
}