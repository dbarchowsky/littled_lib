<?php
namespace Littled\Tests\PageContent\Navigation;

use Littled\Tests\PageContent\Navigation\TestHarness\SectionNavigationRoutesTestHarness;
use PHPUnit\Framework\TestCase;


class SectionNavigationRoutesTest extends TestCase
{
    function testGetDetailsRouteBase()
    {
        $this->assertEquals('unicorn', SectionNavigationRoutesTestHarness::getDetailsRouteBase());
    }

    function testGetListingsRouteBase()
    {
        $this->assertEquals('unicorns', SectionNavigationRoutesTestHarness::getListingsRouteBase());
    }
}