<?php

namespace Littled\Tests\DataProvider\PageContent;

use Littled\App\LittledGlobals;
use Littled\PageContent\PageContent;

class PageContentTestDataProvider
{
	public static function collectEditActionTestProvider(): array
	{
		return array(
			array('', array()),
			array(PageContent::CANCEL_ACTION, array(LittledGlobals::CANCEL_KEY => 'foo')),
			array(PageContent::CANCEL_ACTION, array(LittledGlobals::CANCEL_KEY => 'any value')),
			array('', array(LittledGlobals::CANCEL_KEY => '')),
			array(PageContent::COMMIT_ACTION, array(LittledGlobals::COMMIT_KEY => 'bar')),
			array(PageContent::COMMIT_ACTION, array(LittledGlobals::COMMIT_KEY => 'any value any value')),
			array(PageContent::COMMIT_ACTION, array(LittledGlobals::COMMIT_KEY => 1)),
			array(PageContent::COMMIT_ACTION, array(LittledGlobals::COMMIT_KEY => '1')),
			array('', array(LittledGlobals::COMMIT_KEY => 0)),
			array('', array(LittledGlobals::COMMIT_KEY => '0')),
			array('', array(LittledGlobals::COMMIT_KEY => '')),
			array(PageContent::CANCEL_ACTION, array(LittledGlobals::CANCEL_KEY => 'biz', LittledGlobals::COMMIT_KEY => 'bash')),
			array(PageContent::CANCEL_ACTION, array(LittledGlobals::CANCEL_KEY => 'biz', 'anotherKey' => 'another value', 'key3' => 'value 3')),
			array(PageContent::COMMIT_ACTION, array(LittledGlobals::COMMIT_KEY => 'biz', 'anotherKey' => 'another value', 'key3' => 'value 3')),
			array(PageContent::COMMIT_ACTION, array(LittledGlobals::CANCEL_KEY => '0', LittledGlobals::COMMIT_KEY => 'commit')),
		);
	}

    public static function getRecordIdProvider(): array
    {
        return array(
            [null, null],
            [0, null],
            [45, 45]
        );
    }
}
