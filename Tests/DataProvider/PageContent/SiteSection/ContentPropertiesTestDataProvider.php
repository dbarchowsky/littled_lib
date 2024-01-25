<?php

namespace LittledTests\DataProvider\PageContent\SiteSection;

use LittledTests\PageContent\SiteSection\ContentPropertiesTest;

class ContentPropertiesTestDataProvider
{
    public static function hasDataTestDataProvider(): array
    {
        return array(
            [false, null, '[use defaults]'],
            [true, 893, null],
            [true, null, ContentPropertiesTest::UNIT_TEST_IDENTIFIER],
            [false, null, ''],
            [false, null, null],
            [true, 86425, 'foo'],
        );
    }
}