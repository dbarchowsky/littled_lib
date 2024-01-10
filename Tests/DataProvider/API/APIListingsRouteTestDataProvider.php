<?php
namespace LittledTests\DataProvider\API;


use Littled\API\APIRoute;
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use LittledTests\API\APIRouteTestBase;
use Littled\Utility\LittledUtility;

class APIListingsRouteTestDataProvider
{
    public static function collectContentPropertiesTestProvider(): array
    {
        return array(
            array(
                array(
                    'content_type_id' => APIRouteTestBase::TEST_CONTENT_TYPE_ID,
                    'operation' => APIRoute::getDefaultTemplateName()), '',
                array(LittledGlobals::CONTENT_TYPE_KEY => APIRouteTestBase::TEST_CONTENT_TYPE_ID), '',
                'Content type id value present in POST data'),
            array(
                array(
                    'content_type_id' => APIRouteTestBase::TEST_CONTENT_TYPE_ID,
                    'operation' => 'listings'), '',
                array(
                    LittledGlobals::CONTENT_TYPE_KEY => APIRouteTestBase::TEST_CONTENT_TYPE_ID,
                    APIRoute::TEMPLATE_TOKEN_KEY => 'listings'), '',
                'Content type id and template token values in POST data'),
            array([], ContentValidationException::class, [], '', 'No values present in POST data.'),
            array(
                array(
                    'content_type_id' => APIRouteTestBase::TEST_CONTENT_TYPE_ID,
                    'operation' => 'listings'), '',
                array(
                    LittledGlobals::CONTENT_TYPE_KEY => 3,
                    APIRoute::TEMPLATE_TOKEN_KEY => 'bogus-token'),
                LittledUtility::joinPaths(APP_BASE_DIR, 'Tests/DataProvider/API/APIListingsRoute_collectContentProperties_01.dat'),
                'Request input defaults to ajax stream over POST data.'
            ),
        );
    }

    public static function collectRequestDataTestProvider(): array
    {
        return array(
            array([], ConfigurationUndefinedException::class, [], [], '', 'no data'),
            array(
                array('name_filter' => 'bar'), '',
                array(
                    LittledGlobals::CONTENT_TYPE_KEY => APIRouteTestBase::TEST_CONTENT_TYPE_ID,
                    'name' => 'bar'
                ), [], '',
                'GET data'),
            array(
                array('name_filter' => 'biz'), '',
                [], array(
                    LittledGlobals::CONTENT_TYPE_KEY => APIRouteTestBase::TEST_CONTENT_TYPE_ID,
                    'name' => 'biz'), '',
                'POST data'),
            array(
                array(
                    'name_filter' => 'foo',
                    'int_filter' => 43,
                    'bool_filter' => true),
                '', [], [],
                LittledUtility::joinPaths(APP_BASE_DIR, 'Tests/DataProvider/API/APIListingsRouteTest_collectRequestData_01.dat'),
                'ajax stream'),
            array(
                array(
                    'name_filter' => 'foo',
                    'int_filter' => 43,
                    'bool_filter' => true), '',
                array('int_filter' => 82), [],
                LittledUtility::joinPaths(APP_BASE_DIR, 'Tests/DataProvider/API/APIListingsRouteTest_collectRequestData_01.dat'),
                'ajax stream overrides GET data'),
            array(
                array(
                    'name_filter' => 'foo',
                    'int_filter' => 43,
                    'bool_filter' => true),
                '', [], array('int_filter' => 629),
                LittledUtility::joinPaths(APP_BASE_DIR, 'Tests/DataProvider/API/APIRoute_collectFiltersRequestData_01.dat'),
                'ajax stream overrides POST data'),
        );
    }

    public function loadTemplateContentTestProvider(): array
    {
        return array(
            array(
                '/^<div class=\"listings test-listings">[\s\S]*<td>zib zub<\/td>/',
                array(
                    LittledGlobals::CONTENT_TYPE_KEY => APIRouteTestBase::TEST_CONTENT_TYPE_ID,
                    APIRoute::TEMPLATE_TOKEN_KEY => 'listings'
                ),
                'content/test_table/listings.php',
                null,
                'custom listings template'),
            array(
                '/^<div class="test-container">[\s\S]*<div>custom context value: foo<\/div>[\s\S]*<div>default context value: <\/div>/',
                array(
                    LittledGlobals::CONTENT_TYPE_KEY => APIRouteTestBase::TEST_CONTENT_TYPE_ID,
                    APIRoute::TEMPLATE_TOKEN_KEY => 'listings'
                ),
                'APIRouteTest-LoadTemplateContent.php',
                array('custom_var' => 'foo', 'content' => 'bar'),
                'custom template & manual template context'
            ),
        );
    }
}
