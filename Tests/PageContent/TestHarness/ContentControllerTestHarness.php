<?php

namespace Littled\Tests\PageContent\TestHarness;

use Exception;
use Littled\PageContent\ContentController;

class ContentControllerTestHarness extends ContentController
{
    /**
     * @inheritDoc
     */
    public static function getContentClass(int $content_id): string
    {
        return 'TestContentClassString';
    }

    /**
     * @inheritDoc
     */
    public static function getContentFiltersClass(int $content_id): string
    {
        return 'TestContentFiltersClassString';
    }
}