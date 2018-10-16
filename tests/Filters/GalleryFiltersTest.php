<?php
namespace Littled\Tests\Filters;

use Littled\Filters\GalleryFilters;
use Littled\PageContent\Albums\Gallery;

class GalleryFiltersChild extends GalleryFilters
{
	const TEST_CONTENT_TYPE_ID = 9; /* damien jay database: "Comics Page" in site_section table */

	public static function CONTENT_TYPE_ID()
	{
		return (GalleryFiltersChild::TEST_CONTENT_TYPE_ID);
	}
}

class GalleryFiltersTest extends \PHPUnit\Framework\TestCase
{
	const CONTENT_TYPE_ID = 10; /* sketchbook page from chicot_damienjay */
	const DETAILS_URI = '/hostmgr/_ajax/images/image_details.php';

	/** @var GalleryFilters Filters object used to retrieve gallery listings data. */
	public $filters;

	/**
	 * @throws \Exception
	 */
	public function testDefaultPageLen()
	{
		$new_default = GalleryFiltersChild::DEFAULT_PAGE_LEN + 10;
		$this->filters = new GalleryFiltersChild($new_default);
		$this->assertEquals($this->filters->defaultPageLength, $new_default, "New default page length value.");
	}

	/**
	 * @throws \Exception
	 */
	public function testRetrieveListings()
	{
		$this->filters = new GalleryFiltersChild();
		$data = $this->filters->retrieveListings();
		$this->assertGreaterThan(0, count($data), "Returned records with default filters.");
	}

	/**
	 * @throws \Exception
	 */
	public function testTitleFilter()
	{
		$pattern = 'cover';
		$this->filters = new GalleryFiltersChild();
		$this->filters->title->value = $pattern;
		$data = $this->filters->retrieveListings();
		$this->assertGreaterThan(0, count($data), "Returned records with title filter.");
		foreach($data as $r)
		{
			$this->assertRegExp("/{$pattern}/", $r->title);
		}
	}

	/**
	 * @throws \Exception
	 */
	public function testDetailsURI()
	{
		$this->filters = new GalleryFiltersChild(GalleryFiltersChild::CONTENT_TYPE_ID());
		$uri = $this->filters->getDetailsURI();
		$this->assertEquals($this->filters->detailsURI, GalleryFiltersTest::DETAILS_URI, "Object property value.");
		$this->assertEquals(GalleryFiltersTest::DETAILS_URI, $uri, "Returned value.");
	}
}
