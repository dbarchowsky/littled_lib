<?php

namespace Littled\Routing;

use Littled\API\APIRoute;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidPropertyException;
use Littled\Exception\InvalidRouteException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Exception\ResponseException;
use Littled\Log\Log;
use Littled\PageContent\PageContent;
use Littled\Validation\Validation;
use Throwable;


class PageRouter
{
    protected static string $route_key= 'route';

    /**
     * Collects route value from request data.
     * @return string
     */
    public static function collectRouteFromRequest(): string
    {
        $route = Validation::collectStringRequestVar(static::getRouteKey());
        return $route ? ltrim($route, '/') : '';
    }

    /**
     * Dispatches route to a page instance that handles the request.
     * @param string $route
     * @return void
     * @throws InvalidRouteException
     * @throws ResponseException
     */
    public static function dispatchRoute(string $route=''): void
    {
        try {
            $route = $route ?: static::collectRouteFromRequest();
            static::getPageInstance($route)->processRequest();
        }
        catch (ConfigurationUndefinedException |
            InvalidPropertyException |
            ResourceNotFoundException $ex) {
            throw (new InvalidRouteException(message: $ex->getMessage(), previous: $ex))
                ->setFrontendError('Invalid route.');
        }
        catch (InvalidRouteException $ex) {
            throw $ex;
        }
        catch (Throwable $ex) {
            throw (new ResponseException(message: $ex->getMessage(), previous: $ex))
                ->setFrontendError('An internal error occurred while processing the request.');
        }
    }

    /**
     * Returns a page instance based on the route value.
     * @param string $route
     * @return PageContent|APIRoute
     * @throws InvalidRouteException
     * @noinspection PhpUnusedParameterInspection
     */
    public static function getPageInstance(string $route=''): PageContent|APIRoute
    {
        throw new InvalidRouteException(Log::getClassBaseName(static::class) . '::' . __FUNCTION__ . ' is not implemented.');
    }

    /**
     * Route key getter.
     * @return string
     */
    public static function getRouteKey(): string
    {
        return static::$route_key;
    }
}