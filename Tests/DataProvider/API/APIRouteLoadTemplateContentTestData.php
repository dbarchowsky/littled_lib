<?php

namespace Littled\Tests\DataProvider\API;

use Littled\Tests\API\APIRouteTestBase;


class APIRouteLoadTemplateContentTestData
{
    protected const DEFAULT_CONTENT_TYPE    = APIRouteTestBase::TEST_CONTENT_TYPE_ID;
    protected const DEFAULT_OPERATION       = 'delete';
    protected const DEFAULT_RECORD_ID       = APIRouteTestBase::TEST_RECORD_ID;

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
        $this->record_id        = $record_id ?? APIRouteLoadTemplateContentTestData::DEFAULT_RECORD_ID;
		$this->template         = $template;
        $this->operation        = $operation ?? APIRouteLoadTemplateContentTestData::DEFAULT_OPERATION;
        $this->content_type_id  = $content_type_id ?? APIRouteLoadTemplateContentTestData::DEFAULT_CONTENT_TYPE;
	}
}