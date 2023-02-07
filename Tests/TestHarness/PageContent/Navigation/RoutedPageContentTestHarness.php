<?php
namespace Littled\Tests\TestHarness\PageContent\Navigation;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidTypeException;
use Littled\PageContent\Navigation\RoutedPageContent;
use Littled\Tests\PageContent\Navigation\RoutedPageContentTest;

class RoutedPageContentTestHarness extends RoutedPageContent
{
	protected static string $template_dir       = RoutedPageContentTest::TEST_TEMPLATE_DIR;
	protected static string $template_filename  = RoutedPageContentTest::TEST_TEMPLATE_FILENAME;
    protected static string $routes_class       = SectionNavigationRoutesTestHarness::class;

    /**
     * @throws InvalidTypeException
     * @throws ConfigurationUndefinedException
     */
    function __construct()
    {
        parent::__construct();
        $this->instantiateProperties();
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
}