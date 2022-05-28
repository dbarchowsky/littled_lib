<?php
namespace Littled\Tests\PageContent\Metadata;

use Littled\Exception\InvalidValueException;
use Littled\PageContent\Metadata\MetadataElement;
use PHPUnit\Framework\TestCase;

class MetadataElementTest extends TestCase
{
    public function testConstruct()
    {
        $me = new MetadataElement('name', 'title');
        $this->assertEquals('name', $me->getType());
        $this->assertEquals('title', $me->name);
    }

    /**
     * @dataProvider \Littled\Tests\PageContent\Metadata\DataProvider\MetadataElementTestProvider::renderTestProvider()
     * @param MetadataElement $o
     * @param string $expected
     * @return void
     */
    function testRender(MetadataElement $o, string $expected)
    {
        $this->expectOutputRegex($expected);
        $o->render();
    }

    /**
     * @throws InvalidValueException
     */
    public function testSetType()
    {
        $me = new MetadataElement('name', 'title');
        $me->setType('name');
        $this->assertEquals('name', $me->getType());
        $me->setType('http-equiv');
        $this->assertEquals('http-equiv', $me->getType());
        $me->setType('charset');
        $this->assertEquals('charset', $me->getType());
        $me->setType('itemprop');
        $this->assertEquals('itemprop', $me->getType());
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessageMatches("/not a valid metadata element type/i");
        $me->setType('foo');
    }

    public function testSetName()
    {
        $test_str = 'my_new_name';
        $me = new MetadataElement('name', 'some value');
        $this->assertNotEquals($test_str, $me->getName());
        $me->setName($test_str);
        $this->assertEquals($test_str, $me->getName());
    }

    public function testSetValue()
    {
        $test_str = 'my test value';
        $me = new MetadataElement('name', 'initial metadata value');
        $this->assertNotEquals($test_str, $me->getContent());
        $me->setContent($test_str);
        $this->assertEquals($test_str, $me->getContent());
    }
}