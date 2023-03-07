<?php
namespace Littled\Filters;

use Littled\PageContent\SiteSection\ContentProperties;


class KeywordContentFilters extends ContentFilters
{
    public StringContentFilter $keyword;

    public function __construct(string $properties_class = ContentProperties::class)
    {
        parent::__construct($properties_class);
        $this->keyword = new StringContentFilter("Keyword", "kw", '', 50, static::$cookie_key);
    }
}