<?php

namespace Littled\Tests\Ajax\DataProvider;

use Error;
use Littled\Exception\InvalidTypeException;

class AjaxPageTestDataProvider
{
    public static function setCacheClassTestProvider(): array
    {
        return array(
            ['', '\Littled\Tests\PageContent\Cache\TestHarness\ContentCacheTestHarness'],
            ['', 'Littled\Tests\PageContent\Cache\TestHarness\ContentCacheTestHarness'],
            [Error::class, '\Littled\PageContent\Cache\ContentCache'],
            [InvalidTypeException::class, '\Littled\PageContent\PageConfig'],
        );
    }

    public static function setControllerClassTestProvider(): array
    {
        return array(
            ['', '\Littled\Tests\PageContent\TestHarness\ContentControllerTestHarness'],
            ['', 'Littled\Tests\PageContent\TestHarness\ContentControllerTestHarness'],
            [Error::class, '\Littled\PageContent\ContentController'],
            [InvalidTypeException::class, '\Littled\PageContent\PageConfig'],
        );
    }
}