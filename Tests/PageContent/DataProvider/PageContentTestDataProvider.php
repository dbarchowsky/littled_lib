<?php

namespace Littled\Tests\PageContent\DataProvider;

class PageContentTestDataProvider
{
    public static function getRecordIdProvider(): array
    {
        return array(
            [null, null],
            [0, null],
            [45, 45]
        );
    }
}
