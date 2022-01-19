<?php
namespace Littled\Tests\Filters;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\Filters\IntegerContentFilter;
use PHPUnit\Framework\TestCase;

class IntegerContentFilterTest extends TestCase
{
    function testConstructor()
    {
        // Test default values
        $f = new IntegerContentFilter('my label', 'the_key');
        $this->assertEquals('my label', $f->label);
        $this->assertEquals('the_key', $f->key);
        $this->assertNull($f->value);
        $this->assertEquals(0, $f->size);
        $this->assertEquals('', $f->cookieKey);

        // Test setting property values
        $f = new IntegerContentFilter('test label', 'test', 10, 8, 'cookie_key');
        $this->assertEquals('test label', $f->label);
        $this->assertEquals('test', $f->key);
        $this->assertEquals(10, $f->value);
        $this->assertEquals(8, $f->size);
        $this->assertEquals('cookie_key', $f->cookieKey);

        // Pass 'null' as initial value and size
        $f = new IntegerContentFilter('my label', 'the_key', null, null);
        $this->assertNull($f->value);
        $this->assertNull($f->size);
    }
}