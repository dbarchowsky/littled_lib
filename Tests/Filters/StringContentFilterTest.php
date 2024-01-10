<?php
namespace LittledTests\Filters;

use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Filters\StringContentFilter;
use PHPUnit\Framework\TestCase;

class StringContentFilterTest extends TestCase
{
    /**
     * @throws ConfigurationUndefinedException
     */
    function testEscapeSQL()
    {
        $c = new MySQLConnection();

        // null value
        $sf = new StringContentFilter('label', 'key', null, 50);
        $this->assertEquals("''", $sf->escapeSQL($c->getMysqli()));
        $this->assertEquals("", $sf->escapeSQL($c->getMysqli(), false));
        $this->assertEquals("", $sf->escapeSQL($c->getMysqli(), false, false));

        // empty string
        $sf->value = '';
        $this->assertEquals("''", $sf->escapeSQL($c->getMysqli()));
        $this->assertEquals("", $sf->escapeSQL($c->getMysqli(), false));
        $this->assertEquals("", $sf->escapeSQL($c->getMysqli(), false, false));

        // non-empty string
        $sf->value = 'search pattern';
        $this->assertEquals("'%search pattern%'", $sf->escapeSQL($c->getMysqli()));
        $this->assertEquals("%search pattern%", $sf->escapeSQL($c->getMysqli(), false));
        $this->assertEquals("search pattern", $sf->escapeSQL($c->getMysqli(), false, false));
        $this->assertEquals("'search pattern'", $sf->escapeSQL($c->getMysqli(), true, false));
    }
}