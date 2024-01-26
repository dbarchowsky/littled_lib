<?php

namespace LittledTests\PageContent\Navigation;

use Littled\Exception\InvalidTypeException;
use Littled\PageContent\Navigation\SectionNavigationRoutes;
use LittledTests\DataProvider\PageContent\Navigation\SectionNavigationRoutes\GetPageRouteTestData;
use LittledTests\TestHarness\PageContent\Navigation\SectionNavigationRoutesTestHarness;
use LittledTests\TestHarness\PageContent\Navigation\SectionNavRoutesUndefinedTestHarness;
use LittledTests\TestHarness\SiteContent\TestTableDetailsPage;
use LittledTests\TestHarness\SiteContent\TestTableListingsPage;
use PHPUnit\Framework\TestCase;


class SectionNavigationRoutesTest extends TestCase
{
    function testGetDefaultDetailsRouteWhenUndefined()
    {
        $this->assertEquals('', SectionNavigationRoutes::getDetailsRoute());
    }

    function testGetDefaultEditRouteWhenUndefined()
    {
        $this->assertEquals('', SectionNavigationRoutes::getEditRoute());
    }

    function testGetDefaultListingsRouteWhenUndefined()
    {
        $this->assertEquals('', SectionNavigationRoutes::getListingsRoute());
    }

    function testGetDetailsRouteBase()
    {
        $this->assertEquals(TestTableDetailsPage::getBaseRoute(), SectionNavigationRoutesTestHarness::getDetailsRouteBase());
    }

    function testGetDetailsRouteWhenUndefined()
    {
        $this->assertEquals('', SectionNavRoutesUndefinedTestHarness::getDetailsRoute());
    }

    function testGetEditRouteWhenUndefined()
    {
        $this->assertEquals('', SectionNavRoutesUndefinedTestHarness::getEditRoute());
    }

    function testGetListingsRouteBase()
    {
        $this->assertEquals(TestTableListingsPage::getBaseRoute(), SectionNavigationRoutesTestHarness::getListingsRouteBase());
    }

    function testGetListingsRouteWhenUndefined()
    {
        $this->assertEquals('', SectionNavRoutesUndefinedTestHarness::getListingsRoute());
    }

    /**
     * @dataProvider \LittledTests\DataProvider\PageContent\Navigation\SectionNavigationRoutesTestDataProvider::getPageRouteTestProvider()
     * @param GetPageRouteTestData $data
     * @return void
     * @throws InvalidTypeException
     */
    function testGetPageRoute(GetPageRouteTestData $data)
    {
        $this->assertEquals(
            $data->expected,
            SectionNavigationRoutesTestHarness::getPageRoute($data->class, $data->record_id));
    }

    function testSetTemplateDir()
    {
        // save state
        $start_path = SectionNavigationRoutes::getTemplateDir();

        $new_path = '/new/path/to/templates/';
        SectionNavigationRoutes::setTemplateDir($new_path);
        $this->assertEquals($new_path, SectionNavigationRoutes::getTemplateDir());

        // restore state
        SectionNavigationRoutes::setTemplateDir($start_path);
    }

    function testGetTemplateDirDefault()
    {
        $this->assertEquals('', SectionNavigationRoutes::getTemplateDir());
    }

    function testGetTemplateDirUndefined()
    {
        $this->assertEquals('', SectionNavRoutesUndefinedTestHarness::getTemplateDir());
    }
}