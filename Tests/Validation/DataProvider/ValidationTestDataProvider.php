<?php

namespace Littled\Tests\Validation\DataProvider;

class ValidationTestDataProvider
{
    public static function parseIntegerTestProvider(): array
    {
        return array(
            [1, 1, 'starting value: 1'],
            [0, 0, 'starting value: 0'],
            [-1, -1, 'starting value: -1'],
            [1, '1', 'starting value: "1"'],
            [0, '0', 'starting value: "0'],
            [-1, '-1', 'starting value: "-1"'],
            [null, '-', 'starting value: "-"'],
            [null, 'true', 'starting value: "true"'],
            [null, 'false', 'starting value: "false"'],
            [null, true, 'starting value: true'],
            [null, false, 'starting value: false'],
            [5, 4.5, 'starting value: 4.5'],
            [5, '4.5', 'starting value: "4.5"'],
            [4, 4.49, 'starting value: 4.49'],
            [5, 4.51, 'starting value: 4.51'],
            [null, null, 'starting value: null'],
            [null, 'funky chicken', 'starting value: "funky chicken"'],
        );
    }

    public static function isIntegerTestProvider(): array
    {
        return array(
            [true, 1],
            [true, 0],
            [true, -1],
            [true, '1'],
            [true, '0'],
            [true, '58'],
            [true, 87],
            [false, '-'],
            [false, 'true'],
            [false, 'false'],
            [false, true],
            [false, false],
            [false, 4.5],
            [false, '4.5'],
            [false, null],
        );
    }

    public static function parseNumericTestProvider(): array
    {
        return array(
            [1, '1'],
            [0, '0'],
            [-1, '-1'],
            [5, '5'],
            [PHP_INT_MAX, ''.PHP_INT_MAX],
            [0.01, '0.01'],
            [4.5, '4.5'],
            [null, 'zero'],
            [null, 'j01'],
            [null, '01jx'],
            [null, '0x020'],
            [null, 'true'],
            [null, 'false'],
            [null, true],
            [null, false],
        );
    }
}