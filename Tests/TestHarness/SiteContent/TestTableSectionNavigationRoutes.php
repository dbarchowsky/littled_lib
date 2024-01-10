<?php
namespace LittledTests\TestHarness\SiteContent;

use Littled\PageContent\Navigation\SectionNavigationRoutes;


class TestTableSectionNavigationRoutes extends SectionNavigationRoutes
{
	protected static string $details_page_class=TestTableDetailsPage::class;
	protected static string $edit_page_class=TestTableEditPage::class;
	protected static string $listings_page_class=TestTableListingsPage::class;
}