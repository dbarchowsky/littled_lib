<?php
namespace Littled\Tests\PageContent\Metadata;
require_once(realpath(dirname(__FILE__)) . "/../../bootstrap.php");

use Littled\PageContent\Metadata\Preload;
use PHPUnit\Framework\TestCase;

class PreloadTest extends TestCase
{
    /**
     * @dataProvider \Littled\Tests\PageContent\Metadata\DataProvider\PreloadTestDataProvider::renderTestProvider()
     * @param Preload $o
     * @param string $expected
     * @return void
     */
    function testRender(Preload $o, string $expected)
    {
        $this->expectOutputRegex($expected);
        $o->render();
    }
}