<?php
namespace Littled\Tests\PageContent\Navigation;

use Exception;
use Littled\Account\UserAccount;
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\PageContent\Navigation\RoutedPageContent;
use Littled\Tests\TestHarness\Filters\TestTableContentFiltersTestHarness;
use Littled\Tests\TestHarness\SiteContent\TestTableSectionNavigationRoutes;
use Littled\Tests\TestHarness\PageContent\Serialized\TestTableSerializedContentTestHarness;
use Littled\Tests\TestHarness\PageContent\SiteSection\SectionContentTestHarness;
use Littled\Tests\TestHarness\PageContent\Navigation\RoutedPageContentTestHarness;
use Littled\Tests\TestHarness\PageContent\Navigation\SectionNavigationRoutesTestHarness;
use Littled\Tests\TestHarness\PageContent\SiteSection\TestTableSectionContentTestHarness;
use Littled\Tests\TestHarness\SiteContent\TestTableDetailsPage;
use Littled\Tests\TestHarness\SiteContent\TestTableEditPage;
use Littled\Utility\LittledUtility;
use PHPUnit\Framework\TestCase;


class RoutedPageContentTest extends TestCase
{
	const TEST_TEMPLATE_DIR = '/path/to/templates/';
	const TEST_TEMPLATE_FILENAME = 'my-template.txt';

    /**
     * @dataProvider \Littled\Tests\DataProvider\PageContent\Navigation\RoutedPageContentTestDataProvider::collectActionFromRouteTestProvider()
     * @param string $expected_action
     * @param int|null $expected_record_id
     * @param array $route
     * @return void
     */
    function testCollectActionsFromRoute(string $expected_action, ?int $expected_record_id, array $route)
    {
        $action = RoutedPageContent::collectActionFromRoute($route);
        $this->assertEquals($expected_action, $action->token, static::formatRouteMessage($route));
        $this->assertEquals($expected_record_id, $action->record_id, static::formatRouteMessage($route));
    }

    /**
     * @dataProvider \Littled\Tests\DataProvider\PageContent\Navigation\RoutedPageContentTestDataProvider::collectRecordIdFromRouteTestProvider()
     * @param int|null $expected
     * @param array $route
     * @return void
     */
    function testCollectRecordIdFromRoute(?int $expected, array $route)
    {
        $this->assertEquals($expected, RoutedPageContent::collectRecordIdFromRoute($route), static::formatRouteMessage($route));
    }

	/**
	 * @throws InvalidTypeException
	 */
	function testGetDetailsURI()
    {
        $record_id = 123;
        $o = new RoutedPageContentTestHarness();
        $o::setContentClassName(SectionContentTestHarness::class);
        $o::setRoutesClassName(SectionNavigationRoutesTestHarness::class);
        $o->instantiateProperties($record_id);

        $expected = LittledUtility::joinPaths('/', TestTableDetailsPage::getBaseRoute(), $record_id);
        $this->assertEquals($expected, $o->getDetailsURI($record_id));
    }

	/**
	 * @throws InvalidTypeException
	 */
	function testGetEditURIWithFilters()
    {
        $test_record_id = 643;
        $o = new RoutedPageContentTestHarness();
        $o::setFiltersClassName(TestTableContentFiltersTestHarness::class);
        $o::setContentClassName(TestTableSectionContentTestHarness::class);
        $o::setRoutesClassName(TestTableSectionNavigationRoutes::class);
        $o->instantiateProperties();
        $o->filters->display_listings->value = true;
        $o->formatQueryString();

        // edit uri without a record id
        $expected = LittledUtility::joinPaths('/', TestTableEditPage::getBaseRoute(), RoutedPageContentTestHarness::getAddToken());
        $pattern = '/'.preg_quote($expected, '/').'/';
        $this->assertMatchesRegularExpression($pattern,  $o->getEditURIWithFilters());

        $pattern = '/'.preg_quote($o->filters->display_listings->key.'=1', '/').'/';
        $this->assertMatchesRegularExpression($pattern,  $o->getEditURIWithFilters());

        // edit uri with a record id
        $expected = LittledUtility::joinPaths(TestTableEditPage::getBaseRoute(), $test_record_id, RoutedPageContentTestHarness::getEditToken());
        $pattern = '/'.preg_quote($expected, '/').'/';
        $this->assertMatchesRegularExpression($pattern, $o->getEditURIWithFilters($test_record_id));

        $expected = $o->filters->display_listings->key.'=1';
        $pattern = '/'.preg_quote($expected, '/').'/';
        $this->assertMatchesRegularExpression($pattern, $o->getEditURIWithFilters($test_record_id));

        // edit uri without the filter's value being set
        $o->filters->display_listings->value = null;
        $o->formatQueryString();
        $pattern = '/'.preg_quote($o->filters->display_listings->key.'=1', '/').'/';
        $this->assertDoesNotMatchRegularExpression($pattern, $o->getEditURIWithFilters($test_record_id));
    }

