<?php

namespace LittledTests\PageContent\Serialized;


use LittledTests\TestHarness\PageContent\Serialized\SerializedContentIOTestHarness;
use PHPUnit\Framework\TestCase;

class SerializedContentIOTest extends TestCase
{
    protected const LABEL_VALUE = 'Test serialized content';

    public function testGetInlineLabelDefault()
    {
        $o = new SerializedContentIOTestHarness();
        $expected = strtolower(self::LABEL_VALUE);
        self::assertEquals($expected, $o->getInlineLabel());
    }

    public function testGetInlineLabelPlural()
    {
        $o = new SerializedContentIOTestHarness();
        $expected = $o::makePlural(strtolower(self::LABEL_VALUE));
        self::assertEquals($expected, $o->getInlineLabel(true));
    }

    public function testGetLabel()
    {
        $o = new SerializedContentIOTestHarness();
        self::assertEquals(self::LABEL_VALUE, $o->getLabel());
    }
}