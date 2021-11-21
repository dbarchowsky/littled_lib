<?php
namespace Littled\Tests\Request;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\Request\RequestInput;
use Littled\Request\BooleanCheckbox;
use PHPUnit\Framework\TestCase;

class BooleanCheckboxTest extends TestCase
{
    const DEFAULT_TEMPLATE_FILENAME = 'boolean-checkbox-field.php';

    function setUp(): void
    {
        parent::setUp();
        RequestInput::setTemplateBasePath(SHARED_CMS_TEMPLATE_DIR);
    }

    /**
     *
     */
    public function testSaveInForm()
    {
        RequestInput::setTemplateFilename('forms/input-elements/hidden-input.php');

        // confirm that the path to the boolean checkbox template is unchanged
        $o = new BooleanCheckbox("Checkbox Test", "boolCBTest");
        $this->assertEquals(self::DEFAULT_TEMPLATE_FILENAME, $o::getTemplateFilename());

        $expected = "<input type=\"hidden\" name=\"$o->key\" value=\"\" />\n";
        $this->expectOutputString($expected);
        $o->saveInForm();

        $o->value = true;
        $expected = $expected."<input type=\"hidden\" name=\"$o->key\" value=\"1\" />\n";
        $this->expectOutputString($expected);
        $o->saveInForm();

        $o->value = false;
        $expected = $expected."<input type=\"hidden\" name=\"$o->key\" value=\"0\" />\n";
        $this->expectOutputString($expected);
        $o->saveInForm();
    }
}
