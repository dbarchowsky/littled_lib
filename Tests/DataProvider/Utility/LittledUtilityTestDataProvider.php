<?php
namespace Littled\Tests\DataProvider\Utility;


class LittledUtilityTestDataProvider
{
	public static function joinPathPartsTestProvider(): array
	{
		return array(
			['', array('','')],
			['', array('','','')],
			['/test', array('test')],
			['/path/to-test', array('path', 'to-test')],
			['/path/to/test', array('path', 'to', 'test')],
			['/path/to/test.txt', array('path', 'to', 'test.txt')],
			['/path/to-test', array('path/', 'to-test')],
			['/path/to-test', array('/path/', 'to-test')],
			['/path/to-test', array('/path/', '/to-test')],
			['/path/to-test/', array('/path/', '/to-test/')],
			['/path/to-test/', array('/path', '/to-test/')],
			['/path/to-test/', array('path', '/to-test/')],
			['/path/to/test', array('path/', 'to', 'test')],
			['/path/to/test', array('path/', '/to/', 'test')],
			['/path/to/test', array('path/', '/to/', '/test')],
			['/path/to/test/', array('/path/', '/to/', '/test/')],
			['/path/to/test/', array('/path', 'to', 'test/')],
			['/path/to/test/', array('/path/', 'to', '/test/')],
		);
	}

	public static function joinPathsTestProvider(): array
	{
		return array(
			array('', array('', '')),
			array('/', array('', '/')),
			array('/a', array('/', 'a')),
			array('/a', array('/', '/a')),
			array('abc/def', array('abc', 'def')),
			array('abc/def', array('abc', '/def')),
			array('/abc/def', array('/abc', 'def')),
			array('/abc/def', array('/abc/', '/def')),
			array('abc/def/', array('abc', 'def/')),
			array('foo.jpg', array('', 'foo.jpg')),
			array('dir/0/a.jpg', array('dir', '0', 'a.jpg')),
		);
	}

    public static function overlapTestProvider(): array
    {
        return array(
            array('first string', 'my first string', 'first string then some'),
            array(' first string ', 'my first string bro', 'your first string homie'),
            array('abcd', 'abcd9876', 'abcd12345'),
            array('12345', 'zxyw12345', 'abcd12345'),
            array('12345', 'zxyw12345', 'abcd12345a'),
            array('12345', 'zxyw12345a', 'abcd12345'),
            array('', 'no overlap', '78549449')
        );
    }

    public static function stripPathLevelsTestProvider(): array
    {
        return array(
            ['/path/to/some/lower/directory/foo/', '/path/to/some/lower/directory/foo/bar/', 1, 'levels: 1'],
            ['/path/to/some/lower/', '/path/to/some/lower/directory/foo/bar/', 3, 'levels: 3'],
            ['', '/path/to/foo/', 4, 'overrun'],
            ['/path/', '/path/to/some/lower/directory/foo/bar/', 6, 'levels: 6'],
            ['/path/to/some/', 'path/to/some/lower/directory/foo/bar', 4, 'no leading or trailing slashes'],
            ['/path/to/some/lower/', '/path/to/some/lower/directory/foo/bar', 3, 'no trailing slash'],
            ['/path/to/some/lower/directory/', 'path/to/some/lower/directory/foo/bar/', 2, 'no leading slash'],
        );
    }
}