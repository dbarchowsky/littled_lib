<?php

namespace Littled\Tests\DataProvider\Ajax;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\PageContent\Cache\ContentCache;
use Littled\PageContent\ContentController;
use Littled\PageContent\PageConfig;
use Littled\Ajax\AjaxPage;
use Littled\App\LittledGlobals;
use Littled\Exception\InvalidTypeException;
use Littled\Tests\Ajax\AjaxPageTest;
use Littled\Tests\TestHarness\PageContent\ContentControllerTestHarness;
use Littled\Tests\TestHarness\PageContent\Cache\ContentCacheTestHarness;


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

    public static function collectPageActionTestProvider(): array
    {
        $custom_data = [];
        $custom_data[LittledGlobals::COMMIT_KEY] = true;
        return array(
            array(AjaxPage::COMMIT_ACTION, 'post', LittledGlobals::COMMIT_KEY, true, null, 'POST commit key'),
            array(AjaxPage::CANCEL_ACTION, 'post', LittledGlobals::CANCEL_KEY, true, null, 'POST cancel key'),
            array('', 'post', 'randomKey', true, null, 'POST invalid key'),
            array('', 'post', LittledGlobals::COMMIT_KEY, 45, null, 'POST commit key with non-true value'),
            array('', 'post', LittledGlobals::COMMIT_KEY, false, null, 'POST commit key set to false'),
            array(AjaxPage::COMMIT_ACTION, 'ajax', '', '', null, 'Ajax mock data'),
            array(AjaxPage::COMMIT_ACTION, 'custom', '', '', $custom_data, 'custom data'),
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
            [ContentCacheTestHarness::class, '', 'ContentCacheTestHarness fully qualified'],
            [ContentCacheTestHarness::class, '', 'ContentCacheTestHarness not fully qualified'],
            [ContentCache::class, ConfigurationUndefinedException::class, 'ContentCache throws ConfigurationUndefinedException'],
            [PageConfig::class, InvalidTypeException::class, 'PageConfig throws InvalidTypeException'],
        );
    }

    public static function setControllerClassTestProvider(): array
    {
        return array(
            ['', ContentControllerTestHarness::class],
            ['', ContentControllerTestHarness::class],
            [ConfigurationUndefinedException::class, ContentController::class],
            [InvalidTypeException::class, PageConfig::class],
        );
    }
}