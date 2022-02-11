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
	/** @var TestTable[] Array of linked SerializedContent records */
	public $child_array=[];

    public function __construct()
    {
        parent::__construct();
        $this->cu_field = new StringTextField('Test Content Utils Field', 'cuField', false, '', 50);
		$this->child_array[] = new TestTable(88, 'First test child');
	    $this->child_array[] = new TestTable(99, '2nd test child', 854, true, '2022-02-10', 4);
    }
}