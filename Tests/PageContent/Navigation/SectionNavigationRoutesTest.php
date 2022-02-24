<?php

namespace Littled\Tests\PageContent\Navigation;

use Littled\Tests\PageContent\Navigation\TestHarness\SectionNavigationRoutesTestHarness;

class SectionNavigationRoutesTest extends \PHPUnit\Framework\TestCase
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