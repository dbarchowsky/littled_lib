<?php

namespace Littled\Tests\PageContent\TestHarness;

use Littled\PageContent\ContentController;
use Littled\Tests\Ajax\AjaxPageTest;

class ContentControllerTestHarness extends ContentController
{
    /**
     * @inheritDoc
     */
    public static function getContentClass(int $content_id): string
    {
        switch($content_id) {
            case AjaxPageTest::TEST_CONTENT_TYPE_ID:
                return 'Littled\Tests\PageContent\Serialized\TestHarness\TestTable';
            default:
                return 'TestContentClassString';
        }
    }

    /**
     * @inheritDoc
     */
    public static function getContentFiltersClass(int $content_id): string
    {
        switch($content_id) {
            case AjaxPageTest::TEST_CONTENT_TYPE_ID:
                return 'Littled\Tests\Filters\TestHarness\TestTableFilters';
            default:
                return 'TestContentClassString';
        }
    }
}