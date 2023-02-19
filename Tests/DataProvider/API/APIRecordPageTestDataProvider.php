<?php
namespace Littled\Tests\DataProvider\API;

use Littled\API\APIPage;
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidTypeException;
use Littled\Tests\API\APIPageTestBase;
use Littled\Utility\LittledUtility;


class APIRecordPageTestDataProvider
{
    public static function collectContentPropertiesTestProvider(): array
    {
        return array(
            array(
                array(
                    'content_type_id' => APIPageTestBase::TEST_CONTENT_TYPE_ID,
                    'operation' => APIPage::getDefaultTemplateName()), '',
                array(LittledGlobals::CONTENT_TYPE_KEY => APIPageTestBase::TEST_CONTENT_TYPE_ID), '',
                'Content type id value present in POST data'),
            array(
                array(
                    'content_type_id' => APIPageTestBase::TEST_CONTENT_TYPE_ID,
                    'operation' => 'listings'), '',
                array(
                    LittledGlobals::CONTENT_TYPE_KEY => APIPageTestBase::TEST_CONTENT_TYPE_ID,
                    APIPage::TEMPLATE_TOKEN_KEY => 'listings'), '',
                'Content type id and template token values in POST data'),
            array([], ContentValidationException::class, [], '', 'No values present in POST data.'),
            array(
                array(
                    'content_type_id' => APIPageTestBase::TEST_CONTENT_TYPE_ID,
                    'operation' => 'edit'),
                '',
                array(
                    LittledGlobals::CONTENT_TYPE_KEY => 3,
                    APIPage::TEMPLATE_TOKEN_KEY => 'bogus-value'
                ),
                LittledUtility::joinPaths(APP_BASE_DIR, 'Tests/DataProvider/API/APIRecordPage_collectContentProperties_01.dat'),
                'Request input defaults to ajax stream over POST data.'
            ),
        );
    }
}