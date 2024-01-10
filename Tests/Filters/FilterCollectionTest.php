<?php
namespace LittledTests\Filters;

use Littled\App\LittledGlobals;
use Littled\Exception\NotImplementedException;
use Littled\Filters\ContentFilter;
use LittledTests\DataProvider\Filters\FilterCollection\FormatQueryStringTestData;
use LittledTests\TestHarness\Filters\FilterCollectionAutoloadChild;
use LittledTests\TestHarness\Filters\FilterCollectionChild;
use LittledTests\TestHarness\Filters\FilterCollectionChildWithProcedure;
use LittledTests\TestHarness\Filters\TestTableFilters;

class FilterCollectionTest extends FilterCollectionTestBase
{
	function testAutoloadDefault()
	{
		// confirm default value
		$fc = new FilterCollectionChild();
		$this->assertFalse($fc->getAutoloadDefault());

		// confirm setting autoload listings to TRUE
		$fc->setAutoloadDefault(true);
		$this->assertTrue($fc->getAutoloadDefault());

		// confirm setting autoload listings to FALSE
		$fc->setAutoloadDefault(false);
		$this->assertFalse($fc->getAutoloadDefault());
	}

	/**
	 * @dataProvider \LittledTests\DataProvider\Filters\FilterCollection\FilterCollectionTestDataProvider::calculateOffsetToPageTestProvider()
	 * @param int $page
	 * @param int $listings_length
	 * @param int $expected
	 * @return void
	 */
	function testCalculateOffsetToPage(int $page, int $listings_length, int $expected)
	{
		$f = new FilterCollectionChild();
		$f->page->value = $page;
		$f->listings_length->value = $listings_length;
		$this->assertEquals($expected, $f->calculateOffsetToPage());
	}

	function __testCollectDisplayListingsSettings(FilterCollectionChild $filters, ?bool $expected, string $collection, $value=null, string $msg='')
	{
		switch($collection) {
			case 'cookie':
				$_COOKIE[$filters->display_listings->key] = $value;
				break;
			case 'post':
				$_POST[$filters->display_listings->key] = $value;
				break;
			default:
				break;
		}
		$filters->collectDisplayListingsSetting();
		$this->assertEquals($expected, $filters->display_listings->value, $msg);

		// clean up
		$_POST = [];
		$_COOKIE = [];
	}

	/**
	 * @dataProvider \LittledTests\DataProvider\Filters\FilterCollection\FilterCollectionTestDataProvider::collectDisplayListingsSettingsWithAutoload()
	 * @param ?bool $expected
	 * @param string $collection
	 * @param $value
	 * @param string $msg
	 * @return void
	 */
	function testCollectDisplayListingsSettingsWithAutoload(?bool $expected, string $collection, $value=null, string $msg='')
	{
		$this->__testCollectDisplayListingsSettings(new FilterCollectionAutoloadChild(), $expected, $collection, $value, $msg);
	}

	/**
	 * @dataProvider \LittledTests\DataProvider\Filters\FilterCollection\FilterCollectionTestDataProvider::collectDisplayListingsSettingsWithDefault()
	 * @param ?bool $expected
	 * @param string $collection
	 * @param $value
	 * @param string $msg
	 * @return void
	 */
	function testCollectDisplayListingsSettingsWithDefault(?bool $expected, string $collection, $value=null, string $msg='')
	{
		$this->__testCollectDisplayListingsSettings(new FilterCollectionChild(), $expected, $collection, $value, $msg);
	}

