<?php
namespace Littled\Tests\PageContent;

use Littled\PageContent\PageContent;
use Littled\PageContent\PageContentBase;
use Littled\Tests\TestHarness\PageContent\PageContentBaseTestHarness;
use Littled\Tests\TestHarness\PageContent\PageContentChild;
use PHPUnit\Framework\TestCase;


class PageContentBaseTest extends TestCase
{
    function testGetTemplatePath()
    {
        $pcb = new PageContentBaseTestHarness();
        $this->assertEquals('', $pcb->getTemplatePath());
    }

    function testSetTemplatePath()
    {
        $path1 = '/first/path/to/templates/';
        $path2 = '/second/path/to/templates/';

        // test base class setter
        $pcb = new PageContentBaseTestHarness();
        $pcb->setTemplatePath($path1);
        $this->assertEquals($path1, $pcb->getTemplatePath());

        $pc = new PageContentChild();
        $pc->setTemplatePath($path2);
        $this->assertEquals($path2, $pc->getTemplatePath());
        $this->assertEquals($path1, $pcb->getTemplatePath());
    }
}