<?php

namespace Littled\Routing;


class ContentRegistry
{
    public static array $registry = [];

    /**
     * Returns the class name for a content type.
     * @param string|int $type_id
     * @return string
     */
    public static function getClass(string|int $type_id): string
    {
        if (is_numeric($type_id)) {
            $map = static::lookupById((int)$type_id);
        } else {
            $map = static::lookup($type_id);
        }
        return $map->class ?? '';
    }

    /**
     * Returns a content map by name.
     * @param string $name
     * @return ContentMap|null
     */
    public static function lookup(string $name): ?ContentMap
    {
        return static::$registry[$name] ?? null;
    }

    /**
     * Returns a content map by id.
     * @param int $type_id
     * @return ContentMap|null
     */
    public static function lookupById(int $type_id): ?ContentMap
    {
        foreach (static::$registry as $map) {
            if ($map->id === $type_id) {
                return $map;
            }
        }
        return null;
    }

    /**
     * Registers a content map.
     * @param ContentMap $map
     * @return void
     */
    public static function register(ContentMap $map): void
    {
        static::$registry[$map->slug] = $map;
    }
}