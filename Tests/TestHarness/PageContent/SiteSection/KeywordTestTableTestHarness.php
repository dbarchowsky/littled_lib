<?php
namespace LittledTests\TestHarness\PageContent\SiteSection;

use Littled\PageContent\SiteSection\KeywordSectionContent;

class KeywordTestTableTestHarness extends KeywordSectionContent
{
    public const                CONTENT_TYPE_ID = 6037; /* << test_table table in littledamien database */
    protected static int        $content_type_id = self::CONTENT_TYPE_ID;
    protected static string     $table_name = 'test_table';
}