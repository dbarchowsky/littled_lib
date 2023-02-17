<?php
namespace Littled\Tests\API;

use Littled\API\ContentAPIProperties;
use PHPUnit\Framework\TestCase;
use Exception;


class ContentAPIPropertiesTest extends TestCase
{
	/** @var int */
	public const TEST_CONTENT_TYPE_ID = 6037;

	/**
	 * @throws Exception
	 */
	function testRetrieveRoutes()
	{
		$cap = new ContentAPIProperties();
		$cap->section_id->setInputValue(self::TEST_CONTENT_TYPE_ID);
		$cap->retrieveRoutes();

		$this->assertGreaterThan(1, count($cap->routes));

		$operations = array_map(function($i) { return $i->operation->value;}, $cap->routes);
		$this->assertContains('listings', $operations);
		$this->assertContains('delete', $operations);

		$urls = array_map(function($i) { return $i->api_route->value;}, $cap->routes);
		$this->assertContains('/api/listings', $urls);
		$this->assertContains('/api/[#]/delete', $urls);

		$record_ids = array_map(function($i) { return $i->id->value;}, $cap->routes);
		$this->assertContains(2, $record_ids);
		$this->assertContains(3, $record_ids);
	}

	/**
	 * @throws Exception
	 */
	function testRetrieveTemplates()
	{
		$cap = new ContentAPIProperties();
		$cap->section_id->setInputValue(self::TEST_CONTENT_TYPE_ID);
		$cap->retrieveTemplates();

		$this->assertGreaterThan(1, count($cap->templates));

		$names = array_map(function($i) { return $i->name->value;}, $cap->templates);
		$this->assertContains('listings', $names);
		$this->assertContains('delete', $names);

		$locations = array_map(function($i) { return $i->location->value;}, $cap->templates);
		$this->assertContains('local', $locations);

		$paths = array_map(function($i) { return $i->path->value;}, $cap->templates);
		$paths = implode(' ', $paths);
		$this->assertMatchesRegularExpression('/\bdelete-confirmation-dialog\.php\b/', $paths);
		$this->assertMatchesRegularExpression('/\blistings\.php\b/', $paths);

		$record_ids = array_map(function($i) { return $i->id->value;}, $cap->templates);
		$this->assertContains(383, $record_ids);
		$this->assertContains(382, $record_ids);
	}
}