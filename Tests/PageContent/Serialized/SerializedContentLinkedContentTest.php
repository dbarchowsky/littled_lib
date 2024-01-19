<?php

namespace LittledTests\PageContent\Serialized;


use LittledTests\TestHarness\PageContent\Serialized\LinkedContent\SerializedLinkedTestHarness;
use PHPUnit\Framework\TestCase;

class SerializedContentLinkedContentTest extends TestCase
{
    public function testGetForeignKeyPropertyList()
    {
        $o = new SerializedLinkedTestHarness();
        $fkp = $o->getForeignKeyPropertyList_public();
        self::assertCount(1, $fkp);
        self::assertContains('parent2', $fkp);
    }
}