    /**
     * @dataProvider \LittledTests\DataProvider\Filters\FilterCollection\FilterCollectionTestDataProvider::collectDisplayListingsSettingTestProvider()
     * @param bool|null $expected
     * @param array $post_data
     * @param array $get_data
     * @param array|null $override_data
     * @param string $msg
     * @return void
     */
    function testCollectDisplayListingsSetting(
        ?bool $expected,
        array $post_data=[],
        array $get_data=[],
        ?array $override_data=null,
        string $msg = ''
    )
    {
        $_GET = $get_data;
        $_POST = $post_data;

        $f = new FilterCollectionChild();
        $f->collectDisplayListingsSetting($override_data);
        $this->assertEquals($expected, $f->display_listings->value, $msg);

        // cleanup
        $_GET = $_POST = [];
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Filters\FilterCollection\FilterCollectionTestDataProvider::collectFilterValuesTestProvider()
     * @param array $expected
     * @param array $get_data
     * @param array $post_data
     * @param array|null $custom_data
     * @param bool $save_filters
     * @param array $ignore_keys
     * @param string $msg
     * @return void
     * @throws NotImplementedException
     */
    function testCollectFilterValues(
        array $expected,
        array $get_data=[],
        array $post_data=[],
        array $custom_data=null,
        bool $save_filters=true,
        array $ignore_keys=[],
        string $msg='')
    {
        $_GET = $get_data;
        $_POST = $post_data;

        $o = new FilterCollectionChild();
        $o->collectFilterValues($save_filters, $ignore_keys, $custom_data);
        foreach($expected as $property => $value) {
            /** @var ContentFilter $p */
            $p = $o->$property;
            $this->assertEquals($value, $p->value, $msg);
        }

        // restore state
        $_GET = $_POST = [];
    }

    /**
     * @throws NotImplementedException
     */
    function testCollectFilterValues_ReferringURI()
    {
        $o = new FilterCollectionChild();
        $o->collectFilterValues();
        $this->assertEquals('', $o->referer_uri);

        $_POST[LittledGlobals::REFERER_KEY] = 'https://localhost';
        $o->collectFilterValues();
        $this->assertEquals('https://localhost', $o->referer_uri);
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Filters\FilterCollection\FormatQueryStringTestDataProvider::formatQueryStringTestProvider()
     * @param FormatQueryStringTestData $data
     * @return void
     */
    function testFormatQueryString(FormatQueryStringTestData $data)
    {
        $f = new FilterCollectionChild();
        foreach ($data->test_values as $key => $value) {
            $p = $f->$key;
            $p->value = $value;
        }
        $query_string = $f->formatQueryString($data->excluded_keys);
        foreach ($data->expected->values as $expected) {
            $this->assertStringContainsString($expected, $query_string);
        }
        foreach ($data->expected->not_contains as $test_value) {
            $this->assertStringNotContainsString($test_value, $query_string);
        }
    }

    function testFormatListingsQueryNotImplemented()
    {
        // Test when not implemented in child class
        $fc = new FilterCollectionChild();
        $args = $fc->formatListingsQueryTest();
        $this->assertCount(3, $args);
        $this->assertEquals('', $args[0]);  /* query string */
        $this->assertEquals('', $args[1]);  /* types descriptor */
        $this->assertNull($args[2]);                /* start of variables to bind to query */
    }

    /**
     * @return void
     */
    function testFormatListingsQueryUsingProcedure()
    {
        $fc = new FilterCollectionChildWithProcedure();
        $args = $fc->formatListingsQuery();
        $this->assertCount(9, $args);
        $this->assertMatchesRegularExpression('/^CALL testTableListingsSelect\(/', $args[0]);
        $this->assertEquals('iisiiss', $args[1]);
		// test that when the page value is NULL it translates to a listings_offset value of 0
        $this->assertEquals(0, $args[2]);
    }

    /**
     * @dataProvider \LittledTests\DataProvider\Filters\FilterCollection\FilterCollectionTestDataProvider::listingsDataContainsNeighborIdsTestProvider()
     * @param bool $expected
     * @param array $data
     * @param int $page_position
     * @param int $page
     * @param int $listings_length
     * @param int $page_count
     * @param int $record_count
     * @param string $msg
     * @return void
     */
	function testListingsDataContainsNeighborIds(
		bool $expected,
		array $data,
		int $page_position,
		int $page,
		int $listings_length,
		int $page_count,
		int $record_count,
		string $msg='')
	{
		$f = new TestTableFilters();
		$f->page->value = $page;
		$f->listings_length->value = $listings_length;
		$f->page_count = $page_count;
		$f->record_count = $record_count;
		$this->assertEquals($expected, $f->publicListingsDataContainsNeighborIds($data, $page_position), $msg);
	}
}