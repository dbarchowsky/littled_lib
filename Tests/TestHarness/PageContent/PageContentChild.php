<?php

namespace Littled\Tests\TestHarness\PageContent;

use Littled\PageContent\PageContent;
use Littled\Tests\TestHarness\PageContent\SiteSection\SectionContentTestHarness;

class PageContentChild extends PageContent
{
    /** @var string */
    public string $injected_text;

    public function __construct()
    {
        parent::__construct();
        $this->content = new SectionContentTestHarness();
        $this->injected_text = '';
    }

	public function collectRequestData(?array $src = null)
	{
		// stub
	}

	public function getTemplateContext(): array
    {
        return array(
            'test_var' => $this->injected_text
        );
    }

	public function processRequest(): PageContentChild
	{
		return $this;
	}

    public function setPageState()
    {
	    // stub
    }
}