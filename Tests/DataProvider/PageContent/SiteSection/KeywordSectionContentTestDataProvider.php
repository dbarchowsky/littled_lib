<?php

namespace LittledTests\DataProvider\PageContent\SiteSection;


use LittledTests\PageContent\SiteSection\KeywordSectionContentTest;

class KeywordSectionContentTestDataProvider
{
    public static function fooTestProvider(): array
    {
        return array(
            [1],
        );
    }

    public static function hasKeywordDataTestProvider(): array
    {
        return array(
            [new KeywordSectionContentTestData(false, '[use defaults]')],
            [new KeywordSectionContentTestData(true, 'a')],
            [new KeywordSectionContentTestData(true, '0')],
            [new KeywordSectionContentTestData(true, ' first, second,,third, last ')],
            [new KeywordSectionContentTestData(false, null)],
            [new KeywordSectionContentTestData(false, '')],
            [(new KeywordSectionContentTestData(true, '', 'a',
                KeywordSectionContentTest::TEST_CONTENT_TYPE_ID))
                ->addKeyword(
                    'test keyword',
                    KeywordSectionContentTest::TEST_CONTENT_TYPE_ID,
                    KeywordSectionContentTest::TEST_PARENT_ID_FOR_READ)],
            [(new KeywordSectionContentTestData(true))
                ->addKeyword(
                    'test keyword',
                    KeywordSectionContentTest::TEST_CONTENT_TYPE_ID,
                    KeywordSectionContentTest::TEST_PARENT_ID_FOR_READ)],
            [(new KeywordSectionContentTestData(true))
                ->addKeyword(
                    '  spaced  ',
                    KeywordSectionContentTest::TEST_CONTENT_TYPE_ID,
                    KeywordSectionContentTest::TEST_PARENT_ID_FOR_READ)],
        );
    }
}