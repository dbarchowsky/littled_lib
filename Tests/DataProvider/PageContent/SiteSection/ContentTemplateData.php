<?php

namespace Littled\Tests\DataProvider\PageContent\SiteSection;

class ContentTemplateData
{
    public int      $id;
    public string   $name;
    public string   $template;

    function __construct(int $id, string $name, string $template)
    {
        $this->id = $id;
        $this->name = $name;
        $this->template = $template;
    }
}