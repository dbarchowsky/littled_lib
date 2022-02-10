<?php

namespace Littled\Tests\PageContent\SiteSection\DataProvider;

class ContentTemplateData
{
    /** @var int */
    public $id;
    /** @var string */
    public $name;
    /** @var string */
    public $template;

    function __construct(int $id, string $name, string $template)
    {
        $this->id = $id;
        $this->name = $name;
        $this->template = $template;
    }
}