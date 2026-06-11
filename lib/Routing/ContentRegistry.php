<?php

namespace Littled\Routing;


use Littled\App\AppBase;
use Littled\App\LittledGlobals;
use Littled\Exception\ContentValidationException;
use Littled\PageContent\SiteSection\ContentProperties;
use Littled\Validation\Validation;

class ContentRegistry
{
    /** @var ContentMap[] */
    public static array $registry = [];

    /**
     * Extracts the content type id from the request or route.
     * @param array|null $request_data
     * @param string $slug
     * @param string $key
     * @return int
     * @throws ContentValidationException
     */
    public static function collectContentType(?array $request_data = null, string $slug='', string $key=''): int
    {
        // first check route for content slug
        if ($contentMap = static::lookup($slug)) {
            return $contentMap->id;
        }

        // fall back to request data
        $request_data ??= AppBase::getAjaxRequestData() ?: $_POST ?: [];

        $keys = [LittledGlobals::CONTENT_TYPE_KEY, ContentProperties::ID_KEY];
        if ($key) {
            array_unshift($keys, $key);
        }

        foreach($keys as $key) {
            if ($contentTypeId = Validation::collectIntegerRequestVar($key, null, $request_data)) {
                return $contentTypeId;
            }
        }
        throw new ContentValidationException('Content type not available.');
    }

    /**
     * Returns the class name for a content type.
     * @param string|int $type_id
     * @return string
     */
    public static function getContentClass(string|int $type_id): string
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
     * @param string $slug
     * @return ContentMap|null
     */
    public static function lookup(string $slug): ?ContentMap
    {
        return static::$registry[$slug] ?? null;
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