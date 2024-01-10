<?php
namespace LittledTests\Request;

use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Request\StringTextarea;
use LittledTests\DataProvider\Request\StringTextareaTestDataProvider;
use PHPUnit\Framework\TestCase;


class StringTextareaTest extends TestCase
{
    /**
     * @throws ConfigurationUndefinedException
     */
    protected function setUp(): void
    {
        parent::setUp();
        LittledGlobals::setSharedTemplatesPath(LITTLED_TEMPLATE_DIR);
        StringTextarea::setTemplateBasePath(LittledGlobals::getSharedTemplatesPath().'forms/input-elements/');
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Request\StringTextareaTestDataProvider::renderInputTestProvider()
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
     * @dataProvider \LittledTests\DataProvider\Request\StringTextareaTestDataProvider::renderTestProvider()
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