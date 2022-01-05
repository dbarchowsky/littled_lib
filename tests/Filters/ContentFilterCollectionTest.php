<?php
namespace Littled\Tests\Filters;
use Littled\Tests\Filters\Samples\ContentFilterCollectionSample;
use PHPUnit\Framework\TestCase;
use Exception;

require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

class ContentFilterCollectionTest extends TestCase
{
    function testConstruct()
    {
        $cf = new ContentFilterCollectionSample(ContentFilterCollectionSample::CONTENT_ID);
        $this->assertEquals('article', $cf->section_operations->label->value);
    }

    /**
     * @return void
     * @throws Exception
     */
    function testRetrieveListings()
    {
        $cf = new ContentFilterCollectionSample(ContentFilterCollectionSample::CONTENT_ID);
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
        $result->free();
    }

    /**
     * @return void
     * @throws Exception
     */
    function testExecuteListingsQuery()
    {
        $query = "CALL articleListingsSelect(1, 4, '%recipe%', NULL, NULL, NULL, NULL, NULL, @total_matches);".
            "SELECT @total_matches AS `total_matches`;";

        $cf = new ContentFilterCollectionSample(ContentFilterCollectionSample::CONTENT_ID);
        $result = $cf->executeListingsQuery($query);
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

        $cf = new ContentFilterCollectionSample(ContentFilterCollectionSample::CONTENT_ID);
        $result = $cf->executeListingsQuery($query);
        $this->assertGreaterThan(0, $result->num_rows);
        foreach($result as $row) {
            $this->assertIsNumeric($row['id']);
            $this->assertIsString($row['title']);
        }
        $result->free();
    }
}