<?php

namespace Littled\PageContent;

use Littled\Database\MySQLConnection;
use Littled\Filters\ContentFilters;


abstract class PageContentBase extends MySQLConnection implements PageContentInterface
{
    use RouteTrait;

    /** @var ContentFilters     Filters to apply to page content. */
    public ContentFilters       $filters;
    /** @var string             Query string to attach to page links. */
    protected string            $query_string = '';
    /** @var string             Path to a template file. */
    protected string            $template_path = '';

    /**
     * Formats and stores query string from current filter property values.
     * @param string[]|null $exclude
     * @return string
     */
    public function formatQueryString(?array $exclude=null): string
    {
        if (isset($this->filters)) {
            $this->query_string = $this->filters->formatQueryString($exclude);
        }
        return $this->query_string;
    }

    /**
     * Query string getter
     * @param bool $force_update Flag indicating that the query string should be regenerating instead of using the cached value. Defaults to FALSE.
     * @return string
     */
    public function getQueryString(bool $force_update=false): string
    {
        if ($force_update) {
            $this->query_string = $this->formatQueryString();
        }
        return $this->query_string;
    }

    /**
     * Template path getter.
     * @return string
     */
    public function getTemplatePath(): string
    {
        return $this->template_path;
    }

    /**
     * Filters property setter.
     * @param ContentFilters $filters
     * @return $this
     */
    public function setFilters(ContentFilters $filters): PageContentBase
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * Template path setter.
     * @param $path
     * @return $this
     */
    public function setTemplatePath($path): PageContentBase
    {
        $this->template_path = $path;
        return $this;
    }
}