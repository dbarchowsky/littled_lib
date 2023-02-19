<?php

namespace Littled\PageContent;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Filters\ContentFilters;
use Littled\Log\Log;
use Littled\PageContent\Navigation\RoutedPageContent;
use Littled\PageContent\Serialized\SerializedContent;
use Exception;
use ReflectionClass;
use ReflectionException;

abstract class ContentController
{
    const OPERATION_LISTINGS        = 'listings';
    const OPERATION_DETAILS         = 'details';
    const OPERATION_EDIT            = 'edit';

    /**
     * Returns a navigation route for a given SiteSection page type and operation.
     * @param RoutedPageContent $class
     * @param string $operation
     * @param int|null $record_id
     * @return string
     * @throws InvalidValueException
     * @throws NotImplementedException
     */
    protected static function formatNavigationRoute(RoutedPageContent $class, string $operation, ?int $record_id=null): string
    {
        switch ($operation) {
            case self::OPERATION_LISTINGS;
                return call_user_func([$class, 'getListingsURI']);
            case self::OPERATION_DETAILS;
                if ($record_id===null || $record_id < 1) {
                    throw new InvalidValueException('Record id not provided.');
                }
                return call_user_func_array([$class, 'getDetailsURI'], array($record_id));
            case self::OPERATION_EDIT:
                return call_user_func_array([$class, 'getEditURI'], array($record_id));
            default:
                throw new NotImplementedException('Unrecognized operation.');
        }
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
     * Returns the name of the content class matching the content type id passed to the method.
     * Classes implementing this routine will return the values depending on the value of $content_id.
     * @param int $content_id Content type to match with filter type.
     * @returns string
     * @throws Exception
     */
    abstract public static function getContentClass(int $content_id): string;

    /**
     * Sets the filters object to the appropriate type based on the value of $content_id.
     * @param int $content_id Content type to match with filter type.
     * @return string
     * @throws Exception
     */
    public abstract static function getContentFiltersClass(int $content_id): string;

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
     * @return SerializedContent
     * @throws Exception
     */
    public static function getContentObject(int $content_id): SerializedContent
    {
        // load objects used to fill out listings markup
        $class = static::getContentClass($content_id);
        try {
            $rc = new ReflectionClass($class);
        }
        catch(ReflectionException $ex) {
            throw new InvalidTypeException("Could not create instance of $class.");
        }
        /** @var SerializedContent $content */
        $content = $rc->newInstance();
        // returning variable because return value of newInstance() is object and method's return type is SectionContent
        return $content;
    }

    /**
     * Returns a route for a given site section and operation combination.
     * @param int $site_section_id Record id of the site section to look up.
     * @param string $operation Operation to look up.
     * @param ?int $record_id (Optional) Record id to inject into the route.
     * @return string
     */
    abstract public static function getNavigationRoute(int $site_section_id, string $operation, ?int $record_id=null): string;

	/**
	 * Returns the name of the PageContent class matching the content type id passed to the method.
	 * Classes implementing this routine will return the values depending on the value of $content_id.
	 * @param int $content_id Content type to match with filter type.
	 * @returns string
	 * @throws Exception
     * @deprecated Use getRoutedPageContent() instead
	 */
	abstract public static function getPageContentClass(int $content_id): string;

    /**
     * Returns the name of a RoutedPageContent class appropriate to serve a response matching the requested route represented by the $route_parts argument.
     * @param array $route_parts The route that has been requested, exploded into its parts.
     * @return string The name of the matching RoutedPageContent class.
     * @throws InvalidTypeException
     */
    abstract public static function getRoutedPageContentClass(array $route_parts): string;

    /**
     * Returns a RoutedPageContent instance appropriate to serve a response matching the requested route represented by the $route_parts argument.
     * @param array $route_parts The route that has been requested, exploded into its parts.
     * @return RoutedPageContent
     * @throws InvalidTypeException
     */
    public static function getRoutedPageInstance(array $route_parts): RoutedPageContent
    {
        $class = static::getRoutedPageContentClass($route_parts);
        if (!class_exists($class)) {
            throw new InvalidTypeException("Invalid routed page content class: \"".basename($class)."\".");
        }
        return new $class();
    }

    /**
     * Default action is to load content property values from the database. This method can instead be overridden to
     * perform different actions depending on content type. E.g. replace the default action with a switch statement
     * evaluating the content type id value.
     * @param SerializedContent $content
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     */
    public static function retrieveContentDataByType(SerializedContent $content)
    {
        if (1 > $content->id->value) {
            throw new ConfigurationUndefinedException("[".Log::getShortMethodName()."] A record was not specified for retrieval.");
        }
        $content->read();
    }
}