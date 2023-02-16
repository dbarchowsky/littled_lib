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
	protected static string $routes_class = TestTableSectionNavigationRoutes::class;
	protected static int $access_level = UserAccount::AUTHENTICATION_UNRESTRICTED;

	public function instantiateProperties()
	{
		parent::instantiateProperties();
	}

	public function loadFilters()
	{
		parent::loadFilters();
	}
}