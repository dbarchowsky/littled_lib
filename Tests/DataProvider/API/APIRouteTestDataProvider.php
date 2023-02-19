<?php
namespace Littled\Tests\DataProvider\API;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\PageContent\Cache\ContentCache;
use Littled\PageContent\ContentController;
use Littled\PageContent\PageConfig;
use Littled\API\APIRoute;
use Littled\App\LittledGlobals;
use Littled\Exception\InvalidTypeException;
use Littled\PageContent\PageContentInterface;
use Littled\Tests\API\APIRouteTestBase;
use Littled\Tests\TestHarness\PageContent\ContentControllerTestHarness;
use Littled\Tests\TestHarness\PageContent\Cache\ContentCacheTestHarness;
use Littled\Utility\LittledUtility;


class APIRouteTestDataProvider
{
    public static function collectFiltersRequestDataTestProvider(): array
    {
        return array(
            array([], [], [], '', 'no data'),
            array(array('name_filter' => 'bar'), array('name' => 'bar'), [], '', 'GET data'),
            array(array('name_filter' => 'biz'), [], array('name' => 'biz'), '', 'POST data'),
            array(
                array(
                    'name_filter' => 'foo',
                    'int_filter' => 43,
                    'bool_filter' => true), [], [],
                LittledUtility::joinPaths(APP_BASE_DIR, 'Tests/DataProvider/API/APIRoute_collectFiltersRequestData_01.dat'),
                'ajax stream'),
            array(
                array(
                    'name_filter' => 'foo',
                    'int_filter' => 43,
                    'bool_filter' => true),
                array('int_filter' => 82), [],
                LittledUtility::joinPaths(APP_BASE_DIR, 'Tests/DataProvider/API/APIRoute_collectFiltersRequestData_01.dat'),
                'ajax stream overrides GET data'),
            array(
                array(
                    'name_filter' => 'foo',
                    'int_filter' => 43,
                    'bool_filter' => true),
                [], array('int_filter' => 629),
                LittledUtility::joinPaths(APP_BASE_DIR, 'Tests/DataProvider/API/APIRoute_collectFiltersRequestData_01.dat'),
                'ajax stream overrides POST data'),
        );
    }

    public static function collectPageActionTestProvider(): array
    {
        $custom_data = [];
        $custom_data[LittledGlobals::COMMIT_KEY] = true;
        return array(
            array(PageContentInterface::COMMIT_ACTION, array(LittledGlobals::COMMIT_KEY => true), '', null, 'POST commit key'),
            array(PageContentInterface::CANCEL_ACTION, array(LittledGlobals::CANCEL_KEY => true), '', null, 'POST cancel key'),
            array('', array('randomKey' => true), '', null, 'POST invalid key'),
            array('', array(LittledGlobals::COMMIT_KEY => 45), '', null, 'POST commit key with non-true value'),
            array('', array(LittledGlobals::COMMIT_KEY => false), '', null, 'POST commit key set to false'),
            array(PageContentInterface::COMMIT_ACTION, [], APIRouteTestBase::AJAX_INPUT_SOURCE, null, 'API mock data'),
            array(PageContentInterface::COMMIT_ACTION, [], '', $custom_data, 'custom data'),
        );
    }

    public static function loadTemplateContentTestProvider(): array
    {
        return array(
            [new APIRouteLoadTemplateContentTestData(
                'Default context and template',
                '/<div class=\"dialog delete-confirmation\"(.|\n)*the record will be permanently deleted/i'
            )],
            [new APIRouteLoadTemplateContentTestData(
                'Override context',
                '/<div class=\"test-container\">(.|\n)*custom context value: test injected value(.|\n)*default context value: undefined(.|\n)*<\/div>/i',
                array('custom_var' => 'test injected value'),
                'APIRouteTest-LoadTemplateContent.php'
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

	public static function sendTextResponseTestProvider(): array
	{
		return array(
			array('/^This is text.*json property\.$/', 'This is text response stored in json property.'),
			array('/^\<p\>This is html.*json property.\<\/p\>$/', '<p>This is html stored in json property.</p>'),
			array('/^.*text.*to the method\.$/', 'This is text response passed to the method.'),
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

    public static function lookupRouteTestProvider(): array
    {
        return array(
            array('listings', '/^\/api\/listings$/'),
            array('delete', '/^\/api\/\[#]\/delete$/'),
        );
    }
}