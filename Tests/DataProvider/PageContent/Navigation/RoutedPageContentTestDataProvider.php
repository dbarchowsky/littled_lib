<?php
namespace Littled\Tests\DataProvider\PageContent\Navigation;


use Littled\Tests\DataProvider\PageContent\Navigation\RoutedPageContent\GetPageRouteTestData;
use Littled\Tests\TestHarness\SiteContent\TestTableDetailsPage;
use Littled\Tests\TestHarness\SiteContent\TestTableEditPage;
use Littled\Tests\TestHarness\SiteContent\TestTableListingsPage;

class RoutedPageContentTestDataProvider
{
    public static function collectActionFromRouteTestProvider(): array
    {
        return array(
            ['', null, array('base')],
            ['', 123, array('base', '123')],
            ['edit', 123, array('base', '123', 'edit')],
            ['add', null, array('base', 'add')],
        );
    }

    public static function collectRecordIdFromRouteTestProvider(): array
    {
        return array(
            [null, array('base')],
            [123, array('base', '123')],
            [123, array('base', '123', 'edit')],
            [null, array('base', 'add')],
            [null, array('base', 'view', '123')],
            [123, array('base', '123', 'many', 'other', 'parts')],
        );
    }

    public static function getListingsURIWithFilters(): array
    {
        return array(
            array(['p=1', 'pl=25'], ['kw']),
            array(['p=1', 'pl=25', 'kw=foo', 'next=view'], [], array('kw' => 'foo')),
            array(['p=1', 'pl=25', 'kw=foo'], ['next'], array('kw' => 'foo'), array('next')),
            array(['p=1', 'pl=25'], ['next', 'kw'], array('kw' => 'foo'), array('next', 'kw')),
        );
    }

	public static function getPageRouteTestProvider(): array
	{
		return array_map(
			function ($e) { return array($e); }, array(
			new GetPageRouteTestData('/tests', TestTableListingsPage::class),
			new GetPageRouteTestData('/test/45', TestTableDetailsPage::class, 45),
			new GetPageRouteTestData('/test/add', TestTableEditPage::class),
			new GetPageRouteTestData('/test/82/edit', TestTableEditPage::class, 82),
		));
	}

	public static function getPageRouteWithFiltersTestProvider(): array
	{
		return array_map(
			function ($e) { return array($e); }, array(
			new GetPageRouteTestData('/tests', TestTableListingsPage::class, null, false),
			new GetPageRouteTestData('/test/45', TestTableDetailsPage::class, 45, false),
			new GetPageRouteTestData('/test/add', TestTableEditPage::class, null, false),
			new GetPageRouteTestData('/test/82/edit', TestTableEditPage::class, 82, false),
			new GetPageRouteTestData('/tests?filter=0&pl=20', TestTableListingsPage::class, null, true),
			new GetPageRouteTestData('/test/45?filter=0&pl=20', TestTableDetailsPage::class, 45, true),
			new GetPageRouteTestData('/test/add?filter=0&pl=20', TestTableEditPage::class, null, true),
			new GetPageRouteTestData('/test/82/edit?filter=0&pl=20', TestTableEditPage::class, 82, true),
		));
	}

    public static function getRecordIdProvider(): array
    {
        return array(
            [null, null],
            [0, null],
            [45, 45]
        );
    }
}