<?php
namespace Littled\Tests\TestHarness\API;

use Littled\API\APIRecordPage;
use Littled\PageContent\PageContent;


class APIRecordPageTestHarness extends APIRecordPage
{
    public function newRoutedPageContentInstance(): PageContent
    {
        return parent::newRoutedPageContentInstance();
    }
}