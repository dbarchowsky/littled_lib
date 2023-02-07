<?php
namespace Littled\Tests\PageContent\Metadata;

use Littled\Exception\InvalidValueException;
use Littled\PageContent\Metadata\PageMetadata;
use PHPUnit\Framework\TestCase;

class PageMetadataTest extends TestCase
{
    /**
     * @throws InvalidValueException
     */
    public function testAddPageMetadata()
    {
        $pm = new PageMetadata();
        $pm->addPageMetadata('name', 'description', 'Description of website');
        $pm->addPageMetadata('name', 'keywords', 'testing, development, php, libraries');
        $md = $pm->getPageMetadata();
        $this->assertIsArray($md);
        $this->assertCount(2, $md);
        $this->assertEquals('keywords', $md[1]->getName());
    }

    public function testKeywords()
    {
        $test_array = array('testing', 'Tests', 'development', 'websites');
        $pm = new PageMetadata();
        $pm->setKeywords($test_array);
        $this->assertSame($test_array, $pm->getKeywords());
    }
}