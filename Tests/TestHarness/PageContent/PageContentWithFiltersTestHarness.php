<?php
namespace Littled\Tests\TestHarness\PageContent;

use Littled\PageContent\PageContent;
use Littled\Tests\TestHarness\Filters\TestTableContentFiltersTestHarness;


class PageContentWithFiltersTestHarness extends PageContent
{
    function __construct()
    {
        parent::__construct();
        $this->filters = new TestTableContentFiltersTestHarness();
    }

	public function collectRequestData(?array $src = null)
	{
		// stub
	}

    public function getTemplateContext(): array
    {
	    // stub
        return [];
    }

	public function processRequest(): PageContent
	{
		return $this;
	}

    public function setPageState()
    {
	    // stub
    }
}