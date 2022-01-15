<?php
namespace Littled\Tests\Filters;
require_once (realpath(dirname(__FILE__)).'/../bootstrap.php');

use Littled\Filters\SocialGalleryFilters;
use Littled\Tests\Filters\Samples\SocialGalleryFiltersChild;
use PHPUnit\Framework\TestCase;
use Exception;

class SocialGalleryFiltersTest extends TestCase
{
	/** @var SocialGalleryFilters Filters object used to retrieve gallery listings data. */
	public $filters;

	/**
	 * @throws Exception
	 */
	public function testRetrieveListings()
	{
		$this->filters = new SocialGalleryFiltersChild();
		$data = $this->filters->retrieveListings();
		$this->assertGreaterThan(0, count($data), "Returned records with default filters.");
	}

	/**
	 * @throws Exception
	 */
	public function testRetrieveWordpressListings()
	{
		$this->filters = new SocialGalleryFiltersChild();
		$this->filters->onWordpress->value = true;
		$data = $this->filters->retrieveListings();
		$this->assertGreaterThan(0, count($data));

		$this->filters->onWordpress->value = false;
		$data = $this->filters->retrieveListings();
		$this->assertGreaterThan(0, count($data));
	}

	/**
	 * @throws Exception
	 */
	public function testRetrieveTwitterListings()
	{
		$this->filters = new SocialGalleryFiltersChild();
		$this->filters->onTwitter->value = true;
		$data = $this->filters->retrieveListings();
		$this->assertGreaterThan(0, count($data));

		$this->filters->onTwitter->value = false;
		$data = $this->filters->retrieveListings();
		$this->assertGreaterThan(0, count($data));
	}

	/**
	 * @throws Exception
	 */
	public function testRetrieveShortURLListings()
	{
		$this->filters = new SocialGalleryFiltersChild();
		$this->filters->hasShortURL->value = true;
		$data = $this->filters->retrieveListings();
		$this->assertGreaterThan(0, count($data));

		$this->filters->hasShortURL->value = false;
		$data = $this->filters->retrieveListings();
		$this->assertGreaterThan(0, count($data));
	}
}
