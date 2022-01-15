<?php
namespace Littled\Tests\Filters;
require_once (realpath(dirname(__FILE__)).'/../bootstrap.php');

use Littled\Tests\Filters\Samples\ContentFiltersProcedureSample;
use Littled\Tests\Filters\Samples\ContentFiltersSample;
use PHPUnit\Framework\TestCase;
use Exception;

class ContentFiltersTest extends TestCase
{
    function testConstruct()
    {
        $cf = new ContentFiltersSample(ContentFiltersSample::CONTENT_ID);
        $this->assertEquals('article', $cf->content_properties->label);
    }

    function testDefaultListingsLength()
    {
        $cf = new ContentFiltersSample(ContentFiltersSample::CONTENT_ID);
        $this->assertGreaterThan(0, $cf->listings_length->value);
    }

    /**
     * @return void
     * @throws Exception
     */
    function testRetrieveListings()
    {
        $cf = new ContentFiltersSample(ContentFiltersSample::CONTENT_ID);
        $data = $cf->retrieveListings();
        $this->assertGreaterThan(2, count($data));
        $last_id = 0;
        $i = 0;
        foreach($data as $row) {
            if ($i> 2) {
                break;
            }
            $this->assertIsNumeric($row->id);
            $this->assertIsString($row->title);
            $this->assertNotEquals($last_id, $row->id);
            $last_id = $row->id;
            $i++;
        }
        $this->assertGreaterThan(0, $cf->record_count);
    }

    /**
     * @return void
     * @throws Exception
     */
    function testRetrieveListingsUsingProcedure()
    {
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
        $cf = new ContentFiltersProcedureSample(ContentFiltersSample::CONTENT_ID);
        $data = $cf->retrieveListings();
        $this->assertCount($cf->listings_length->value, $data);
        $this->assertIsNumeric($cf->record_count);
        $this->assertGreaterThan($cf->listings_length->value, $cf->record_count);
    }

    /**
     * @return void
     * @throws Exception
     */
    function testExecuteListingsQueryAsAssociativeArray()
    {
        $cf = new ContentFiltersSample(ContentFiltersSample::CONTENT_ID);
        $data = $cf->retrieveListings();
        $this->assertGreaterThan(0, count($data));
        foreach($data as $row) {
            $this->assertIsNumeric($row->id);
            $this->assertIsString($row->title);
        }
    }
}
