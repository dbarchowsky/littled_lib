<?php
namespace Littled\Tests\DataProvider\App;

use Littled\API\APIRoute;
use Littled\App\LittledGlobals;
use Littled\Tests\API\APIRouteTestBase;
use Littled\Utility\LittledUtility;


class AppBaseTestDataProvider
{
    public static function getAjaxRequestDataTestProvider(): array
    {
        return array(
            array(null, '', []),
            array(null, '', array(LittledGlobals::CONTENT_TYPE_KEY => APIRouteTestBase::TEST_CONTENT_TYPE_ID)),
            array(array(LittledGlobals::CONTENT_TYPE_KEY => 3),
                LittledUtility::joinPaths(APP_BASE_DIR, 'Tests', 'DataProvider', 'App', 'AppBaseTest_testGetAjaxRequestData_01.dat'),
                []),
            array(
                array(
                    LittledGlobals::CONTENT_TYPE_KEY => 83,
                    APIRoute::TEMPLATE_TOKEN_KEY => 'my-operation'),
                LittledUtility::joinPaths(APP_BASE_DIR, 'Tests', 'DataProvider', 'App', 'AppBaseTest_testGetAjaxRequestData_02.dat'),
                array(LittledGlobals::CONTENT_TYPE_KEY => APIRouteTestBase::TEST_CONTENT_TYPE_ID)),
        );
    }
}