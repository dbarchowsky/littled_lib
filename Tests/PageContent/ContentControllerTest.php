<?php
namespace Littled\Tests\PageContent;

use Exception;
use Littled\Exception\InvalidRouteException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Tests\TestHarness\PageContent\ContentControllerTestHarness;
use Littled\Tests\TestHarness\SiteContent\TestTableDetailsPage;
use Littled\Tests\TestHarness\SiteContent\TestTableListingsPage;
use PHPUnit\Framework\TestCase;


class ContentControllerTest extends TestCase
{
    /**
     * @dataProvider \Littled\Tests\DataProvider\PageContent\ContentControllerTestDataProvider::formatNavigationRouteTestProvider()
     * @throws InvalidValueException
     * @throws NotImplementedException
     */
    function testFormatNavigationRoute(
        string $expected,
        string $routed_content_class,
        string $operation,
        ?int $record_id,
        string $msg='' )
    {
        $route = ContentControllerTestHarness::publicFormatNavigationRoute(
            new $routed_content_class(),
            $operation,
            $record_id);
        $this->assertEquals($expected, $route, $msg);
    }

    function testGetRoutedPageContentClass()
    {
        // condition that exists within ContentControllerTestHarness::getRoutedPageContentClass()
        $route = array('test');
        $this->assertEquals(
            TestTableDetailsPage::class,
            call_user_func([ContentControllerTestHarness::class, 'getRoutedPageContentClass'], $route));

        // condition that exists within ContentControllerTestHarness::getRoutedPageContentClass()
        $route = array('tests');
        $this->assertEquals(
            TestTableListingsPage::class,
            call_user_func([ContentControllerTestHarness::class, 'getRoutedPageContentClass'], $route));

        // condition that does not exist within ContentControllerTestHarness::getRoutedPageContentClass()
        try {
            $route = array('bogus');
            call_user_func([ContentControllerTestHarness::class, 'getRoutedPageContentClass'], $route);
        }
        catch(Exception $e) {
            $this->assertInstanceOf(InvalidRouteException::class, $e);
            $this->assertMatchesRegularExpression('/invalid route/i', $e->getMessage());
        }

        // empty route
        try {
            call_user_func([ContentControllerTestHarness::class, 'getRoutedPageContentClass'], []);
        }
        catch(Exception $e) {
            $this->assertInstanceOf(InvalidRouteException::class, $e);
            $this->assertMatchesRegularExpression('/route was not supplied/i', $e->getMessage());
        }
    }
}