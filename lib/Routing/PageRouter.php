<?php

namespace Littled\Routing;

use Littled\API\APIRoute;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidCredentialsException;
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
     * @param string $route_path
     * @return void
     * @throws InvalidCredentialsException
     * @throws InvalidRouteException
     * @throws ResponseException
     */
    public static function dispatchRoute(string $route_path =''): void
    {
        try {
            $route_path = $route_path ?: static::collectRoute($route_path);

            $routeMap = RouteRegistry::lookup($route_path);
            if ($routeMap === null) {
                throw new InvalidRouteException("Unrecognized route \"$route_path\".");
            }

            if (!empty($routeMap->slug)) {
                $contentMap = ContentRegistry::lookup($routeMap->slug);
            } else {
                try {
                    $contentMap = ContentRegistry::lookupById(ContentRegistry::collectContentType());
                }
                catch (ContentValidationException) {
                    /** continue without a content type */
                }
            }

            $route = new $routeMap->class();

            if (isset($contentMap)) {

                // when available, register a content type and record for the api route to act on
                $content = new $contentMap->class();
                $recordId = $routeMap->collectRecordId($route_path);
                if ($recordId > 0) {
                    $content->setRecordId($recordId)->read();
                }

                $route->withConnection($content)
                    ->setContentTypeId($contentMap->id)
                    ->setContent($content);
            }

            // process the api request and send a response
            $route->processRequest()->sendResponse();
        }
        catch (InvalidCredentialsException|InvalidRouteException $ex) {
            throw $ex;
        }
        catch (LittledException $ex) {
            throw new ResponseException(message: $ex->getMessage(), previous: $ex)
                ->setFrontendError('An internal error occurred.');
        }
        catch (Throwable $ex) {
            throw new ResponseException(message: $ex->getMessage(), previous: $ex)
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