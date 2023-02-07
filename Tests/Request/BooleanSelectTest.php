<?php
namespace Littled\Tests\Request;

use Littled\Exception\NotImplementedException;
use Littled\Request\RequestInput;
use Littled\Tests\DataProvider\Request\BooleanSelectTestData;
use PHPUnit\Framework\TestCase;

class BooleanSelectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        RequestInput::setTemplateBasePath(LITTLED_TEMPLATE_DIR.'forms/input-elements/');
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\Request\BooleanSelectTestDataProvider::renderInputTestProvider()
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