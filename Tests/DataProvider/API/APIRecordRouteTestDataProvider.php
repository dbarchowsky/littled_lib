<?php
namespace Littled\Tests\DataProvider\API;

use Littled\API\APIRoute;
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidTypeException;
use Littled\Tests\API\APIRouteTestBase;
use Littled\Utility\LittledUtility;


class APIRecordRouteTestDataProvider
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
                    'operation' => 'edit'),
                '',
                array(
                    LittledGlobals::CONTENT_TYPE_KEY => 3,
                    APIRoute::TEMPLATE_TOKEN_KEY => 'bogus-value'
                ),
                LittledUtility::joinPaths(APP_BASE_DIR, 'Tests/DataProvider/API/APIRecordRoute_collectContentProperties_01.dat'),
                'Request input defaults to ajax stream over POST data.'
            ),
        );
    }
}