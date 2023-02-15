<?php
namespace Littled\Tests\TestHarness\PageContent;

use Littled\Exception\InvalidRouteException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\PageContent\ContentController;
use Littled\PageContent\Navigation\RoutedPageContent;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\PageContent\SiteSection\SectionContent;
use Littled\Tests\API\AjaxPageTest;
use Littled\Tests\TestHarness\PageContent\Serialized\TestTable;
use Littled\Tests\TestHarness\Filters\TestTableContentFiltersTestHarness;
use Littled\Tests\TestHarness\PageContent\SiteSection\TestTableDetailsPage;
use Littled\Tests\TestHarness\PageContent\SiteSection\TestTableListingsPage;
use Littled\Utility\LittledUtility;


class ContentControllerTestHarness extends ContentController
{
    /**
     * @inheritDoc
     */
    public static function getContentClass(int $content_id): string
    {
        switch($content_id) {
            case AjaxPageTest::TEST_CONTENT_TYPE_ID:
                return TestTable::class;
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
                return TestTableContentFiltersTestHarness::class;
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
				return 'Littled\Tests\TestHarness\PageContent\PageContentChild';
			default:
				return 'TestContentClassString';
		}
	}

    /**
     * @inheritDoc
     * @throws InvalidRouteException
     */
    public static function getRoutedPageContentClass(array $route_parts): string
    {
        if (count($route_parts) < 1) {
            throw new InvalidRouteException('A route was not supplied.');
        }
        switch($route_parts[0]) {
            case TestTableDetailsPage::getBaseRoute():
                return TestTableDetailsPage::class;
            case TestTableListingsPage::getBaseRoute():
                /* This is a stand-in. IRL the sub-route would dictate a subclass representing Details, Edit, or Delete page content. */
                return TestTableListingsPage::class;
            default:
                throw new InvalidRouteException('Invalid route "'.call_user_func_array([LittledUtility::class, 'joinPaths'], $route_parts).'".');
        }
    }

    /**
     * @param RoutedPageContent $class
     * @param string $operation
     * @param int|null $record_id
     * @return string
     * @throws InvalidValueException
     * @throws NotImplementedException
     */
    public static function publicFormatNavigationRoute(RoutedPageContent $class, string $operation, ?int $record_id=null): string
    {
        return parent::formatNavigationRoute($class, $operation, $record_id);
    }
}