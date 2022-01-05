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
        $result = $cf->retrieveListings();
        $this->assertGreaterThan(2, $result->num_rows);
        $last_id = 0;
        $i = 0;
        foreach($result as $row) {
            if ($i> 2) {
                break;
            }
            $this->assertIsNumeric($row['id']);
            $this->assertIsString($row['title']);
            $this->assertNotEquals($last_id, $row['id']);
            $last_id = $row['id'];
            $i++;
        }
        $this->assertNull($cf->record_count);
        $result->free();
    }

    /**
     * @return void
     * @throws Exception
     */
    function testRetrieveListingsUsingProcedure()
    {
        $cf = new ContentFiltersProcedureSample(ContentFiltersSample::CONTENT_ID);
        $result = $cf->retrieveListings();
        $this->assertEquals($cf->listings_length, $result->num_rows);
        $this->assertIsNumeric($cf->record_count);
        $this->assertGreaterThan($cf->listings_length, $cf->record_count);
    }

    /**
     * @return void
     * @throws Exception
     */
    function testExecuteListingsQuery()
    {
        $query = "CALL articleListingsSelect(1, 4, '%recipe%', NULL, NULL, NULL, NULL, NULL, @total_matches);".
            "SELECT @total_matches AS `total_matches`;";

        $cf = new ContentFiltersSample(ContentFiltersSample::CONTENT_ID);
        $result = $cf->retrieveListingsUsingProcedureTest($query);
        $this->assertGreaterThan(0, $result->num_rows);
        $this->assertGreaterThan(0, $cf->record_count);
        $this->assertGreaterThan(0, $cf->page_count);

        $row = $result->fetch_assoc();
        $this->assertMatchesRegularExpression('/^[a-zA-Z].*/', $row['title']);

        $this->assertGreaterThan(0, $result->num_rows);
        while($row = $result->fetch_object()) {
            $this->assertIsNumeric($row->id);
            $this->assertIsString($row->title);
        }
        $result->free();
    }

    /**
     * @return void
     * @throws Exception
     */
    function testExecuteListingsQueryAsAssociativeArray()
    {
        $query = "CALL articleListingsSelect(1, 3, NULL, NULL, NULL, NULL, NULL, NULL, @total_matches);".
            "SELECT @total_matches AS `total_matches`;";

        $cf = new ContentFiltersSample(ContentFiltersSample::CONTENT_ID);
        $result = $cf->retrieveListingsUsingProcedureTest($query);
        $this->assertGreaterThan(0, $result->num_rows);
        foreach($result as $row) {
            $this->assertIsNumeric($row['id']);
            $this->assertIsString($row['title']);
        }
        $result->free();
    }
}
