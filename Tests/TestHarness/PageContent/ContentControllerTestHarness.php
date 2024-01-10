<?php
namespace LittledTests\TestHarness\PageContent;

use Littled\Exception\InvalidRouteException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\PageContent\ContentController;
use Littled\PageContent\Navigation\RoutedPageContent;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\PageContent\SiteSection\SectionContent;
use LittledTests\API\APIRouteTestBase;
use LittledTests\TestHarness\API\APIListingsRouteTestHarness;
use LittledTests\TestHarness\API\APIRecordRouteTestHarness;
use LittledTests\TestHarness\Filters\TestTableContentFiltersTestHarness;
use LittledTests\TestHarness\SiteContent\TestTableDetailsPage;
use LittledTests\TestHarness\SiteContent\TestTableListingsPage;
use LittledTests\TestHarness\PageContent\SiteSection\TestTableSectionContentTestHarness;
use Littled\Utility\LittledUtility;


class ContentControllerTestHarness extends ContentController
{
    /**
     * @param RoutedPageContent $class
     * @param string $operation
     * @param int|null $record_id
     * @return string
     * @throws InvalidValueException
     * @throws NotImplementedException
     */
    public static function formatNavigationRoute(RoutedPageContent $class, string $operation, ?int $record_id=null): string
    {
        return parent::formatNavigationRoute($class, $operation, $record_id);
    }

    /**
     * @inheritDoc
     * @throws InvalidRouteException
     */
    public static function getAPIRouteClassName(array $route_parts): string
    {
        if (count($route_parts) < 2) {
            throw new InvalidRouteException("Invalid route: \"".LittledUtility::joinPaths('/', ...$route_parts)."\"");
        }
        switch($route_parts[1]) {
            case APIListingsRouteTestHarness::getSubRoute():
                return APIListingsRouteTestHarness::class;
            case APIRecordRouteTestHarness::getSubRoute():
                return APIRecordRouteTestHarness::class;
            default:
                throw new InvalidRouteException('Invalid api route "'.LittledUtility::joinPaths('/', ...$route_parts).'".');
        }
    }

    /**
     * @inheritDoc
     */
    public static function getContentClass(int $content_id): string
    {
        switch($content_id) {
            case APIRouteTestBase::TEST_CONTENT_TYPE_ID:
                return TestTableSectionContentTestHarness::class;
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
            case APIRouteTestBase::TEST_CONTENT_TYPE_ID:
                return TestTableContentFiltersTestHarness::class;
            default:
                return 'TestContentClassString';
        }
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
			case APIRouteTestBase::TEST_CONTENT_TYPE_ID:
				return 'LittledTests\TestHarness\PageContent\PageContentChild';
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
     * @inheritDoc
     * @throws InvalidRouteException
     */
    public static function getRoutedPageContentClass(array $route_parts, bool $send_404=true): string
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
                throw new InvalidRouteException('Invalid route "'.LittledUtility::joinPaths('/', ...$route_parts).'".');
        }
    }
}