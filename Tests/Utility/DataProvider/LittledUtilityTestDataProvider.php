<?php

namespace Littled\Tests\Utility\DataProvider;


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
}