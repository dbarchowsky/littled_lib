<?php

namespace Littled\Tests\Ajax\DataProvider;

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

    public static function setCacheClassTestProvider(): array
    {
        return array(
            ['', '\Littled\Tests\PageContent\Cache\TestHarness\ContentCacheTestHarness'],
            ['', 'Littled\Tests\PageContent\Cache\TestHarness\ContentCacheTestHarness'],
            [Error::class, '\Littled\PageContent\Cache\ContentCache'],
            [InvalidTypeException::class, '\Littled\PageContent\PageConfig'],
        );
    }

    public static function setControllerClassTestProvider(): array
    {
        return array(
            ['', '\Littled\Tests\PageContent\TestHarness\ContentControllerTestHarness'],
            ['', 'Littled\Tests\PageContent\TestHarness\ContentControllerTestHarness'],
            [Error::class, '\Littled\PageContent\ContentController'],
            [InvalidTypeException::class, '\Littled\PageContent\PageConfig'],
        );
    }
}