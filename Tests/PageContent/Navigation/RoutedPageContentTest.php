<?php
namespace Littled\Tests\PageContent\Navigation;

use Littled\Account\UserAccount;
use Littled\Exception\InvalidTypeException;
use Littled\PageContent\Navigation\RoutedPageContent;
use Littled\Tests\Filters\TestHarness\TestTableFilters;
use Littled\Tests\PageContent\Navigation\TestHarness\RoutedPageContentTestHarness;
use Littled\Tests\PageContent\Navigation\TestHarness\SectionNavigationRoutesTestHarness;
use Littled\Tests\PageContent\Serialized\TestHarness\TestTable;
use Littled\Tests\PageContent\SiteSection\TestHarness\SectionContentTestHarness;
use PHPUnit\Framework\TestCase;


class RoutedPageContentTest extends TestCase
{
    /**
     * @dataProvider \Littled\Tests\PageContent\Navigation\DataProvider\RoutedPageContentTestDataProvider::collectActionFromRouteTestProvider()
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
     * @dataProvider \Littled\Tests\PageContent\Navigation\DataProvider\RoutedPageContentTestDataProvider::collectRecordIdFromRouteTestProvider()
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

        $this->assertEquals('/unicorn/123', $o->getDetailsURI());
    }

	/**
	 * @throws InvalidTypeException
	 */
	function testGetEditURIWithFilters()
    {
        $o = new RoutedPageContentTestHarness();
        $o::setFiltersClassName(TestTableFilters::class);
        $o::setContentClassName(SectionContentTestHarness::class);
        $o::setRoutesClassName(SectionNavigationRoutesTestHarness::class);
        $o->instantiateProperties();
        $o->filters->display_listings->value = true;
        $o->formatQueryString();

        // edit uri without a record id
        $uri = $o->getEditURIWithFilters();
        $pattern = '/'.preg_quote(SectionNavigationRoutesTestHarness::getDetailsRoute().'/'.RoutedPageContentTestHarness::getAddToken(), '/').'/';
        $this->assertMatchesRegularExpression($pattern, $uri);

        $pattern = '/'.preg_quote($o->filters->display_listings->key.'=1', '/').'/';
        $this->assertMatchesRegularExpression($pattern, $uri);

        // edit uri with a record id
        $uri = $o->getEditURIWithFilters(643);
        $pattern = '/'.preg_quote(SectionNavigationRoutesTestHarness::getDetailsRoute().'/643/'.RoutedPageContentTestHarness::getEditToken(), '/').'/';
        $this->assertMatchesRegularExpression($pattern, $uri);

        $pattern = '/'.preg_quote($o->filters->display_listings->key.'=1', '/').'/';
        $this->assertMatchesRegularExpression($pattern, $uri);

        // edit uri without the filter's value being set
        $o->filters->display_listings->value = null;
        $o->formatQueryString();
        $pattern = '/'.preg_quote($o->filters->display_listings->key.'=1', '/').'/';
        $uri = $o->getEditURIWithFilters(643);
        $this->assertDoesNotMatchRegularExpression($pattern, $uri);
    }

	/**
	 * @throws InvalidTypeException
	 */
	function testGetEditURIWithoutRecordId()
    {
        $o = new RoutedPageContentTestHarness();
        $o::setContentClassName(SectionContentTestHarness::class);
        $o::setRoutesClassName(SectionNavigationRoutesTestHarness::class);
        $o->instantiateProperties();

        // format uri using object's internal value
        $expected = SectionNavigationRoutesTestHarness::getDetailsRoute().'/'.RoutedPageContentTestHarness::getAddToken();
        $this->assertEquals($expected, $o->getEditURI());
        // format uri by passing record id as argument
        $expected = SectionNavigationRoutesTestHarness::getDetailsRoute().'/539/'.RoutedPageContentTestHarness::getEditToken();
        $this->assertEquals($expected, $o->getEditURI(539));
    }

	/**
	 * @throws InvalidTypeException
	 */
	function testGetEditURIWithRecordId()
    {
        $record_id = 123;
        $o = new RoutedPageContentTestHarness();
        $o::setContentClassName(SectionContentTestHarness::class);
        $o::setRoutesClassName(SectionNavigationRoutesTestHarness::class);
        $o->instantiateProperties($record_id);

        // format uri using object's internal value
        $this->assertEquals('/unicorn/123/edit', $o->getEditURI());
        // format uri by passing record id as argument
        $expected = SectionNavigationRoutesTestHarness::getDetailsRoute().'/765/'.RoutedPageContentTestHarness::getEditToken();
        $this->assertEquals($expected, $o->getEditURI(765));
    }

	function testGetAccessLevel()
	{
		$o = new RoutedPageContent();

		// default setting
		$this->assertNull($o::getAccessLevel());

		// set to a value
		$o::setAccessLevel(UserAccount::BASIC_AUTHENTICATION);
		$this->assertEquals(UserAccount::BASIC_AUTHENTICATION, $o::getAccessLevel());
	}

    protected static function formatRouteMessage(array $route): string
    {
        return "route: \"".implode("/", $route)."\"";
    }
}