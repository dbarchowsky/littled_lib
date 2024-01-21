<?php

namespace LittledTests\Request;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\InvalidTypeException;
use Littled\PageContent\Serialized\LinkedContent;
use Littled\Request\ForeignKeyInput;
use LittledTests\TestHarness\PageContent\Serialized\LinkedContent\LinkedContentTestHarness;
use PHPUnit\Framework\TestCase;

class ForeignKeyInputTest extends TestCase
{
    public function testGetContentUninitialized()
    {
        $o = new ForeignKeyInput('My label', 'testKey');

        try {
            $class = $o->getContentClass();
            self::fail('Expected ConfigurationUndefinedException not thrown.');
        }
        catch (ConfigurationUndefinedException $e) {
            self::assertMatchesRegularExpression('/content class.* not.* assigned/i', $e->getMessage());
        }
    }

    public function testIsDatabaseFieldDefault()
    {
        $o = new ForeignKeyInput('DB field default', 'testKey');
        self::assertTrue($o->isDatabaseField());
    }

    /**
     * @throws InvalidTypeException
     * @throws ConfigurationUndefinedException
     */
    public function testSetContentClass()
    {
        $o = new ForeignKeyInput('My label', 'testKey');
        $o->setContentClass(LinkedContentTestHarness::class);
        self::assertEquals(LinkedContentTestHarness::class, $o->getContentClass());
    }

    /**
     * @throws InvalidTypeException
     * @throws ConfigurationUndefinedException
     */
    public function testSetContentClassChained()
    {
        $o = (new ForeignKeyInput('My label', 'testKey'))
            ->setContentClass(LinkedContentTestHarness::class);
        self::assertEquals(LinkedContentTestHarness::class, $o->getContentClass());
    }

    public function testSetContentClassWithBadClass()
    {
        $o = new ForeignKeyInput('My label', 'testKey');

        try {
            $o->setContentClass('GarbageClass');
            self::fail('Expected InvalidTypeException not thrown.');
        }
        catch (InvalidTypeException $e) {
            $expected = '/content class.* '.str_replace('\\', '\\\\', LinkedContent::class).'/i';
            self::assertMatchesRegularExpression($expected, $e->getMessage());
        }
    }

    public function testSetLinkedListingsCB()
    {
        $o = new ForeignKeyInput('FK input', 'fkKey');
        self::assertFalse($o->getLinkedListingsCB());

        $cb = 'linkedListingsSelect';
        $o->setLinkedListingsCB($cb);
        self::assertEquals($cb, $o->getLinkedListingsCB());
    }
}