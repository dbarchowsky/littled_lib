<?php
namespace Littled\Tests\PageContent\Navigation;

use Littled\PageContent\Navigation\SectionNavigationRoutes;
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

	function testGetTemplateDir()
	{
		$this->assertEquals('', SectionNavigationRoutes::getTemplateDir());
	}

	function testSetTemplateDir()
	{
		$new_path = '/new/path/to/templates/';
		SectionNavigationRoutes::setTemplateDir($new_path);
		$this->assertEquals($new_path, SectionNavigationRoutes::getTemplateDir());
	}
}