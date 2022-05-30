<?php

namespace Littled\Tests\Ajax\DataProvider;

use Littled\Tests\Ajax\DataProvider\AjaxPageLoadTemplateContentTestData;
use Error;
use Littled\Ajax\AjaxPage;
use Littled\App\LittledGlobals;
use Littled\Exception\InvalidTypeException;
use Littled\Tests\Ajax\AjaxPageTest;

class AjaxPageTestDataProvider
{
    public static function collectContentPropertiesTestProvider(): array
    {
        return array(
            array(
                AjaxPageTest::TEST_CONTENT_TYPE_ID,
                AjaxPage::getDefaultTemplateName(),
                array(LittledGlobals::CONTENT_TYPE_KEY => AjaxPageTest::TEST_CONTENT_TYPE_ID),
                'Content type id value present in POST data'),
            array(
                AjaxPageTest::TEST_CONTENT_TYPE_ID,
                'listings',
                array(
                    LittledGlobals::CONTENT_TYPE_KEY => AjaxPageTest::TEST_CONTENT_TYPE_ID,
                    AjaxPage::TEMPLATE_TOKEN_KEY => 'listings'),
                'Content type id and template token values in POST data'),
            array(null, AjaxPage::getDefaultTemplateName(), [], 'No values present in POST data.'),
        );
    }

    public static function loadTemplateContentTestProvider(): array
    {
        return array(
            [new AjaxPageLoadTemplateContentTestData(
                'Default context and template',
                '/<div class=\"dialog delete-confirmation\"(.|\n)*the record will be permanently deleted/i'
            )],
            [new AjaxPageLoadTemplateContentTestData(
                'Override context',
                '/<div class=\"test-container\">(.|\n)*custom context value: test injected value(.|\n)*default context value: undefined(.|\n)*<\/div>/i',
                array('custom_var' => 'test injected value'),
                'AjaxPageTest-LoadTemplateContent.php'
            )],
        );
    }

    public static function setCacheClassTestProvider(): array
    {
        return array(
            ['', '\Littled\Tests\PageContent\Cache\TestHarness\ContentCacheTestHarness', 'ContentCacheTestHarness fully qualified'],
            ['', 'Littled\Tests\PageContent\Cache\TestHarness\ContentCacheTestHarness', 'ContentCacheTestHarness not fully qualified'],
            ['', '\Littled\PageContent\Cache\ContentCache', 'ContentCache (parent class) does not throw InvalidTypeException'],
            [InvalidTypeException::class, '\Littled\PageContent\PageConfig', 'PageConfig throws InvalidTypeException'],
        );
    }

    public static function setControllerClassTestProvider(): array
    {
        return array(
            ['', '\Littled\Tests\PageContent\TestHarness\ContentControllerTestHarness'],
            ['', 'Littled\Tests\PageContent\TestHarness\ContentControllerTestHarness'],
            ['', '\Littled\PageContent\ContentController'],
            [InvalidTypeException::class, '\Littled\PageContent\PageConfig'],
        );
    }
}