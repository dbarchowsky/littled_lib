<?php

namespace Littled\Routing;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidRouteException;


class RouteRegistry
{
    /** @var RouteMap[] */
    public static array $registry = [];

    /**
     * Returns the class name for a route.
     * @param string $route
     * @return string
     * @throws InvalidRouteException
     */
    public static function getRouteClass(string $route): string
    {
        $map = static::lookup($route);
        if ($map === null) {
            throw new InvalidRouteException("Unrecognized route \"$route\".");
        }
        return $map->class;
    }

    /**
     * Returns the route map for a route.
     * @param string $route
     * @return RouteMap|null
     */
    public static function lookup(string $route): ?RouteMap
    {
        return array_find(static::$registry, fn($map) => $map->matches($route));
    }

    /**
     * Register a map between a route pattern and the class used to perform the business logic for the route.
     * @param RouteMap $map
     * @return void
     * @throws ConfigurationUndefinedException
     */
    public static function register(RouteMap $map): void
    {
        if (empty($map->pattern)) {
            throw new ConfigurationUndefinedException('A pattern was not provided.');
        }

        $patternList = is_array($map->pattern) ? $map->pattern : [$map->pattern];
        foreach($patternList as $pattern) {
            static::$registry[] = clone $map->setPattern($pattern);
        }
    }
}