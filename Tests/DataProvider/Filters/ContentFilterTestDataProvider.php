<?php

namespace Littled\Tests\DataProvider\Filters;

class ContentFilterTestDataProvider
{
	/** @var string */
	public const DEFAULT_LABEL = 'Test Filter';
	/** @var string */
	public const DEFAULT_KEY = 'filterKey';
	public string $expected;
    public $value;

    function __construct(?string $expected='', $value='')
    {
        $this->value = $value;
        $this->expected = $expected;
    }

    public function collectRequestValueTestProvider(): array
    {
        return array(
            array(null, 'p', [], []),
            array(12, 'p', array('p' => 12), []),
            array(13, 'p', [], array('p' => 13)),
            array(14, 'p', array('n' => 12, 'p' => 14, 'o' => 13), []),
            array(15, 'p', array('n' => 12, 'p' => 15, 'o' => 13), array('a' => 1, 'b' => 2)),
            array(null, 'p', array('n' => 12, 'p' => 16, 'o' => 13), array('a' => 1, 'b' => 2), []),
            array(17, 'p', array('n' => 12, 'p' => 16, 'o' => 13), array('a' => 1, 'b' => 2), array('p' => 17)),
        );
    }

    public static function escapeSQLTestProvider(): array
    {
        return array(
	        [new ContentFilterTestDataProvider('NULL', null)],
            [new ContentFilterTestDataProvider("''", '')],
            [new ContentFilterTestDataProvider("'foo'", 'foo')],
            [new ContentFilterTestDataProvider('1', true)],
            [new ContentFilterTestDataProvider('0', false)],
        );
    }

    public static function formatQueryStringTestProvider(): array
    {
        return array(
            array('', null),
            array('', ''),
            array('key=1', '1'),
            array('key=86', '86'),
            array('key=1', 1),
            array('key=845', 845),
            array('key=-12', -12),
            array('key=my+test', 'my test'),
            array('key=my+%26test', 'my &test'),
            array('key=1', true),
            array('key=0', false),
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