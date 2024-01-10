<?php
namespace LittledTests\DataProvider\PageContent\Navigation\SectionNavigationRoutes;


class GetPageRouteTestData
{
	public string   $expected;
	public string   $class;
	public ?int     $record_id;

	public function __construct(
		string  $expected,
		string  $class,
		?int    $record_id=null
	)
	{
		$this->expected = $expected;
		$this->class = $class;
		$this->record_id = $record_id;
	}
}