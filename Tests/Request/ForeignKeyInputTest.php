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
    public function testIsDatabaseFieldDefault()
    {
        $o = new ForeignKeyInput('DB field default', 'testKey');
        self::assertTrue($o->isDatabaseField());
    }
}