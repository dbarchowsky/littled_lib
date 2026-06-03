<?php

namespace Littled\Routing;


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

    public static function lookup(string $route): ?RouteMap
    {
        foreach (static::$registry as $map) {
            if ($map->matches($route)) {
                return $map;
            }
        }
        return null;
    }

    public static function register(RouteMap $map): void
    {
        static::$registry[] = $map;
    }
}