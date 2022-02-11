<?php
namespace Littled\Tests\PageContent\Cache;
require_once(realpath(dirname(__FILE__)) . "/../../bootstrap.php");

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidTypeException;
use Littled\PageContent\Cache\ContentCache;
use PHPUnit\Framework\TestCase;


class ContentCacheTest extends TestCase
{
    public const TEST_CONTROLLER_CLASS = 'Littled\Tests\PageContent\TestHarness\ContentControllerTestHarness';

    /**
     * @throws InvalidTypeException|ConfigurationUndefinedException
     */
    function testSetControllerClassToBaseClass()
    {
        // try to assign the base default controller class
        $this->expectExceptionMessageMatches('/cannot instantiate abstract class/i');
        ContentCache::setControllerClass('Littled\PageContent\ContentController');
    }

    /**
     * @throws InvalidTypeException|ConfigurationUndefinedException
     */
    function testSetControllerClassInvalidClass()
    {
        // try to assign the base default controller class
        $this->expectExceptionMessageMatches('/invalid controller class/i');
        ContentCache::setControllerClass('Littled\Tests\PageContent\SiteSection\TestHarness\SectionContentTestHarness');
    }

    /**
     * @throws InvalidTypeException|ConfigurationUndefinedException
     */
    function testSetControllerClass()
    {
        // assign valid controller class
        ContentCache::setControllerClass(self::TEST_CONTROLLER_CLASS);
        $this->assertEquals(self::TEST_CONTROLLER_CLASS, ContentCache::getControllerClass());

    }
}