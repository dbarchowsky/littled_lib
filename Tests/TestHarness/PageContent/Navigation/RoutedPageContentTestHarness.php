<?php
namespace Littled\Tests\TestHarness\PageContent\Navigation;

use Littled\Account\UserAccount;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidTypeException;
use Littled\PageContent\Navigation\RoutedPageContent;
use Littled\Tests\PageContent\Navigation\RoutedPageContentTest;

class RoutedPageContentTestHarness extends RoutedPageContent
{
    protected static int    $access_level       = UserAccount::AUTHENTICATION_UNRESTRICTED;
	protected static string $template_dir       = RoutedPageContentTest::TEST_TEMPLATE_DIR;
	protected static string $template_filename  = RoutedPageContentTest::TEST_TEMPLATE_FILENAME;
    protected static string $routes_class       = SectionNavigationRoutesTestHarness::class;
    protected static string $base_route         = '';

    /**
     * @throws InvalidTypeException
     * @throws ConfigurationUndefinedException
     */
    function __construct()
    {
        parent::__construct();
        $this->instantiateProperties();
    }

    /**
     * @return string
     */
    public static function getBaseRoute(): string
    {
        return static::$base_route;
    }

    public function getTemplateContext(): array
    {
        // TODO: Implement getTemplateContext() method.
        return [];
    }
    public function instantiateProperties(?int $record_id=null)
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

    public function setPageState()
    {
        // TODO: Implement setPageState() method.
    }
}