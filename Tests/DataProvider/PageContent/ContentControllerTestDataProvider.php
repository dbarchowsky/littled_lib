<?php
namespace LittledTests\DataProvider\PageContent;

use Littled\Exception\InvalidRouteException;
use Littled\PageContent\ContentController;
use LittledTests\PageContent\SiteSection\SectionContentTest;
use LittledTests\TestHarness\API\APIListingsRouteTestHarness;
use LittledTests\TestHarness\API\APIRecordRouteTestHarness;
use LittledTests\TestHarness\PageContent\Navigation\RoutedPageContentTestHarness;
use LittledTests\TestHarness\SiteContent\TestTableDetailsPage;
use LittledTests\TestHarness\SiteContent\TestTableEditPage;
use LittledTests\TestHarness\SiteContent\TestTableListingsPage;
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