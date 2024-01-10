<?php
namespace LittledTests\DataProvider\API;


class APIRouteTestExpectations
{
	public string $name_filter;
	public ?int $int_filter;
	public ?bool $bool_filter;
	public string $exception_class;

	function __construct(
		string $name_filter='',
		?int $int_filter=null,
		?bool $bool_filter=null,
		string $exception_class='')
	{
		$this->name_filter = $name_filter;
		$this->int_filter = $int_filter;
		$this->bool_filter = $bool_filter;
		$this->exception_class = $exception_class;
	}
}