    /**
     * @throws InvalidTypeException
     * @throws ConfigurationUndefinedException
     */
	function testGetEditURIWithoutRecordId()
    {
        $o = new RoutedPageContentTestHarness();
        $o::setContentClassName(SectionContentTestHarness::class);
        $o::setRoutesClassName(SectionNavigationRoutesTestHarness::class);
        $o->instantiateProperties();

        // format uri using object's internal value
        $expected = LittledUtility::joinPaths('/', TestTableDetailsPage::getBaseRoute(), RoutedPageContentTestHarness::getAddToken());
        $this->assertEquals($expected, $o->getEditURI());
        // format uri by passing record id as argument
        $expected = LittledUtility::joinPaths('/', TestTableEditPage::getBaseRoute(), 539, RoutedPageContentTestHarness::getEditToken());
        $this->assertEquals($expected, $o->getEditURI(539));
    }

    /**
     * @throws InvalidTypeException
     * @throws ConfigurationUndefinedException
     */
	function testGetEditURIWithRecordId()
    {
        $record_id = 123;
        $o = new RoutedPageContentTestHarness();
        $o::setContentClassName(SectionContentTestHarness::class);
        $o::setRoutesClassName(SectionNavigationRoutesTestHarness::class);
        $o->instantiateProperties($record_id);

        // format uri using object's internal value
        $expected = LittledUtility::joinPaths('/', TestTableEditPage::getBaseRoute(), $record_id, TestTableEditPage::getEditToken());
        $this->assertEquals($expected, $o->getEditURI());
        // format uri by passing record id as argument
        $expected = LittledUtility::joinPaths('/', TestTableDetailsPage::getBaseRoute(), 765, RoutedPageContentTestHarness::getEditToken());
        $this->assertEquals($expected, $o->getEditURI(765));
    }

	function testGetAccessLevel()
	{
		$o = new RoutedPageContentTestHarness();
        $original_access = $o::getAccessLevel();

		// default setting
		$this->assertEquals(UserAccount::AUTHENTICATION_UNRESTRICTED, $o::getAccessLevel());

		// set to a value
		$o::setAccessLevel(UserAccount::BASIC_AUTHENTICATION);
		$this->assertEquals(UserAccount::BASIC_AUTHENTICATION, $o::getAccessLevel());

        // restore state
        $o::setAccessLevel($original_access);
	}

    /**
     * @dataProvider \Littled\Tests\DataProvider\PageContent\Navigation\RoutedPageContentTestDataProvider::getRecordIdProvider()
     * @param int|null $record_id
     * @param int|null $expected
     * @return void
     */
    function testGetRecordId(?int $record_id, ?int $expected)
    {
        $o = new RoutedPageContentTestHarness();
        $o::setContentClassName(TestTableSectionContentTestHarness::class);
        $o->instantiateProperties();
        $o->content->id->value = $record_id;
        $this->assertEquals($expected, $o->getRecordId());
    }

    /**
	 * @throws ConfigurationUndefinedException
	 */
	function testGetTemplateDir()
	{
		$this->assertEquals(RoutedPageContentTest::TEST_TEMPLATE_DIR, RoutedPageContentTestHarness::getTemplateDir());
	}

	function testGetTemplateFilename()
	{
		$this->assertEquals(RoutedPageContentTest::TEST_TEMPLATE_FILENAME, RoutedPageContentTestHarness::getTemplateFilename());
	}

	/**
	 * @throws ConfigurationUndefinedException
	 */
	function testGetTemplateFullPath()
	{
		try {
			$this->assertEquals(LittledUtility::joinPaths(RoutedPageContentTest::TEST_TEMPLATE_DIR, RoutedPageContentTest::TEST_TEMPLATE_FILENAME), RoutedPageContentTestHarness::getTemplateFullPath());
		}
		catch(Exception $e) {
			$this->assertInstanceOf(ConfigurationUndefinedException::class, $e);
		}
		RoutedPageContentTestHarness::setRoutesClassName(SectionNavigationRoutesTestHarness::class);
		$this->assertEquals(LittledUtility::joinPaths(RoutedPageContentTest::TEST_TEMPLATE_DIR, RoutedPageContentTest::TEST_TEMPLATE_FILENAME), RoutedPageContentTestHarness::getTemplateFullPath());
	}

