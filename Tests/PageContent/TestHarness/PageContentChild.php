<?php

namespace Littled\Tests\PageContent\TestHarness;

use Littled\PageContent\PageContent;
use Littled\PageContent\SiteSection\SectionContent;

class PageContentChild extends PageContent
{
    /** @var string */
    public $injected_text;

    public function __construct()
    {
        parent::__construct();
        $this->content = new SectionContent();
        $this->injected_text = '';
    }

    public function getTemplateContext(): array
    {
        return array(
            'test_var' => $this->injected_text
        );
    }
}