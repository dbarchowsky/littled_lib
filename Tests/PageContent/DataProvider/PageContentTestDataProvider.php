<?php

namespace Littled\Tests\PageContent\DataProvider;

use Littled\App\LittledGlobals;
use Littled\PageContent\PageContent;

class PageContentTestDataProvider
{
	public static function collectEditActionTestProvider(): array
	{
		return array(
			array('', array()),
			array(PageContent::CANCEL_ACTION, array(LittledGlobals::P_CANCEL => 'foo')),
			array(PageContent::CANCEL_ACTION, array(LittledGlobals::P_CANCEL => 'any value')),
			array('', array(LittledGlobals::P_CANCEL => '')),
			array(PageContent::COMMIT_ACTION, array(LittledGlobals::P_COMMIT => 'bar')),
			array(PageContent::COMMIT_ACTION, array(LittledGlobals::P_COMMIT => 'any value any value')),
			array(PageContent::COMMIT_ACTION, array(LittledGlobals::P_COMMIT => 1)),
			array(PageContent::COMMIT_ACTION, array(LittledGlobals::P_COMMIT => '1')),
			array('', array(LittledGlobals::P_COMMIT => 0)),
			array('', array(LittledGlobals::P_COMMIT => '0')),
			array('', array(LittledGlobals::P_COMMIT => '')),
			array(PageContent::CANCEL_ACTION, array(LittledGlobals::P_CANCEL => 'biz', LittledGlobals::P_COMMIT => 'bash')),
			array(PageContent::CANCEL_ACTION, array(LittledGlobals::P_CANCEL => 'biz', 'anotherKey' => 'another value', 'key3' => 'value 3')),
			array(PageContent::COMMIT_ACTION, array(LittledGlobals::P_COMMIT => 'biz', 'anotherKey' => 'another value', 'key3' => 'value 3')),
			array(PageContent::COMMIT_ACTION, array(LittledGlobals::P_CANCEL => '0', LittledGlobals::P_COMMIT => 'commit')),
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
