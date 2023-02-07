<?php
namespace Littled\Tests\PageContent\Cache;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidTypeException;
use Littled\PageContent\Cache\ContentCache;
use Littled\Tests\TestHarness\PageContent\SiteSection\SectionContentTestHarness;
use PHPUnit\Framework\TestCase;


class ContentCacheTest extends TestCase
{
    public const TEST_CONTROLLER_CLASS = 'Littled\Tests\TestHarness\PageContent\ContentControllerTestHarness';

    /**
     * @throws InvalidTypeException
     */
    function testSetControllerClassToBaseClass()
    {
        // try to assign the base default controller class
        $this->expectExceptionMessageMatches('/cannot instantiate abstract class/i');
        ContentCache::setControllerClass('Littled\PageContent\ContentController');
    }

    /**
     * @throws InvalidTypeException
     */
    function testSetControllerClassInvalidClass()
    {
        // try to assign the base default controller class
        $this->expectExceptionMessageMatches('/invalid controller class/i');
        ContentCache::setControllerClass(SectionContentTestHarness::class);
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