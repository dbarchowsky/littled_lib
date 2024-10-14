<?php

namespace Littled\PageContent;

use JetBrains\PhpStorm\NoReturn;
use Littled\API\APIRoute;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidRouteException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Filters\ContentFilters;
use Littled\Log\Log;
use Littled\PageContent\Navigation\RoutedPageContent;
use Littled\PageContent\Serialized\SerializedContent;
use Exception;
use Littled\Validation\Validation;
use ReflectionClass;
use ReflectionException;
use mysqli;

abstract class ContentController
{
    const OPERATION_LISTINGS = 'listings';
    const OPERATION_DETAILS = 'details';
    const OPERATION_EDIT = 'edit';

    /**
     * Returns a navigation route for a given SiteSection page type and operation.
     * @param RoutedPageContent $class
     * @param string $operation
     * @param int|null $record_id
     * @return string
     * @throws InvalidValueException
     * @throws NotImplementedException
     */
    protected static function formatNavigationRoute(RoutedPageContent $class, string $operation, ?int $record_id = null): string
    {
        switch ($operation) {
            case self::OPERATION_LISTINGS;
                return call_user_func([$class, 'getListingsURI']);
            case self::OPERATION_DETAILS;
                if ($record_id === null || $record_id < 1) {
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
     * Returns a fully-qualified name of an APIRoute class corresponding to the given route components.
     * @param array $route_parts
     * @return string
     * @throws InvalidRouteException
     */
    public static function getAPIRouteClassName(array $route_parts): string
    {
        /* Implement in child class. Cannot declare the method abstract as it's called in this class. */
        if (count($route_parts) < 1) {
            throw new InvalidRouteException('A route was not provided.');
        }
        return '';
    }

    /**
     * Returns an APIRoute class corresponding ot the given route components.
     * @throws InvalidTypeException|InvalidRouteException
     */
    public static function getAPIRouteInstance(array $route_parts): APIRoute
    {
        $class = static::getAPIRouteClassName($route_parts);
        if (!class_exists($class)) {
            throw new InvalidTypeException('bad class');
        }
        return new $class();
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
    public static function getContentClass(int $content_id): string
    {
        /** Implement in child class. Cannot declare the method abstract as it's called within this class. */
        if ($content_id < 1) {
            throw new InvalidTypeException('Unrecognized content type.');
        }
        return '';
    }

    /**
     * Sets the filters object to the appropriate type based on the value of $content_id.
     * @param int $content_id Content type to match with filter type.
     * @return string
     * @throws InvalidTypeException
     */
    public static function getContentFiltersClass(int $content_id): string
    {
        /** Implement in child class. Cannot declare the method abstract as it's called within this class. */
        if ($content_id < 1) {
            throw new InvalidTypeException('Unrecognized content type.');
        }
        return '';
    }

    /**
     * Returns an object derived from ContentFilters appropriate to the content type represented by $content_id
     * @param int $content_id Content type identifier
     * @param ?mysqli $mysqli Database connection
     * @return ContentFilters
     * @throws ConfigurationUndefinedException
     * @throws InvalidTypeException
     */
    public static function getContentFiltersObject(int $content_id, ?mysqli $mysqli=null): ContentFilters
    {
        // load objects used to fill out listings markup
        $class = static::getContentFiltersClass($content_id);
        if (!class_exists($class) || !Validation::isSubclass($class, ContentFilters::class)) {
            throw new InvalidTypeException('Invalid content filters class: ' . $class);
        }
        $filters = new $class(mysqli: $mysqli);
        if ($mysqli !== null) {
            $filters->setMySQLi($mysqli);
        }

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
        } catch (ReflectionException) {
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
    abstract public static function getNavigationRoute(int $site_section_id, string $operation, ?int $record_id = null): string;

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
     * @param bool $send_404 (Optional) Flag indicating to send a 404 response if the route is invalid.
     * @return string The name of the matching RoutedPageContent class.
     * @throws InvalidRouteException
     */
    public static function getRoutedPageContentClass(array $route_parts, bool $send_404 = true): string
    {
        if (count($route_parts) < 1) {
            if ($send_404) {
                static::send404ResponseAndExit();
            }
            throw new InvalidRouteException('A route was not provided.');
        }
        return '';
    }

    /**
     * Returns a RoutedPageContent instance appropriate to serve a response matching the requested route represented by the $route_parts argument.
     * @param array $route_parts The route that has been requested, exploded into its parts.
     * @return RoutedPageContent
     * @throws InvalidTypeException|InvalidRouteException
     */
    public static function getRoutedPageInstance(array $route_parts): RoutedPageContent
    {
        $class = static::getRoutedPageContentClass($route_parts);
        if (!class_exists($class)) {
            throw new InvalidTypeException("Invalid routed page content class: \"" . basename($class) . "\".");
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
     * @throws InvalidQueryException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     * @throws InvalidValueException
     */
    public static function retrieveContentDataByType(SerializedContent $content): void
    {
        if (1 > $content->id->value) {
            throw new ConfigurationUndefinedException('[' . Log::getShortMethodName() . '] A record was not specified for retrieval.');
        }
        $content->read();
    }

    /**
     * Respond to request with 404 error
     * @return void
     */
    #[NoReturn] public static function send404ResponseAndExit(): void
    {
        http_response_code(404);
        exit;
    }
}