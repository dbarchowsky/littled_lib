<?php

namespace Littled\Routing;


use Littled\App\AppBase;
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
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
            $contentTypeId = Validation::collectIntegerRequestVar($key, null, $request_data);
            if ($contentTypeId > 0) {
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
        return array_find(static::$registry, fn($map) => $map->id === $type_id);
    }

    /**
     * Registers a content map.
     * @param ContentMap $map
     * @return void
     * @throws ConfigurationUndefinedException
     */
    public static function register(ContentMap $map): void
    {
        if (empty($map->slug)) {
            throw new ConfigurationUndefinedException('A content slug value was not provided.');
        }

        $slugs = is_array($map->slug) ? $map->slug : [$map->slug];
        foreach($slugs as $slug) {
            static::$registry[$slug] = clone $map->setSlug($slug);
        }
    }
}