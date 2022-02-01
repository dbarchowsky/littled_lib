<?php

namespace Littled\Tests\Filters\DataProvider;

class ContentFilterTestDataProvider
{
	/** @var string */
	public const DEFAULT_LABEL = 'Test Filter';
	/** @var string */
	public const DEFAULT_KEY = 'filterKey';
	/** @var string */
	public $expected;
    /** @var mixed */
    public $value;

    function __construct(?string $expected='', $value='')
    {
        $this->value = $value;
        $this->expected = $expected;
    }

    public static function escapeSQLTestProvider(): array
    {
        return array(
	        [new ContentFilterTestDataProvider('NULL', $value=null)],
            [new ContentFilterTestDataProvider("''", '')],
            [new ContentFilterTestDataProvider("'foo'", 'foo')],
            [new ContentFilterTestDataProvider('1', true)],
            [new ContentFilterTestDataProvider('0', false)],
        );
    }

    public static function safeValueTestProvider(): array
    {
        return array(
            [new ContentFilterTestDataProvider('', null)],
            [new ContentFilterTestDataProvider('', '')],
            [new ContentFilterTestDataProvider('plain text', 'plain text')],
            [new ContentFilterTestDataProvider('alert(\'hello\');', '<script>alert(\'hello\');</script>')],
        );
    }

	public static function saveInFormTestProvider(): array
	{
		return array(
			[new ContentFilterTestDataProvider('/<input type=\"hidden\" name=\"'.self::DEFAULT_KEY.'\" value=\"\" \/>/', '')],
			[new ContentFilterTestDataProvider('/<input type=\"hidden\" name=\"'.self::DEFAULT_KEY.'\" value=\"\" \/>/', null)],
			[new ContentFilterTestDataProvider('/<input type=\"hidden\" name=\"'.self::DEFAULT_KEY.'\" value=\"1\" \/>/', 1)],
			[new ContentFilterTestDataProvider('/<input type=\"hidden\" name=\"'.self::DEFAULT_KEY.'\" value=\"1\" \/>/', '1')],
			[new ContentFilterTestDataProvider('/<input type=\"hidden\" name=\"'.self::DEFAULT_KEY.'\" value=\"0\" \/>/', 0)],
			[new ContentFilterTestDataProvider('/<input type=\"hidden\" name=\"'.self::DEFAULT_KEY.'\" value=\"0\" \/>/', '0')],
			[new ContentFilterTestDataProvider('/<input type=\"hidden\" name=\"'.self::DEFAULT_KEY.'\" value=\"foo\" \/>/', 'foo')],
		);
	}
}