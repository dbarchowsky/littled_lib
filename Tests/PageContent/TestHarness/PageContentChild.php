<?php

namespace Littled\Tests\PageContent\TestHarness;

use Littled\PageContent\PageContent;
use Littled\PageContent\SiteSection\SectionContent;
use Littled\Tests\PageContent\SiteSection\TestHarness\SectionContentTestHarness;

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

    public function getTemplateContext(): array
    {
        return array(
            'test_var' => $this->injected_text
        );
    }
}