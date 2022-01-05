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

    function testRetrieveListings()
    {
        $cf = new ContentFilterCollectionSample(ContentFilterCollectionSample::CONTENT_ID);
        $data = $cf->retrieveListings();
        $this->assertGreaterThan(0, count($data));
    }

    /**
     * @return void
     * @throws Exception
     */
    function testExecuteListingsQuery()
    {
        $query = "CALL articleListingsSelect(1, 10, '%recipe%', NULL, NULL, NULL, NULL, NULL, @total_matches);".
            "SELECT @total_matches AS `total_matches`;";

        $cf = new ContentFilterCollectionSample(ContentFilterCollectionSample::CONTENT_ID);
        $result = $cf->testListingsQuery($query);
        $this->assertGreaterThan(0, $result->num_rows);
        $this->assertGreaterThan(0, $cf->record_count);
        $this->assertGreaterThan(0, $cf->page_count);

        $row = $result->fetch_assoc();
        $this->assertMatchesRegularExpression('/^[a-zA-Z].*/', $row['title']);
        $result->close();
    }
}