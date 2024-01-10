<?php
namespace LittledTests\TestHarness\API;

use Littled\API\APIRecordRoute;
use Littled\API\APIRoute;
use Littled\PageContent\PageContent;
use LittledTests\TestHarness\PageContent\ContentControllerTestHarness;


class APIRecordRouteTestHarness extends APIRecordRoute
{
    protected static array $route_parts = ['api', 'test'];
    protected static string $controller_class = ContentControllerTestHarness::class;

    /**
     * @inheritDoc
     * Override parent method to provide public interface for testing.
     */
    public function newAPIRouteInstance(): APIRoute
    {
        return parent::newAPIRouteInstance();
    }
}