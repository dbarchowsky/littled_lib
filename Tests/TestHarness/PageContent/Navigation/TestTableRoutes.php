<?php
namespace Littled\Tests\TestHarness\PageContent\Navigation;

use Littled\Account\UserAccount;
use Littled\App\LittledGlobals;
use Littled\Filters\ContentFilters;
use Littled\PageContent\Navigation\RoutedPageContent;
use Littled\Tests\TestHarness\Filters\TestTableContentFiltersTestHarness;
use Littled\Tests\TestHarness\PageContent\Serialized\TestTable;


class TestTableRoutes extends RoutedPageContent
{
	protected static string $content_class = TestTable::class;
	protected static string $filters_class = TestTableContentFiltersTestHarness::class;
	protected static int $access_level = UserAccount::AUTHENTICATION_UNRESTRICTED;
    protected static string $details_page_class='';
    protected static string $details_route='/test';
    protected static string $edit_page_class='';
    protected static string $listings_page_class = RoutedPageContentTestHarness::class;
    protected static string $listings_route='/Tests';

	public function instantiateProperties()
	{
		parent::instantiateProperties();
	}

	public function loadFilters()
	{
		parent::loadFilters();
	}
}