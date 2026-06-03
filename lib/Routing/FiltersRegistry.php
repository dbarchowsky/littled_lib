<?php

namespace Littled\Routing;


class FiltersRegistry extends ContentRegistry
{
    public static array $registry = [];

    public static function getFiltersClass(string|int $id): string
    {
        return static::getContentClass($id);
    }
}