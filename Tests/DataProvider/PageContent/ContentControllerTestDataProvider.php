<?php
namespace Littled\Tests\DataProvider\PageContent;

use Littled\Exception\InvalidRouteException;
use Littled\PageContent\ContentController;
use Littled\Tests\PageContent\SiteSection\SectionContentTest;
use Littled\Tests\TestHarness\API\APIListingsRouteTestHarness;
use Littled\Tests\TestHarness\API\APIRecordRouteTestHarness;
use Littled\Tests\TestHarness\PageContent\Navigation\RoutedPageContentTestHarness;
use Littled\Tests\TestHarness\SiteContent\TestTableDetailsPage;
use Littled\Tests\TestHarness\SiteContent\TestTableEditPage;
use Littled\Tests\TestHarness\SiteContent\TestTableListingsPage;
use Littled\Utility\LittledUtility;


class ContentControllerTestDataProvider
{
    function formatNavigationRouteTestProvider(): array
    {
        return array(
            array(
                LittledUtility::joinPaths('/', TestTableDetailsPage::getBaseRoute(), SectionContentTest::TEST_RECORD_ID),
                TestTableListingsPage::class,
                ContentController::OPERATION_DETAILS,
                SectionContentTest::TEST_RECORD_ID,
                'details route'
                ),
            array(
                LittledUtility::joinPaths('/', TestTableListingsPage::getBaseRoute()),
                TestTableEditPage::class,
                ContentController::OPERATION_LISTINGS,
                null,
                'listings route'
            ),
            array(
                LittledUtility::joinPaths('/', TestTableEditPage::getBaseRoute(), SectionContentTest::TEST_RECORD_ID, ContentController::OPERATION_EDIT),
                TestTableDetailsPage::class,
                ContentController::OPERATION_EDIT,
                SectionContentTest::TEST_RECORD_ID,
                'details route'
            ),
        );
    }

    function getAPIRouteClassNameTestProvider(): array
    {
        return array(
            array(['api', 'listings'], APIListingsRouteTestHarness::class),
            array(['api', 'test', SectionContentTest::TEST_RECORD_ID], APIRecordRouteTestHarness::class),
            array(['api'], '', InvalidRouteException::class),
            array([''], '', InvalidRouteException::class),
            array(['listings'], '', InvalidRouteException::class),
        );
    }
}