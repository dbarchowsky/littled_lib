<?php
namespace Littled\Tests\Filters;

require_once (realpath(dirname(__FILE__).'/../../').'/_dbo/bootstrap.php');

use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Filters\ContentFilters;

define('VALID_CONTENT_TYPE_ID', 2);
define('INVALID_CONTENT_TYPE_ID', 145);

class InvalidContentType extends ContentFilters
{
	public static function CONTENT_TYPE_ID() { return (INVALID_CONTENT_TYPE_ID); }
}

class ValidContentType extends ContentFilters
{
	public static function CONTENT_TYPE_ID() { return(VALID_CONTENT_TYPE_ID); }
}

class ContentFiltersTest extends \PHPUnit_Framework_TestCase
{
	public function testContentTypeId()
	{
		$ex_msg = "";
		try {
			$c = new ContentFilters();
		}
		catch(NotImplementedException $ex) {
			$ex_msg = $ex->getMessage();
		}
		$this->assertEquals("Littled\Filters\ContentFilters::CONTENT_TYPE_ID not implemented.", $ex_msg, "Invoking constructor in base ContentFilters class without defining CONTENT_TYPE_ID().");

		$ex_msg = "";
		try {
			$d = new ValidContentType();
		}
		catch(NotImplementedException $ex) {
			$ex_msg = $ex->getMessage();
		}
		$this->assertEquals("", $ex_msg, "Invoking constructor in derived class after implementing CONTENT_TYPE_ID().");
		$this->assertInstanceOf('Littled\Filters\ContentFilters', $d, "Child object created.");
		$this->assertEquals(VALID_CONTENT_TYPE_ID, $d->getContentTypeId());

		$ex_msg = '';
		try {
			$i = new InvalidContentType();
		}
		catch(RecordNotFoundException $ex) {
			$ex_msg = $ex->getMessage();
		}
		$this->assertEquals("The requested record was not found.", $ex_msg, "Setting content type id to non-existent record.");
	}
}
