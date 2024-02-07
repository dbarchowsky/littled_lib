<?php
namespace Littled\PageContent;

use Littled\Database\MySQLConnection;
use Littled\Filters\ContentFilters;


abstract class PageContentBase extends MySQLConnection implements PageContentInterface
{
    /** @var ContentFilters     Filters to apply to page content. */
    public ContentFilters       $filters;
    /** @var string             Query string to attach to page links. */
    protected string            $query_string = '';
    protected static array      $route_parts=[];
    /** @var string             Path to template file. */
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
     * Base route getter.
     * @return string
     */
    public static function getBaseRoute(): string
    {
        return ((count(static::$route_parts) > 0) ? (static::$route_parts[0]) :(''));
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
     * Route parts getter.
     * @return array
     */
    public static function getRouteParts(): array
    {
        return (static::$route_parts ?? []);
    }

    /**
     * Returns one component of the route parts, the 2nd one by default.
     * @param int $index
     * @return string
     */
    public static function getSubRoute( int $index=1 ): string
    {
        if (count(static::$route_parts) > $index) {
            return static::$route_parts[$index];
        }
        return '';
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
     * Base route setter.
     * @param string $route
     * @return void
     */
    public static function setBaseRoute(string $route)
    {
        static::$route_parts = array($route);
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
     * Route parts setter.
     * @param array $route
     * @return void
     */
    public static function setRouteParts(array $route)
    {
        static::$route_parts = array_values(array_map(
            function($n) {
                return ''.$n;
            },
            $route));
    }

    /**
     * Sets a sub-route component of the object's route path.
     * @param string $sub_route Value to assign to the route component.
     * @param int $index Optional 0-based index of the component to assign the sub route value. Defaults to 1, i.e. the 2nd component in the route path.
     * @return void
     */
    public static function setSubRoute(string $sub_route, int $index=1)
    {
        if (count(static::$route_parts) <= $index) {
            for ($i = count(static::$route_parts); $i <= $index; $i++) {
                static::$route_parts[] = '';
            }
        }
        static::$route_parts[$index] = $sub_route;
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