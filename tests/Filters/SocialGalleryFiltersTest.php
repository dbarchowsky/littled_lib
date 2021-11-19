<?php
namespace Littled\Tests\Filters;

use Littled\Filters\SocialGalleryFilters;

class SocialGalleryFiltersChild extends SocialGalleryFilters
{
	const TEST_CONTENT_TYPE_ID = 10; /* damien jay database: "Comics Page" in site_section table */

	public static function CONTENT_TYPE_ID()
	{
		return (SocialGalleryFiltersChild::TEST_CONTENT_TYPE_ID);
	}
}

class SocialGalleryFiltersTest extends \PHPUnit\Framework\TestCase
{
	const CONTENT_TYPE_ID = 10; /* sketchbook page from chicot_damienjay */

	/** @var SocialGalleryFilters Filters object used to retrieve gallery listings data. */
	public $filters;

	/**
	 * @throws \Exception
	 */
	public function testRetrieveListings()
	{
		$this->filters = new SocialGalleryFiltersChild();
		$data = $this->filters->retrieveListings();
		$this->assertEquals(CURRENT_SKETCHBOOK_IMAGES, count($data), "Returned records with default filters.");
	}

	/**
	 * @throws \Exception
	 */
	public function testRetrieveWordpressListings()
	{
		$this->filters = new SocialGalleryFiltersChild();
		$this->filters->onWordpress->value = true;
		$data = $this->filters->retrieveListings();
		$this->assertEquals(CURRENT_SKETCHBOOK_WP_IMAGES, count($data), "Returned records with default filters.");

		$this->filters->onWordpress->value = false;
		$data = $this->filters->retrieveListings();
		$this->assertEquals(CURRENT_SKETCHBOOK_IMAGES - CURRENT_SKETCHBOOK_WP_IMAGES, count($data), "Returned records with default filters.");
	}

	/**
	 * @throws \Exception
	 */
	public function testRetrieveTwitterListings()
	{
		$this->filters = new SocialGalleryFiltersChild();
		$this->filters->onTwitter->value = true;
		$data = $this->filters->retrieveListings();
		$this->assertEquals(CURRENT_SKETCHBOOK_TWITTER_IMAGES, count($data), "Returned records with default filters.");

		$this->filters->onTwitter->value = false;
		$data = $this->filters->retrieveListings();
		$this->assertEquals(CURRENT_SKETCHBOOK_IMAGES - CURRENT_SKETCHBOOK_TWITTER_IMAGES, count($data), "Returned records with default filters.");
	}

	/**
	 * @throws \Exception
	 */
	public function testRetrieveShortURLListings()
	{
		$this->filters = new SocialGalleryFiltersChild();
		$this->filters->hasShortURL->value = true;
		$data = $this->filters->retrieveListings();
		$this->assertEquals(CURRENT_SKETCHBOOK_SHORT_URL_IMAGES, count($data), "Returned records with default filters.");

		$this->filters->hasShortURL->value = false;
		$data = $this->filters->retrieveListings();
		$this->assertEquals(CURRENT_SKETCHBOOK_IMAGES - CURRENT_SKETCHBOOK_SHORT_URL_IMAGES, count($data), "Returned records with default filters.");
	}
}
