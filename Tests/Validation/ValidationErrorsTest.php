<?php
namespace Littled\Tests\Validation;

use Littled\Validation\ValidationErrors;
use PHPUnit\Framework\TestCase;

class ValidationErrorsTest extends TestCase
{
    function testClear()
    {
        $o = new ValidationErrors();
        $o->clear();
        $this->assertCount(0, $o->getList());

        $o->push('an error');
        $o->push('another error');
        $this->assertCount(2, $o->getList());

        $o->clear();
        $this->assertCount(0, $o->getList());
    }

    function testGetErrorsString()
    {
        $o = new ValidationErrors();

        $o->push('an error');
        $this->assertEquals("an error", $o->getErrorsString());
        $this->assertEquals("an error", $o->getErrorsString('##'));

        $o->push('another error');
        $this->assertEquals("an error \nanother error", $o->getErrorsString());
        $this->assertEquals("an error##another error", $o->getErrorsString('##'));
    }

    function testHasErrors()
    {
        $o = new ValidationErrors();
        $this->assertFalse($o->hasErrors());

        $o->push('an error');
        $this->assertTrue($o->hasErrors());

        $o->push('another error');
        $this->assertTrue($o->hasErrors());
    }

    function testPush()
    {
        $o = new ValidationErrors();
        $this->assertCount(0, $o->getList());

        $o->push('first error');
        $this->assertCount(1, $o->getList());

        $o->push('second error');
        $this->assertCount(2, $o->getList());

        $errors = $o->getList();
        $this->assertEquals('first error', $errors[0]);
        $this->assertEquals('second error', $errors[1]);

        $o->push(array('another error', 'the last error'));
        $errors = $o->getList();
        $this->assertCount(4, $errors);
        $this->assertEquals('the last error', $errors[3]);
    }

    function testUnshift()
    {
        $o = new ValidationErrors();
        $this->assertCount(0, $o->getList());

        $o->push('the last error');
        $this->assertCount(1, $o->getList());

        $o->unshift('second to last error');
        $this->assertCount(2, $o->getList());

        $errors = $o->getList();
        $this->assertEquals('second to last error', $errors[0]);
        $this->assertEquals('the last error', $errors[1]);

        $o->unshift(array('the first error', 'second error'));
        $errors = $o->getList();
        $this->assertCount(4, $errors);
        $this->assertEquals('second error', $errors[1]);
        $this->assertEquals('the last error', $errors[3]);
    }
}