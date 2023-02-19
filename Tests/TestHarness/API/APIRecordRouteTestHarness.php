<?php
namespace Littled\Tests\TestHarness\API;

use Littled\API\APIRecordRoute;
use Littled\PageContent\PageContent;


class APIRecordRouteTestHarness extends APIRecordRoute
{
    public function newRoutedPageContentInstance(): PageContent
    {
        return parent::newRoutedPageContentInstance();
    }
}