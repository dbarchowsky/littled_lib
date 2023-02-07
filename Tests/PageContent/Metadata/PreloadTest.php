<?php
namespace Littled\Tests\PageContent\Metadata;

use Littled\PageContent\Metadata\Preload;
use PHPUnit\Framework\TestCase;

class PreloadTest extends TestCase
{
    /**
     * @dataProvider \Littled\Tests\DataProvider\PageContent\Metadata\PreloadTestDataProvider::renderTestProvider()
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