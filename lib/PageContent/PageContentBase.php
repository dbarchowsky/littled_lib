<?php
namespace Littled\PageContent;

use Littled\Database\MySQLConnection;
use Littled\Filters\ContentFilters;


abstract class PageContentBase extends MySQLConnection implements PageContentInterface
{
    /** @var string             Path to template file. */
    protected string            $template_path = '';
    /** @var ContentFilters     Filters to apply to page content. */
    public ContentFilters       $filters;

    /**
     * Template path getter.
     * @return string
     */
    public function getTemplatePath(): string
    {
        return $this->template_path;
    }

    /**
     * Template path setter.
     * @param $path
     * @return void
     */
    public function setTemplatePath($path)
    {
        $this->template_path = $path;
    }
}