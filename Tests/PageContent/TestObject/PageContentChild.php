<?php

namespace Littled\Tests\PageContent\TestObject;

class PageContentChild extends \Littled\PageContent\PageContent
{
    /** @var string */
    public $injected_text;

    public function __construct()
    {
        parent::__construct();
        $this->injected_text = '';
    }

    public function getTemplateContext(): array
    {
        return array(
            'test_var' => $this->injected_text
        );
    }
}