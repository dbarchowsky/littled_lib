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
    protected static array      $route_parts=[];

    /**
     * Base route getter.
     * @return string
     */
    public static function getBaseRoute(): string
    {
        return ((count(static::$route_parts) > 0) ? (static::$route_parts[0]) :(''));
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
        static::$route_parts[$index] = ''.$sub_route;
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