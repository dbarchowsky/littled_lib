<?php

namespace Littled\Tests\DataProvider\PageContent;

use Littled\PageContent\ContentController;
use Littled\Tests\PageContent\SiteSection\SectionContentTest;
use Littled\Tests\TestHarness\PageContent\Navigation\RoutedPageContentTestHarness;
use Littled\Tests\TestHarness\PageContent\Navigation\SectionNavigationRoutesTestHarness;
use Littled\Utility\LittledUtility;

class ContentControllerTestDataProvider
{
    public function formatNavigationRouteTestProvider(): array
    {
        return array(
            array(
                LittledUtility::joinPaths(SectionNavigationRoutesTestHarness::getDetailsRoute(), SectionContentTest::TEST_RECORD_ID),
                    RoutedPageContentTestHarness::class,
                    ContentController::OPERATION_DETAILS,
                    SectionContentTest::TEST_RECORD_ID,
                    'details route'
                ),
            array(
                SectionNavigationRoutesTestHarness::getListingsRoute(),
                RoutedPageContentTestHarness::class,
                ContentController::OPERATION_LISTINGS,
                null,
                'listings route'
            ),
            array(
                LittledUtility::joinPaths(SectionNavigationRoutesTestHarness::getDetailsRoute(), SectionContentTest::TEST_RECORD_ID, ContentController::OPERATION_EDIT),
                RoutedPageContentTestHarness::class,
                ContentController::OPERATION_EDIT,
                SectionContentTest::TEST_RECORD_ID,
                'details route'
            ),
        );
    }
}