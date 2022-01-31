<?php

namespace Littled\Tests\Database\DataProvider;

class AppContentBaseTestDataProvider
{
    public static function makePluralTestProvider(): array
    {
        return array(
            ['', ''],
            ['apple', 'apples'],
            ['box', 'boxes'],
            ['pony', 'ponies'],
            ['plurals', 'plurals'],
        );
    }
}