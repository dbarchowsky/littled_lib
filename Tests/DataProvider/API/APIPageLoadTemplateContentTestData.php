<?php

namespace Littled\Tests\DataProvider\API;

use Littled\Tests\API\APIPageTest;


class APIPageLoadTemplateContentTestData
{
    protected const DEFAULT_CONTENT_TYPE    = APIPageTest::TEST_CONTENT_TYPE_ID;
    protected const DEFAULT_OPERATION       = 'delete';
    protected const DEFAULT_RECORD_ID       = APIPageTest::TEST_RECORD_ID;

	public array        $context;
    public int          $content_type_id ;
	public string       $msg;
    public string       $operation;
	public string       $pattern;
    public int          $record_id;
	public string       $template;

	function __construct(
        string      $msg='',
        string      $pattern='',
        array       $context=[],
        string      $template='',
        ?int        $record_id=null,
        ?string     $operation=null,
        ?int        $content_type_id=null)
	{
        $this->msg              = $msg;
		$this->pattern          = $pattern;
		$this->context          = $context;
        $this->record_id        = $record_id ?? APIPageLoadTemplateContentTestData::DEFAULT_RECORD_ID;
		$this->template         = $template;
        $this->operation        = $operation ?? APIPageLoadTemplateContentTestData::DEFAULT_OPERATION;
        $this->content_type_id  = $content_type_id ?? APIPageLoadTemplateContentTestData::DEFAULT_CONTENT_TYPE;
	}
}