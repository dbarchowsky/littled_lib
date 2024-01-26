<?php

namespace LittledTests\TestHarness\PageContent\Navigation;

use Littled\Account\UserAccount;
use Littled\PageContent\Navigation\RoutedPageContent;
use LittledTests\PageContent\Navigation\RoutedPageContentTest;
use Littled\Utility\LittledUtility;

class RoutedPageContentTestHarness extends RoutedPageContent
{
    protected static int $access_level = UserAccount::AUTHENTICATION_UNRESTRICTED;
    protected static string $template_dir = RoutedPageContentTest::TEST_TEMPLATE_DIR;
    protected static string $template_filename = RoutedPageContentTest::TEST_TEMPLATE_FILENAME;
    protected static string $routes_class = SectionNavigationRoutesTestHarness::class;
    protected static string $base_route = '';

    public function collectRequestData(?array $src = null)
    {
        /* stub */
    }

    public static function formatRoutePath(?int $record_id = null): string
    {
        return LittledUtility::joinPaths(static::$route_parts);
    }

    public function getTemplateContext(): array
    {
        /* stub */
        return [];
    }

    public function instantiateProperties(?int $record_id = null)
    {
        parent::instantiateProperties();
        $content_class = static::getContentClassName();
        if ($content_class) {
            $this->content = new $content_class();
            if ($record_id > 0) {
                $this->content->id->setInputValue($record_id);
            }
        }
    }

    /**
     * @inheritDoc
     * Override parent to provide public interface to tests.
     */
    public function loadFilters()
    {
        parent::loadFilters();
    }

    public function processRequest(): RoutedPageContent
    {
        return $this;
    }

    public function setPageState()
    {
        // stub
    }
}