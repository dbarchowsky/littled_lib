<?php

namespace Littled\Tests\DataProvider\Database;

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