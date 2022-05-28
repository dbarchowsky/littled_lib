<?php

namespace Littled\Tests\PageContent\Serialized\TestHarness;

use Littled\Request\StringTextField;

class SerializedContentUtilsChild extends SerializedContentChild
{
    /** @var string */
    protected static string $cache_template = "/path/to/templates/child-cache-template.php";
    /** @var int */
    protected static int $content_type_id = 10; /* sketchbook page */
    /** @var StringTextField */
    public StringTextField $cu_field;
	/** @var TestTable[] Array of linked SerializedContent records */
	public array $child_array=[];
	/** @var string Property that will be unset during Tests */
	public string $unassigned;

    public function __construct()
    {
        parent::__construct();
        $this->cu_field = new StringTextField('Test Content Utils Field', 'cuField', false, '', 50);
		$this->child_array[] = new TestTable(88, 'First test child');
	    $this->child_array[] = new TestTable(99, '2nd test child', 854, true, '2022-02-10', 4);
    }
}