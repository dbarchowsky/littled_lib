<?php

namespace Littled\PageContent;

use Littled\PageContent\Navigation\RoutePlaceholder;
use Littled\Utility\LittledUtility;

/**
 * @method formatRoutePath(?int $record_id = null): string
 * @method static formatRoutePath(?int $record_id = null): string
 */
trait RouteTrait
{
    /** @var RoutePlaceholder[] */
    public static array $placeholders;
    /** @var string[] */
    protected static array  $route_parts = [];

    /**
     * @param string $name
     * @param array $arguments
     * @return string|null
     */
    public function __call(string $name, array $arguments)
    {
        if ($name === 'formatRoutePath') {
            $record_id  = count($arguments) > 0 ? $arguments[0] : null;
            return $this->_formatRoutePath($record_id);
        }
        return null;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return string|null
     */
    public static function __callStatic(string $name, array $arguments)
    {
        if ($name === 'formatRoutePath') {
            $record_id  = count($arguments) > 0 ? $arguments[0] : null;
            return (new static())->_formatRoutePath($record_id);
        }
        return null;
    }

    /**
     * Formats and returns a path to use to reach this page.
     * @param int|null $record_id
     * @return string
     */
    public function _formatRoutePath(?int $record_id = null): string
    {
        $route_parts = static::$route_parts;
        if (($record_id ?? 0) === 0) {
            if (isset($this->content)) {
                $record_id = $this->content->getRecordId();
            }
        }
        if ($record_id > 0) {
            $route_parts = static::substituteRoutePart($route_parts, 'int', $record_id);
        }
        if (isset($this->content) && $this->content->getContentTypeSlug()) {
            $route_parts = static::substituteRoutePart($route_parts, 'str', $this->content->getContentTypeSlug());
        }
        $route = LittledUtility::joinPaths(...$route_parts);
        if ($route === '') {
            return $route;
        }
        return '/' . ltrim($route, '/');
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
     * Returns a list of wildcards for a given type.
     * @param string $type
     * @return string[]
     */
    protected static function getRouteWildcardsByType(string $type): array
    {
        $wc = [];
        if (!isset(static::$placeholders)) {
            static::initializePlaceholders();
        }
        foreach (static::$placeholders as $placeholder) {
            if ($placeholder->type === $type) {
                $wc[] = $placeholder->wildcard;
            }
        }
        return $wc;
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
     * Configures route placeholder values.
     * @return void
     */
    protected static function initializePlaceholders(): void
    {
        if (isset(static::$placeholders)) {
            return;
        }
        static::$placeholders = [
            (new RoutePlaceholder())
                ->setWildcard('%s')
                ->setPattern('/^(?=[a-zA-Z0-9\-_\.]*[A-Za-z])[a-zA-Z0-9\-_\.]+$/')
                ->setType('str'),
            (new RoutePlaceholder())
                ->setWildcard('%d')
                ->setPattern('/^\d+$/')
                ->setType('int'),
            (new RoutePlaceholder())
                ->setWildcard('#')
                ->setPattern('/^\d+$/')
                ->setType('int')
        ];
    }

    /**
     * Compares route parts with internal route parts, ignoring values that may have been inserted in place of
     * wildcard components of the route. Returns true if they match.
     * @param array $route
     * @param array|null $src
     * @return bool
     */
    public static function matchRouteParts(array $route, ?array $src=null): bool
    {
        $src ??= static::$route_parts;
        if (count($src) !== count($route)) {
            // a route with a different number of parts is automatically invalid
            return false;
        }
        if (!isset(static::$placeholders)) {
            static::initializePlaceholders();
        }
        foreach(static::$placeholders as $placeholder) {
            // a route is not allowed to contain placeholder values
            if (in_array($placeholder->wildcard, $route)) {
                return false;
            }
        }

        // pre-index by wildcard
        $placeholders = array_column(static::$placeholders, null, 'wildcard');

        for ($i = 0; $i < count($src); $i++) {
            $placeholder = $placeholders[$src[$i]] ?? null;
            if ($placeholder && preg_match($placeholder->pattern, $route[$i])) {
                $route[$i] = $placeholder->wildcard;
            }
        }
        return $src === $route;
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

    /**
     * Swap out route parts containing wildcards for supplied values.
     * @param string[] $route_parts
     * @param string $type
     * @param mixed $value
     * @return string[]
     */
    protected static function substituteRoutePart(array $route_parts, string $type, mixed $value): array
    {
        $wc = static::getRouteWildcardsByType($type);
        foreach ($route_parts as $i => $part) {
            if (in_array($part, $wc, true)) {
                $route_parts[$i] = $value;
            }
        }
        return $route_parts;
    }
}