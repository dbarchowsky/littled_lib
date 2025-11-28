<?php

namespace Littled\PageContent;

use Littled\Utility\LittledUtility;

trait RouteTrait
{
    /** @var string[] */
    protected static array  $route_parts = [];

    /**
     * Returns $route_parts property value as a string.
     * @return string
     */
    public static function formatRoute(): string
    {
        if (count(static::$route_parts) < 1) {
            return '';
        }
        return LittledUtility::joinPaths(...static::$route_parts);
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
     * Route parts getter.
     * @return array
     */
    public static function getRouteParts(): array
    {
        return (static::$route_parts ?? []);
    }

    /**
     * Returns one part of the route parts, the 2nd one by default.
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
     * Returns string that can be used to insert a page's route into HTML
     * @return string
     */
    public static function insertRoute(): string
    {
        return '/' . static::formatRoute();
    }

    /**
     * Base route setter.
     * @param string $route
     * @return void
     */
    public static function setBaseRoute(string $route): void
    {
        static::$route_parts = array($route);
    }

    /**
     * Route parts setter.
     * @param array $route
     * @return void
     */
    public static function setRouteParts(array $route): void
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
     * @param int $index Optional 0-based index of the component to assign the sub route value. Defaults to 1, i.e., the 2nd component in the route path.
     * @return void
     */
    public static function setSubRoute(string $sub_route, int $index=1): void
    {
        if (count(static::$route_parts) <= $index) {
            for ($i = count(static::$route_parts); $i <= $index; $i++) {
                static::$route_parts[] = '';
            }
        }
        static::$route_parts[$index] = $sub_route;
    }
}