<?php

namespace Littled\Tests\DataProvider\Request;

class RequestInputTestDataProvider
{
    /** @var string */
    public const TEST_LABEL = 'My Request Input';
    /** @var string */
    public const TEST_KEY = 'requestKey';

    public static function escapeSQLTestProvider(): array
    {
        return array(
            array(null, '[use default]'),
            array(null, '[use default]', false),
            array(null, null),
            array(null, null, false),
            array("''", ''),
            array('', '', false),
            array("'foobar'", 'foobar'),
            array("foobar", 'foobar', false),
            array("'122'", 122),
            array("'67.23'", 67.23),
            array("'0'", false),
            array("'1'", true),
            array("'0'", 0),
            array("'1'", 1),
        );
    }

    public static function formatClassAttributeTestProvider(): array
    {
        return array(
            [''],
            [' class="my-class"', 'my-class'],
            [' class="my-class custom-class"', 'my-class', 'custom-class'],
            [' class="my-class input-error"', 'my-class', '', true],
            [' class="input-error"', '', '', true],
            [' class="my-class custom-class input-error"', 'my-class', 'custom-class', true],
            [' class="my-class"', 'my-class', '', false, 'input'],
            [' class="my-container-class"', 'my-container-class', '', false, 'container'],
        );
    }
}