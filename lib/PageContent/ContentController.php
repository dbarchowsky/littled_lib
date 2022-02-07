<?php

namespace Littled\PageContent;

use Littled\Filters\ContentFilters;
use Littled\PageContent\SiteSection\SectionContent;
use Exception;
use ReflectionClass;
use ReflectionException;

abstract class ContentController
{
    /**
     * Returns the name of the content class matching the content type id passed to the method.
     * Classes implementing this routine will return the values depending on the value of $content_id.
     * @param int $content_id Content type to match with filter type.
     * @returns string
     * @throws Exception
     */
    abstract public static function getContentClass(int $content_id): string;

    /**
     * @param int $content_id
     * @return ContentFilters
     * @throws Exception
     */
    public static function getContentFiltersObject(int $content_id): ContentFilters
    {
        // load objects used to fill out listings markup
        $class = static::getContentFiltersClass($content_id);
        try {
            $rc = new ReflectionClass($class);
        }
        catch(ReflectionException $ex) {
            throw new Exception("Could not create instance of $class.");
        }
        /** @var ContentFilters $filters */
        $filters = $rc->newInstance();
        // returning variable instead return value of newInstance() is more reliable
        return $filters;
    }

    /**
     * @param int $content_id
     * @return SectionContent
     * @throws Exception
     */
    public static function getContentObject(int $content_id): SectionContent
    {
        // load objects used to fill out listings markup
        $class = static::getContentClass($content_id);
        try {
            $rc = new ReflectionClass($class);
        }
        catch(ReflectionException $ex) {
            throw new Exception("Could not create instance of $class.");
        }
        /** @var SectionContent $content */
        $content = $rc->newInstance();
        // returning variable because return value of newInstance() is object and method's return type is SectionContent
        return $content;
    }

    /**
     * Sets the filters object to the appropriate type based on the value of $content_id.
     * @param int $content_id Content type to match with filter type.
     * @returns array
     * @throws Exception
     */
    public static function getContentAndFiltersClasses(int $content_id): array
    {
        $content_class = static::getContentClass($content_id);
        $filters_class = static::getContentFiltersClass($content_id);
        return array($content_class, $filters_class);
    }

    /**
     * Sets the filters object to the appropriate type based on the value of $content_id.
     * @param int $content_id Content type to match with filter type.
     * @return string
     * @throws Exception
     */
    public abstract static function getContentFiltersClass(int $content_id): string;
}