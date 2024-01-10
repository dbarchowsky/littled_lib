<?php
namespace LittledTests\Filters;

use LittledTests\TestHarness\Filters\SocialGalleryFiltersChild;
use PHPUnit\Framework\TestCase;
use Exception;

class SocialGalleryFiltersTest extends TestCase
{
	/**
	 * @throws Exception
	 */
	public function testRetrieveListings()
	{
		$o = new SocialGalleryFiltersChild();
		$data = $o->retrieveListings();
		$this->assertGreaterThan(0, count($data), "Returned records with default filters.");
	}

	/**
	 * @throws Exception
	 */
	public function testRetrieveWordpressListings()
	{
		$o = new SocialGalleryFiltersChild();
        $data = $o->retrieveListings();
        $full_count = count($data);

		$o->onWordpress->value = true;
		$data = $o->retrieveListings();
		$this->assertGreaterThan(0, count($data));
        $this->assertLessThan($full_count, count($data));

		$o->onWordpress->value = false;
		$data = $o->retrieveListings();
		$this->assertGreaterThan(0, count($data));
        $this->assertLessThan($full_count, count($data));
	}

	/**
	 * @throws Exception
	 */
	public function testRetrieveTwitterListings()
	{
		$o = new SocialGalleryFiltersChild();
        $data = $o->retrieveListings();
        $full_count = count($data);

		$o->onTwitter->value = true;
		$data = $o->retrieveListings();
		$this->assertCount(0, $data);

		$o->onTwitter->value = false;
		$data = $o->retrieveListings();
		$this->assertCount($full_count, $data);
	}

	/**
	 * @throws Exception
	 */
	public function testRetrieveShortURLListings()
	{
		$o = new SocialGalleryFiltersChild();
        $data = $o->retrieveListings();
        $full_count = count($data);

		$o->hasShortURL->value = true;
		$data = $o->retrieveListings();
		$this->assertGreaterThan(0, count($data));
        $this->assertLessThan($full_count, count($data));

		$o->hasShortURL->value = false;
		$data = $o->retrieveListings();
		$this->assertGreaterThan(0, count($data));
        $this->assertLessThan($full_count, count($data));
	}
}
