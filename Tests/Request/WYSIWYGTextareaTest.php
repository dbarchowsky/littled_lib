<?php

namespace LittledTests\Request;

use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Request\WYSIWYGTextarea;
use PHPUnit\Framework\TestCase;

class WYSIWYGTextareaTest extends TestCase
{
    /**
     * @throws ConfigurationUndefinedException
     */
    protected function setUp(): void
    {
        parent::setUp();
        LittledGlobals::setSharedTemplatesPath(LITTLED_TEMPLATE_DIR);
        WYSIWYGTextarea::setTemplateBasePath(LittledGlobals::getSharedTemplatesPath().'forms/input-elements/');
    }

    function testConstructor()
    {
        /* default size limit and editor class */
        $o1 = new WYSIWYGTextarea('WYSIWYG label', 'articleText');
        self::assertEquals(WYSIWYGTextarea::DEFAULT_SIZE_LIMIT, $o1->size_limit);
        self::assertEquals(WYSIWYGTextarea::getEditorClass(), $o1->input_css_class);

        /* non-default size limit */
        $o2 = new WYSIWYGTextarea('WYSIWYG label', 'articleText', false, '', 200);
        self::assertEquals(200, $o2->size_limit);
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Request\WYSIWYGTestDataProvider::collectRequestDataTestProvider()
     * @param string $expected
     * @param string $key
     * @param string $src
     * @param array $whitelist_tags
     * @param string $collection
     * @param string $msg
     * @return void
     */
    function testCollectRequestData(string $expected, string $key, string $src, array $whitelist_tags, string $collection='', string $msg='')
    {
        $data = null;
        switch ($collection) {
            case 'POST':
                $_POST[$key] = $src;
                break;
            case 'REQUEST':
                $_REQUEST[$key] = $src;
                break;
            default:
                $data = array($key => $src);
        }
        $o = new WYSIWYGTextarea('WYSIWYG Test', 'p1');
        $o->collectRequestData($data);
        self::assertMatchesRegularExpression($expected, $o->value, $msg);
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Request\WYSIWYGTestDataProvider::renderInputTestProvider()
     * @param string $expected
     * @param string $editor_class
     * @param string $msg
     * @return void
     */
    function testRenderInput(string $expected, string $editor_class, string $msg='')
    {
        $o = new WYSIWYGTextarea('WYSIWYG editor', 'articleText');
        if ($editor_class) {
            $o->setInputCSSClass($editor_class);
        }
        ob_start();
        $o->renderInput();
        $markup = ob_get_contents();
        ob_end_clean();
        self::assertMatchesRegularExpression($expected, $markup, $msg);
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Request\WYSIWYGTestDataProvider::setInputCSSClassTestProvider()
     * @param string $expected
     * @param string $editor_class
     * @param string $msg
     * @return void
     */
    function testSetInputCSSClass(string $expected, string $editor_class, string $msg='')
    {
        $o = new WYSIWYGTextarea('WYSIWYG editor', 'articleText');
        if ($editor_class) {
            $o->setInputCSSClass($editor_class);
        }
        self::assertEquals($expected, $o->input_css_class, $msg);
    }
}