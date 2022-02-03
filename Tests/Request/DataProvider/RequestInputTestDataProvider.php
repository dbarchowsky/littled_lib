<?php

namespace Littled\Tests\Request\DataProvider;

class RequestInputTestDataProvider
{
    /** @var string */
    public const TEST_LABEL = 'My Request Input';
    /** @var string */
    public const TEST_KEY = 'requestKey';

    public static function formatClassAttributeTestProvider(): array
    {
        return array(
            [''],
            [' class="my-class"', 'my-class'],
            [' class="my-class custom-class"', 'my-class', 'custom-class'],
            [' class="my-class input-error"', 'my-class', '', true],
            [' class="input-error"', '', '', true],
            [' class="my-class custom-class input-error"', 'my-class', 'custom-class', true],
        );
    }
}