	/**
	 * @throws ConfigurationUndefinedException
	 */
	function testGetValidatedRoutesClass()
	{
		// Tests unassigned routes class
		try {
			RoutedPageContent::getValidatedRoutesClass();
		}
		catch(Exception $e) {
			$this->assertEquals('Invalid route object in Littled\PageContent\Navigation\RoutedPageContent.', $e->getMessage());
		}

		// Tests unassigned routes class in child class
		try {
			RoutedPageContentTestHarness::getValidatedRoutesClass();
		}
		catch(Exception $e) {
			$this->assertEquals('Invalid route object in Littled\Tests\TestHarness\PageContent\Navigation\RoutedPageContentTestHarness.', $e->getMessage());
		}

		// tests class after assigning routes class
		$routes_class = SectionNavigationRoutesTestHarness::class;
		RoutedPageContentTestHarness::setRoutesClassName($routes_class);
		$this->assertEquals($routes_class, RoutedPageContentTestHarness::getValidatedRoutesClass());

		// Tests non-existent method
		try {
			RoutedPageContentTestHarness::getValidatedRoutesClass('nonExistentMethod');
		}
		catch (Exception $e) {
			$this->assertInstanceOf(ConfigurationUndefinedException::class, $e);
			$this->assertStringStartsWith('Invalid interface', $e->getMessage());
		}

		// Tests valid existing method
		$this->assertEquals($routes_class, RoutedPageContentTestHarness::getValidatedRoutesClass('methodAvailableForTestPurposes'));

		// Reset routes class name
		RoutedPageContentTestHarness::setRoutesClassName('');
	}

	/**
	 * @throws InvalidTypeException
	 * @throws NotImplementedException
	 */
	function testSetFilters()
	{
		$_POST = array(
			LittledGlobals::CONTENT_TYPE_KEY => TestTableSerializedContentTestHarness::CONTENT_TYPE_ID,
			'name' => 'foo',
			'dateAfter' => '2023-02-14',
			'dateBefore' => '2023-02-28',
			);

		$rpc = new RoutedPageContentTestHarness();
        $rpc::setFiltersClassName(TestTableContentFiltersTestHarness::class);
		$rpc->instantiateProperties();
		$this->assertTrue(isset($rpc->filters));

		/** @var TestTableContentFiltersTestHarness $f1 */
		$f1 = $rpc->filters;
		$this->assertEquals('', $f1->name_filter->value);
		$this->assertEquals('', $f1->date_after->value);

		$rpc->loadFilters();
		$this->assertEquals('foo', $f1->name_filter->value);
		$this->assertEquals('02/14/2023', $f1->date_after->value);
		$this->assertEquals('02/28/2023', $f1->date_before->value);

		$new_filters = new TestTableContentFiltersTestHarness();
		$new_filters->name_filter->value = 'bar';
		$new_filters->date_after->value = '06/01/2022';
		$rpc->setFilters($new_filters);

		/** @var TestTableContentFiltersTestHarness $f2 */
		$f2 = $rpc->filters;
		$this->assertEquals('bar', $f2->name_filter->value);
		$this->assertEquals('06/01/2022', $f2->date_after->value);
		$this->assertEquals('', $f2->date_before->value);

		$_POST = [];
	}

	/**
	 * @throws ConfigurationUndefinedException
	 */
	function testSetTemplateFilename()
	{
		$new_filename = '/new-template.txt';
		RoutedPageContentTestHarness::setTemplateFilename($new_filename);
		$this->assertEquals($new_filename, RoutedPageContentTestHarness::getTemplateFilename());

		try {
			$this->assertEquals(LittledUtility::joinPaths(RoutedPageContentTest::TEST_TEMPLATE_DIR, $new_filename), RoutedPageContentTestHarness::getTemplateFullPath());
		}
		catch(ConfigurationUndefinedException $e) {
			$this->assertStringStartsWith('Invalid route object', $e->getMessage());
		}

		RoutedPageContentTestHarness::setRoutesClassName(SectionNavigationRoutesTestHarness::class);
		$this->assertEquals(LittledUtility::joinPaths(RoutedPageContentTest::TEST_TEMPLATE_DIR, $new_filename), RoutedPageContentTestHarness::getTemplateFullPath());

		// reset
		RoutedPageContentTestHarness::setTemplateFilename(RoutedPageContentTest::TEST_TEMPLATE_FILENAME);
	}

    protected static function formatRouteMessage(array $route): string
    {
        return "route: \"".implode("/", $route)."\"";
    }
}