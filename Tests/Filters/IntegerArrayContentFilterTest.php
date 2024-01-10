<?php
namespace LittledTests\Filters;

use PHPUnit\Framework\TestCase;
use Littled\Filters\IntegerArrayContentFilter;


class IntegerArrayContentFilterTest extends TestCase
{
	function testFormatValuesString()
	{
		$f = new IntegerArrayContentFilter('Test Filter', 'testFilter');
		$_POST['testFilter'] = array(88,64,93,2);
		$f->collectValue();
		$this->assertContains(64, $f->value);

		$this->assertEquals('88,64,93,2', $f->formatValuesString());

		$this->assertEquals('88##64##93##2', $f->formatValuesString('##'));
	}
}