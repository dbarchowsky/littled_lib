<?php

namespace Littled\Tests\PageContent\TestHarness;

use Littled\PageContent\ContentController;
use Littled\PageContent\Navigation\RoutedPageContent;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\PageContent\SiteSection\SectionContent;
use Littled\Tests\Ajax\AjaxPageTest;

class ContentControllerTestHarness extends ContentController
{
    /**
     * @inheritDoc
     */
    public static function getContentClass(int $content_id): string
    {
        switch($content_id) {
            case AjaxPageTest::TEST_CONTENT_TYPE_ID:
                return 'Littled\Tests\PageContent\SiteSection\TestHarness\SectionContentTestHarness';
            default:
                return 'TestContentClassString';
        }
    }

    /**
     * @inheritDoc
     */
    public static function getContentFiltersClass(int $content_id): string
    {
        switch($content_id) {
            case AjaxPageTest::TEST_CONTENT_TYPE_ID:
                return 'Littled\Tests\Filters\TestHarness\TestTableFilters';
            default:
                return 'TestContentClassString';
        }
    }

    /**
     * @param SectionContent $content
     * @return string
     */
    protected static function getPostEditTemplatePath(SerializedContent $content): string
    {
        return 'Abstract method placeholder. Content type '.get_class($content);
    }

    /**
     * @param int $site_section_id
     * @param string $operation
     * @param int|null $record_id
     * @return string
     */
    public static function getNavigationRoute(int $site_section_id, string $operation, ?int $record_id = null): string
    {
        return 'Abstract method placeholder';
    }

	public static function getPageContentClass(int $content_id): string
	{
		switch($content_id) {
			case AjaxPageTest::TEST_CONTENT_TYPE_ID:
				return 'Littled\Tests\PageContent\TestHarness\PageContentChild';
			default:
				return 'TestContentClassString';
		}
	}

	public static function getRoutedPageInstance(array $route_parts): RoutedPageContent
	{
		// TODO: Implement getRoutedPageInstance() method.
		return new RoutedPageContent();
	}
}