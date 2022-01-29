<?php

namespace Littled\Tests\Filters\DataProvider;

class ContentFilterTestDataProvider
{
    /** @var mixed */
    public $value;
    /** @var string */
    public $expected;

    function __construct($value='', ?string $expected='')
    {
        $this->value = $value;
        $this->expected = $expected;
    }

    public static function escapeSQLTestProvider(): array
    {
        return array(
            [new ContentFilterTestDataProvider(null, 'null')],
            [new ContentFilterTestDataProvider('', "''")],
            [new ContentFilterTestDataProvider('foo', "'foo'")],
            [new ContentFilterTestDataProvider(true, '1')],
            [new ContentFilterTestDataProvider(false, '0')],
        );
    }

    public static function safeValueTestProvider(): array
    {
        return array(
            [new ContentFilterTestDataProvider(null, '')],
            [new ContentFilterTestDataProvider('', '')],
            [new ContentFilterTestDataProvider('plain text', 'plain text')],
            [new ContentFilterTestDataProvider('<script>alert(\'hello\');</script>', 'alert(\'hello\');')],
        );
    }
}