<?php

namespace Littled\Tests\PageContent\Serialized\TestHarness;

use Littled\Request\StringTextField;

class SerializedContentUtilsChild extends SerializedContentChild
{
    /** @var string */
    protected static $cache_template = "/path/to/templates/child-cache-template.php";
    /** @var int */
    protected static $content_type_id = 10; /* sketchbook page */
    /** @var StringTextField */
    public $cu_field;

    public function __construct()
    {
        parent::__construct();
        $this->cu_field = new StringTextField('Test Content Utils Field', 'cuField', false, '', 50);
    }
}