<?php
namespace Littled\Tests\Filters;

use Littled\Filters\GalleryFilters;
use Littled\Tests\TestHarness\Filters\GalleryFiltersChild;
use PHPUnit\Framework\TestCase;
use Exception;

class GalleryFiltersTest extends TestCase
{
	const CHILD_CONTENT_TYPE_ID = 10; /* sketchbook page from chicot_damienjay */
	const DETAILS_URI = '/_ajax/images/image_details.php';

	/** @var GalleryFilters Filters object used to retrieve gallery listings data. */
	public GalleryFilters $filters;

	/**
	 * @throws Exception
	 */
	public function testRetrieveListings()
	{
		$this->filters = new GalleryFiltersChild();
		$data = $this->filters->retrieveListings();
		$this->assertGreaterThan(0, count($data), "Returned records with default filters.");
	}

	/**
	 * @throws Exception
	 */
	public function testTitleFilter()
	{
		$pattern = 'sketchbook';
		$this->filters = new GalleryFiltersChild();
		$this->filters->title->value = $pattern;
		$data = $this->filters->retrieveListings();
		$this->assertGreaterThan(0, count($data), "Returned records with title filter.");
		foreach($data as $r)
		{
			$this->assertMatchesRegularExpression("/$pattern/", $r->title);
		}
	}

	/**
	 * @throws Exception
	 */
	public function testDetailsURI()
	{
		$this->filters = new GalleryFiltersChild();
		$uri = $this->filters->getDetailsUri();
		$this->assertEquals(GalleryFiltersTest::DETAILS_URI, $this->filters->details_uri);
		$this->assertEquals(GalleryFiltersTest::DETAILS_URI, $uri, "Returned value.");
	}
}
