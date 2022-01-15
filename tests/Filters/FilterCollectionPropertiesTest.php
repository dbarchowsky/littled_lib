<?php
namespace Littled\Tests\Filters;
require_once (realpath(dirname(__FILE__)).'/../bootstrap.php');

use Littled\Exception\NotImplementedException;
use Littled\Filters\FilterCollectionProperties;
use Littled\Tests\Filters\Samples\FilterCollectionPropertiesChild;
use PHPUnit\Framework\TestCase;

class FilterCollectionPropertiesTest extends TestCase
{
    /** @var int */
    protected const CHILD_LISTINGS_LENGTH = 50;
    /** @var string */
    protected const CHILD_LISTINGS_LABEL = 'child';
    /** @var string */
    protected const CHILD_TABLE_NAME = 'child_table';

    /**
     * @return void
     * @throws NotImplementedException
     */
    function testConstructor()
    {
        $c = new FilterCollectionPropertiesChild();
        $this->assertEquals(self::CHILD_LISTINGS_LENGTH, $c::getDefaultListingsLength());
    }

    function testCookieKey()
    {
        $this->assertEquals('', FilterCollectionProperties::getCookieKey());

        FilterCollectionProperties::setCookieKey('test');
        $this->assertEquals('test', FilterCollectionProperties::getCookieKey());

        $this->assertEquals('', FilterCollectionPropertiesChild::getCookieKey());
        FilterCollectionPropertiesChild::setCookieKey('child');
        $this->assertEquals('test', FilterCollectionProperties::getCookieKey());
        $this->assertEquals('child', FilterCollectionPropertiesChild::getCookieKey());
    }

    function testKeyPrefix()
    {
        $this->assertEquals('', FilterCollectionProperties::getKeyPrefix());

        FilterCollectionProperties::setKeyPrefix('pk');
        $this->assertEquals('pk', FilterCollectionProperties::getKeyPrefix());

        $this->assertEquals('', FilterCollectionPropertiesChild::getKeyPrefix());
        FilterCollectionPropertiesChild::setKeyPrefix('ck');
        $this->assertEquals('pk', FilterCollectionProperties::getKeyPrefix());
        $this->assertEquals('ck', FilterCollectionPropertiesChild::getKeyPrefix());

        $this->assertEquals('pkKey', FilterCollectionProperties::getLocalKey('Key'));
        $this->assertEquals('ckKey', FilterCollectionPropertiesChild::getLocalKey('Key'));
    }

    function testDefaultListingsLengthUnset()
    {
        $this->expectException(NotImplementedException::class);
        FilterCollectionProperties::getDefaultListingsLength();
    }

    /**
     * @return void
     * @throws NotImplementedException
     */
    function testDefaultListingsLength()
    {
        // Test value after setting it on parent class
        FilterCollectionProperties::setDefaultListingsLength(100);
        $this->assertEquals(100, FilterCollectionProperties::getDefaultListingsLength());

        // Child class should retain its own value
        $this->assertEquals(self::CHILD_LISTINGS_LENGTH, FilterCollectionPropertiesChild::getDefaultListingsLength());

        // Set value on child class
        FilterCollectionPropertiesChild::setDefaultListingsLength(75);
        $this->assertEquals(100, FilterCollectionProperties::getDefaultListingsLength());
        $this->assertEquals(75, FilterCollectionPropertiesChild::getDefaultListingsLength());
    }

    function testListingsLabelUnset()
    {
        $this->expectException(NotImplementedException::class);
        FilterCollectionProperties::getListingsLabel();
    }

    /**
     * @return void
     * @throws NotImplementedException
     */
    function testListingsLabel()
    {
        // Test value after setting it on parent class
        FilterCollectionProperties::setListingsLabel('item');
        $this->assertEquals('item', FilterCollectionProperties::getListingsLabel());

        // Child class should retain its own value
        $this->assertEquals(self::CHILD_LISTINGS_LABEL, FilterCollectionPropertiesChild::getListingsLabel());

        // Set value on child class
        FilterCollectionPropertiesChild::setListingsLabel('new child');
        $this->assertEquals('item', FilterCollectionProperties::getListingsLabel());
        $this->assertEquals('new child', FilterCollectionPropertiesChild::getListingsLabel());
    }

    function testTableNameUnset()
    {
        $this->expectException(NotImplementedException::class);
        FilterCollectionProperties::getTableName();
    }

    /**
     * @return void
     * @throws NotImplementedException
     */
    function testTableName()
    {
        // Test value after setting it on parent class
        FilterCollectionProperties::setTableName('parent_table');
        $this->assertEquals('parent_table', FilterCollectionProperties::getTableName());

        // Child class should retain its own value
        $this->assertEquals(self::CHILD_TABLE_NAME, FilterCollectionPropertiesChild::getTableName());

        // Set value on child class
        FilterCollectionPropertiesChild::setTableName('new_child_table');
        $this->assertEquals('parent_table', FilterCollectionProperties::getTableName());
        $this->assertEquals('new_child_table', FilterCollectionPropertiesChild::getTableName());
    }
}