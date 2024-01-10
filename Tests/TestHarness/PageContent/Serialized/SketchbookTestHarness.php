<?php

namespace LittledTests\TestHarness\PageContent\Serialized;

use Littled\PageContent\SiteSection\SectionContent;

class SketchbookTestHarness extends SectionContent
{
    /** @var int */
    public const CONTENT_TYPE_ID = 11; /* "sketchbook" in site_section table */
    /** @var string */
    protected static string $table_name='album';
    /** @var int */
    protected static int $content_type_id = self::CONTENT_TYPE_ID;
    /** @var KeywordTestHarness[] */
    public array $keyword_list;

    function __construct(int $id = null, int $content_type_id = null)
    {
        $content_type_id = $content_type_id ?: static::$content_type_id;
        parent::__construct($id, $content_type_id);
        $this->keyword_list = [];
    }
}