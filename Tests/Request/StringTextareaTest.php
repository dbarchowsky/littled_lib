<?php
namespace Littled\Tests\Request;

use Littled\App\LittledGlobals;
use Littled\Request\StringTextarea;
use Littled\Tests\Request\DataProvider\StringTextareaTestDataProvider;
use PHPUnit\Framework\TestCase;


class StringTextareaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        LittledGlobals::setSharedTemplatesPath(LITTLED_TEMPLATE_DIR);
        StringTextarea::setTemplateBasePath(LittledGlobals::getSharedTemplatesPath().'forms/input-elements/');
    }

    /**
     * @dataProvider \Littled\Tests\Request\DataProvider\StringTextareaTestDataProvider::renderInputTestProvider()
     * @param StringTextareaTestDataProvider $data
     * @return void
     */
    function testRenderInput(StringTextareaTestDataProvider $data)
    {
        ob_start();
        $data->field->renderInput();
        $markup = ob_get_contents();
        ob_end_clean();
        foreach($data->expected as $pattern) {
            $this->assertMatchesRegularExpression($pattern, $markup, $data->message);
        }
    }

    /**
     * @dataProvider \Littled\Tests\Request\DataProvider\StringTextareaTestDataProvider::renderTestProvider()
     * @param StringTextareaTestDataProvider $data
     * @return void
     */
    function testRender(StringTextareaTestDataProvider $data)
    {
        ob_start();
        $data->field->render();
        $markup = ob_get_contents();
        ob_end_clean();
        foreach($data->expected as $pattern) {
            $this->assertMatchesRegularExpression($pattern, $markup, $data->message);
        }
    }
}