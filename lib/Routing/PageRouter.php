<?php

namespace Littled\Routing;

use Littled\API\APIRoute;
use Littled\Exception\InvalidRouteException;
use Littled\Exception\LittledException;
use Littled\Exception\ResponseException;
use Littled\Log\Log;
use Littled\PageContent\PageContent;
use Littled\Validation\Validation;
use Throwable;


class PageRouter
{
    protected static string $route_key= 'route';

    /**
     * Collects and formats the route string.
     * @param string $route
     * @param array|null $request_data
     * @return string
     */
    public static function collectRoute(string $route='', ?array $request_data = null): string
    {
        $route = $route ?: static::collectRouteFromRequest($request_data);
        if (empty($route)) {
            return $_SERVER['REQUEST_URI'] ?? '/';
        }
        return '/' . ltrim($route, '/');
    }

    /**
     * Collects route value from request data.
     * @param array|null $request_data
     * @return string
     */
    public static function collectRouteFromRequest(?array $request_data = null): string
    {
        $route = Validation::collectStringRequestVar(key: static::getRouteKey(), src: $request_data);
        return $route ? ltrim($route, '/') : '';
    }

    /**
     * Dispatches route to a page instance that handles the request.
     * @param string $route
     * @return void
     * @throws InvalidRouteException
     * @throws ResponseException
     */
    public static function dispatchRoute(string $route =''): void
    {
        try {
            $route = $route ?: static::collectRoute($route);

            $routeMap = RouteRegistry::lookup($route);
            if ($routeMap === null) {
                throw new InvalidRouteException("Unrecognized route \"$route\".");
            }

            if (empty($routeMap->slug)) {
                $contentMap = ContentRegistry::lookupById(ContentRegistry::collectContentType());
            } else {
                $contentMap = ContentRegistry::lookup($routeMap->slug);
            }

            $content = new $contentMap->class();
            $recordId = $routeMap->collectRecordId($route);
            if ($recordId > 0) {
                $content->setRecordId($recordId)->read();
            }

            (new $routeMap->class())
                ->withConnection($content)
                ->setContentTypeId($contentMap->id)
                ->setContent($content)
                ->processRequest()
                ->sendResponse();
        }
        catch (InvalidRouteException $ex) {
            throw $ex;
        }
        catch (LittledException $ex) {
            throw (new InvalidRouteException(message: $ex->getMessage(), previous: $ex))
                ->setFrontendError('Invalid route.');
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