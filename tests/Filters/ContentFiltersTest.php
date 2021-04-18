<?php
namespace Littled\Tests\Filters;

require_once (realpath(dirname(__FILE__).'/../../').'/_dbo/bootstrap.php');

use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Filters\ContentFilters;
use PHPUnit\Framework\TestCase;

define('VALID_CONTENT_TYPE_ID', 2);
define('INVALID_CONTENT_TYPE_ID', 145);

class InvalidContentFilters extends ContentFilters
{
	public static function CONTENT_TYPE_ID() { return (INVALID_CONTENT_TYPE_ID); }
}

class ValidContentFilters extends ContentFilters
{
	public static function CONTENT_TYPE_ID() { return(VALID_CONTENT_TYPE_ID); }
	
	protected function formatListingsQuery()
	{
		$query = "CALL shippingRatesListings(1, 10, '', @total_matches);SELECT CAST(@total_matches AS UNSIGNED) as `total_matches`;";
		return ($query);
	}
}

class ContentFiltersTest extends TestCase
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
			$d = new ValidContentFilters();
		}
		catch(NotImplementedException $ex) {
			$ex_msg = $ex->getMessage();
		}
		$this->assertEquals("", $ex_msg, "Invoking constructor in derived class after implementing CONTENT_TYPE_ID().");
		$this->assertInstanceOf('Littled\Filters\ContentFilters', $d, "Child object created.");
		$this->assertEquals(VALID_CONTENT_TYPE_ID, $d->getContentTypeId());

		$ex_msg = '';
		try {
			$i = new InvalidContentFilters();
		}
		catch(RecordNotFoundException $ex) {
			$ex_msg = $ex->getMessage();
		}
		$this->assertEquals("The requested record was not found.", $ex_msg, "Setting content type id to non-existent record.");
	}
	
	public function testRetrieveListings()
	{
		$f = new ValidContentFilters();
		$data = $f->retrieveListings();
		$this->assertGreaterThan(0, count($data), "Listings records returned.");
		$this->assertEquals(11, $f->record_count, "Record count.");
		$this->assertEquals('USA', $data[6]->region, "Expected cell value.");
	}
}
