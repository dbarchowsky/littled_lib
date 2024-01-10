<?php
namespace LittledTests\DataProvider\PageContent\Navigation\RoutedPageContent;


class GetPageRouteTestData
{
	public string   $expected;
	public string   $class;
	public ?int     $record_id;
	public bool     $force_update;

	public function __construct(
		string  $expected,
		string  $class,
		?int    $record_id=null,
		bool    $force_update=true
	)
	{
		$this->expected = $expected;
		$this->class = $class;
		$this->record_id = $record_id;
		$this->force_update = $force_update;
	}
}
