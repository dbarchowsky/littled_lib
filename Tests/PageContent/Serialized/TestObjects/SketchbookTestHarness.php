<?php

namespace Littled\Tests\PageContent\Serialized\TestObjects;

use Littled\PageContent\SiteSection\SectionContent;

class SketchbookTestHarness extends SectionContent
{
    /** @var int */
    public const CONTENT_TYPE_ID = 11; /* "sketchbook" in site_section table */
    /** @var string */
    protected static $table_name='album';
    /** @var int */
    protected static $content_type_id = self::CONTENT_TYPE_ID;
    /** @var KeywordTestHarness[] */
    public $keyword_list;

    function __construct(int $id = null, int $content_content_id = null)
    {
        $content_content_id = $content_content_id ?: static::$content_type_id;
        parent::__construct($id, $content_content_id);
        $this->keyword_list = [];
    }
}