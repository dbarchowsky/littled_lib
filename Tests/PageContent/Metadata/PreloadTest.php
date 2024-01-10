<?php
namespace LittledTests\PageContent\Metadata;

use Littled\PageContent\Metadata\Preload;
use PHPUnit\Framework\TestCase;

class PreloadTest extends TestCase
{
    /**
     * @dataProvider \LittledTests\DataProvider\PageContent\Metadata\PreloadTestDataProvider::renderTestProvider()
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