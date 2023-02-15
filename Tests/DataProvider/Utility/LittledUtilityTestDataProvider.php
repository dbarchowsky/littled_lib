<?php

namespace Littled\Tests\DataProvider\Utility;


use Littled\API\AjaxPage;
use Littled\App\AppBase;
use Littled\Filters\ContentFilters;
use Littled\PageContent\PageContent;
use Littled\Tests\TestHarness\Filters\AjaxPageChild;
use Littled\Tests\TestHarness\Filters\ContentFiltersChild;
use Littled\Tests\TestHarness\Filters\TestTableContentFiltersTestHarness;

class LittledUtilityTestDataProvider
{
	public static function isSubclassTestProvider(): array
	{
		return array(
			array(PageContent::class, AppBase::class, true),
			array(PageContent::class, PageContent::class, true),
			array(AppBase::class, PageContent::class, false),
			array(TestTableContentFiltersTestHarness::class, PageContent::class, false),
			array(PageContent::class, TestTableContentFiltersTestHarness::class, false),
			array(new AjaxPageChild(), ContentFiltersChild::class, false),
			array(new AjaxPageChild(), AjaxPage::class, true),
			array(new AjaxPageChild(), AjaxPageChild::class, true),
		);
	}

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