<?php

namespace Littled\PageContent;

use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Filters\ContentFilters;
use Littled\Log\Log;


abstract class RouteBase extends MySQLConnection implements RouteInterface
{
    use RouteTrait;

    /** @var ContentFilters     Filters to apply to page content. */
    public ContentFilters       $filters;
    /** @var string             Query string to attach to page links. */
    protected string            $query_string = '';
    /** @var string             Path to a template file. */
    protected string            $template_filename;

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
     * Template filename getter.
     * @return string
     * @throws ConfigurationUndefinedException
     */
    public function getTemplateFilename(): string
    {
        if (!isset($this->template_filename)) {
            throw new ConfigurationUndefinedException('The template filename is not defined in ' . Log::getClassBaseName($this::class) . '.');
        }
        return $this->template_filename;
    }

    /**
     * Template full path getter
     * @return string
     * @throws ConfigurationUndefinedException
     */
    public function getTemplatePath(): string
    {
        // alias for getTemplateFilename() intended to be overridden by child classes
        // (e.g., route classes where a template path property is defined)
        return $this->getTemplateFilename();
    }

    /**
     * Filters property setter.
     * @param ContentFilters $filters
     * @return $this
     */
    public function setFilters(ContentFilters $filters): RouteBase
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * Template path setter.
     * @param $path
     * @return $this
     */
    public function setTemplateFilename($path): RouteBase
    {
        $this->template_filename = $path;
        return $this;
    }
}