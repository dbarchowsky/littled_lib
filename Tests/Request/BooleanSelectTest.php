<?php
namespace Littled\Tests\Request;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\Exception\NotImplementedException;
use Littled\Request\RequestInput;
use Littled\Tests\Request\DataProvider\BooleanSelectTestData;
use PHPUnit\Framework\TestCase;

class BooleanSelectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        RequestInput::setTemplateBasePath(LITTLED_TEMPLATE_DIR.'forms/input-elements/');
    }

    /**
     * @dataProvider \Littled\Tests\Request\DataProvider\BooleanSelectTestDataProvider::renderInputTestProvider()
     * @param BooleanSelectTestData $data
     * @return void
     * @throws NotImplementedException
     */
    function testRenderInput(BooleanSelectTestData $data)
    {
        $this->expectOutputRegex($data->expected);
        $data->input->renderInput($data->label_override, $data->options);
    }
}