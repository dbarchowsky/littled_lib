<?php
namespace Littled\Tests\TestHarness\PageContent;

use Littled\PageContent\PageContent;
use Littled\Tests\TestHarness\Filters\ContentFiltersChild;
use Littled\Tests\TestHarness\Filters\TestTableContentFiltersTestHarness;
use Littled\Tests\TestHarness\Filters\TestTableFilters;

class PageContentWithFiltersTestHarness extends PageContent
{
    function __construct()
    {
        parent::__construct();
        $this->filters = new TestTableContentFiltersTestHarness();
    }

    public function getTemplateContext(): array
    {
        // TODO: Implement getTemplateContext() method.
        return [];
    }

    public function setPageState()
    {
        // TODO: Implement setPageState() method.
    }
}