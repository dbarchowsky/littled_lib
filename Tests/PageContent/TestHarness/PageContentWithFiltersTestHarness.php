<?php
namespace Littled\Tests\PageContent\TestHarness;

use Littled\PageContent\PageContent;
use Littled\Tests\Filters\TestHarness\TestTableFilters;

class PageContentWithFiltersTestHarness extends PageContent
{
    function __construct()
    {
        parent::__construct();
        $this->filters = new TestTableFilters();
    }
}