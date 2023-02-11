<?php

namespace Littled\Tests\DataProvider\PageContent;

use Littled\App\LittledGlobals;
use Littled\PageContent\PageContent;

class PageContentTestDataProvider
{
	public static function collectEditActionTestProvider(): array
	{
		return array(
			array('', array(), null),
			array(PageContent::CANCEL_ACTION, array(LittledGlobals::CANCEL_KEY => 'foo'), null),
			array(PageContent::CANCEL_ACTION, array(LittledGlobals::CANCEL_KEY => 'any value'), ''),
			array('', array(LittledGlobals::CANCEL_KEY => ''), ''),
			array(PageContent::COMMIT_ACTION, array(LittledGlobals::COMMIT_KEY => 'bar'), ''),
			array(PageContent::COMMIT_ACTION, array(LittledGlobals::COMMIT_KEY => 'any value any value'), ''),
			array(PageContent::COMMIT_ACTION, array(LittledGlobals::COMMIT_KEY => 1), ''),
			array(PageContent::COMMIT_ACTION, array(LittledGlobals::COMMIT_KEY => '1'), ''),
			array('', array(LittledGlobals::COMMIT_KEY => 0), ''),
			array('', array(LittledGlobals::COMMIT_KEY => '0'), ''),
			array('', array(LittledGlobals::COMMIT_KEY => ''), ''),
			array(PageContent::CANCEL_ACTION, array(LittledGlobals::CANCEL_KEY => 'biz', LittledGlobals::COMMIT_KEY => 'bash'), '0'),
			array(PageContent::CANCEL_ACTION, array(LittledGlobals::CANCEL_KEY => 'biz', 'anotherKey' => 'another value', 'key3' => 'value 3'), ''),
			array(PageContent::COMMIT_ACTION, array(LittledGlobals::COMMIT_KEY => 'biz', 'anotherKey' => 'another value', 'key3' => 'value 3'), ''),
			array(PageContent::COMMIT_ACTION, array(LittledGlobals::CANCEL_KEY => '0', LittledGlobals::COMMIT_KEY => 'commit'), ''),
			array(PageContent::COMMIT_ACTION, array(
				'id' => '',
				'filter' => '1',
				'p' => '1',
				'pl' => '25',
				'mvtx' => '',
				'commit' => 'submit',
				'next' => 'view'
				), ''),
			array(PageContent::COMMIT_ACTION, array(LittledGlobals::COMMIT_KEY => 'biz', 'anotherKey' => 'another value', 'key3' => 'value 3'), PageContent::CANCEL_ACTION),
			array(PageContent::CANCEL_ACTION, array(LittledGlobals::CANCEL_KEY => 'biz', 'anotherKey' => 'another value', 'key3' => 'value 3'), PageContent::COMMIT_ACTION),
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
