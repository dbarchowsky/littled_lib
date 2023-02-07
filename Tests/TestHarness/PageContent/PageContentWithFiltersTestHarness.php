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
}