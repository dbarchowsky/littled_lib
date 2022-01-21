<?php

namespace Littled\Tests\PageContent\Serialized\TestObjects;

class SerializedContentUtilsChild extends SerializedContentChild
{
    /** @var string */
    protected static $cache_template = "/path/to/templates/child-cache-template.php";
    /** @var int */
    protected static $content_id = 10; /* sketchbook page */
}