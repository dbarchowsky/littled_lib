<?php
namespace Littled\Tests\SiteContent;

require_once (realpath(dirname(__FILE__).'/../../').'/_dbo/bootstrap.php');

use Littled\SiteContent\ContentProperties;


class ContentPropertiesTest extends \PHPUnit_Framework_TestCase
{
	public function testRead()
	{
		$c = new ContentProperties();
		$c->id = 2;
		$c->read();
		$this->assertEquals('Cart Order', $c->name->value);
	}
}